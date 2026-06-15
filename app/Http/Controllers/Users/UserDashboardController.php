<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\CreditSale;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Log;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDashboardController extends Controller
{
    private const COLLECTED_SALE_TYPES = ['cash', 'card', 'credit', 'mixed'];

    /**
     * عرض لوحة المالك بعد تجميع كل جزء من البيانات داخل دالة مستقلة.
     */
    public function index()
    {
        $user = auth('web')->user();
        $stores = $user->stores;
        $storeIds = $stores->pluck('id');

        if ($storeIds->isEmpty()) {
            return view('dashboard.user.index', $this->emptyStateData($user, $stores));
        }

        [$selectedSummaryStore, $dailyStoreIds] = $this->resolveDailyStoreFilter($stores);

        $dailySummary = $this->buildDailySummary($dailyStoreIds);
        $monthlySummary = $this->buildMonthlySummary($user->id, $storeIds);
        $salarySummary = $this->buildSalarySummary($user, $storeIds);
        $creditSummary = $this->buildCreditSummary($storeIds);
        $inventorySummary = $this->buildInventorySummary($user->id, $storeIds);
        $metricStoreBreakdowns = $this->buildStoreBreakdowns(
            $stores,
            $monthlySummary['store_metrics'],
            $salarySummary['salariesByStore']
        );

        $subscriptionEnd = $user->subscription_end_at;
        $daysLeft = $subscriptionEnd ? now()->diffInDays($subscriptionEnd, false) : null;
        $chartData = $this->prepareChartData($storeIds);
        $activities = Log::with('store')
            ->whereIn('store_id', $storeIds)
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboard.user.index', array_merge(
            [
                'user' => $user,
                'stores' => $stores,
                'selectedSummaryStore' => $selectedSummaryStore,
                'daysLeft' => $daysLeft,
                'activities' => $activities,
                'metricStoreBreakdowns' => $metricStoreBreakdowns,
            ],
            $dailySummary,
            $monthlySummary['totals'],
            $salarySummary,
            $creditSummary,
            $inventorySummary,
            $chartData
        ));
    }

    /**
     * إرجاع بطاقات اليوم وآخر عملية دون إعادة تحميل الصفحة.
     *
     * النتيجة تخزن لثلاث ثوانٍ فقط لمنع تكرار الحساب نفسه بين عدة تبويبات.
     */
    public function dailySnapshot()
    {
        $user = auth('web')->user();
        $stores = $user->stores;
        [$selectedStore, $dailyStoreIds] = $this->resolveDailyStoreFilter($stores);
        $filterKey = $dailyStoreIds->sort()->implode('-') ?: 'none';
        $cacheKey = "owner-dashboard:{$user->id}:daily-snapshot:".today()->toDateString().":{$filterKey}";

        $snapshot = Cache::remember($cacheKey, now()->addSeconds(3), function () use ($dailyStoreIds) {
            $dailySummary = $this->buildDailySummary($dailyStoreIds);
            $latestSale = Sale::query()
                ->collectedDashboardSales()
                ->whereIn('store_id', $dailyStoreIds)
                ->whereDate('created_at', today())
                ->with(['store:id,name', 'items.product:id,name'])
                ->latest()
                ->first();

            return [
                'sales_today' => $dailySummary['salesToday'],
                'expenses_today' => $dailySummary['expensesToday'],
                'products_cost_today' => $dailySummary['productsCostToday'],
                // المصروفات لا تخصم من الربح بناءً على توجيه النظام الحالي.
                'profit_today' => $dailySummary['profitToday'],
                'operations_count' => $dailySummary['dailySalesOperationsCount'],
                'latest_operation' => $this->buildLatestOperation($latestSale),
            ];
        });

        $snapshot['summary_store_id'] = $selectedStore?->id;
        $snapshot['updated_at'] = now()->format('h:i:s A');

        return response()->json($snapshot)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * تطبيق فلتر متجر اليوم مع رفض أي متجر لا يتبع المالك.
     *
     * @return array{0: mixed, 1: Collection}
     */
    private function resolveDailyStoreFilter(Collection $stores): array
    {
        $selectedStore = null;

        if ($requestedStoreId = request()->integer('summary_store_id')) {
            $selectedStore = $stores->firstWhere('id', $requestedStoreId);
        }

        return [
            $selectedStore,
            $selectedStore ? collect([$selectedStore->id]) : $stores->pluck('id'),
        ];
    }

    /**
     * حساب مؤشرات اليوم. الربح لا يخصم المصروفات حسب السلوك المعتمد حاليًا.
     */
    private function buildDailySummary(Collection $storeIds): array
    {
        $salesQuery = Sale::query()
            ->collectedDashboardSales()
            ->whereIn('store_id', $storeIds)
            ->whereDate('created_at', today());

        $salesToday = (float) (clone $salesQuery)->sum('paid_amount');
        $productsCostToday = $this->calculateProductsCost(
            $storeIds,
            today()->startOfDay(),
            today()->endOfDay(),
            self::COLLECTED_SALE_TYPES
        );

        return [
            'salesToday' => $salesToday,
            'dailySalesOperationsCount' => (int) (clone $salesQuery)->count(),
            'productsCostToday' => $productsCostToday,
            'expensesToday' => (float) Expense::whereIn('store_id', $storeIds)
                ->whereDate('created_at', today())
                ->sum('amount'),
            'profitToday' => $salesToday - $productsCostToday,
        ];
    }

    /**
     * بناء ملخص الشهر مرة واحدة مع القيم المجمعة حسب المتجر.
     */
    private function buildMonthlySummary(int $userId, Collection $storeIds): array
    {
        $monthKey = now()->format('Y-m');
        $storeKey = $storeIds->sort()->implode('-');

        return Cache::remember(
            "owner-dashboard:{$userId}:monthly-summary:{$monthKey}:{$storeKey}",
            now()->addMinutes(5),
            function () use ($storeIds) {
                $monthStart = now()->startOfMonth();
                $monthEnd = now()->endOfMonth();
                $salesByStore = $this->sumCollectedSalesByStore($storeIds, $monthStart, $monthEnd);
                $productsCostByStore = $this->calculateProductsCostByStore(
                    $storeIds,
                    $monthStart,
                    $monthEnd,
                    self::COLLECTED_SALE_TYPES
                );
                $expensesByStore = $this->sumByStoreForPeriod(
                    'expenses',
                    'amount',
                    $storeIds,
                    $monthStart,
                    $monthEnd
                );
                $ownerPurchasesByStore = $this->sumByStoreForPeriod(
                    'purchases',
                    'cost',
                    $storeIds,
                    $monthStart,
                    $monthEnd
                );
                $accountantConsumptionByStore = Sale::query()
                    ->excludeManualInvoiceEntries()
                    ->whereIn('store_id', $storeIds)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('sale_type', 'internal_use')
                    ->groupBy('store_id')
                    ->selectRaw('store_id, COALESCE(SUM(total), 0) as aggregate')
                    ->pluck('aggregate', 'store_id');

                $storeMetrics = [];
                foreach ($storeIds as $storeId) {
                    $sales = (float) ($salesByStore[$storeId] ?? 0);
                    $cost = (float) ($productsCostByStore[$storeId] ?? 0);
                    $expenses = (float) ($expensesByStore[$storeId] ?? 0);
                    $purchases = (float) ($ownerPurchasesByStore[$storeId] ?? 0);
                    $consumption = (float) ($accountantConsumptionByStore[$storeId] ?? 0);

                    $storeMetrics[$storeId] = [
                        'sales_month' => $sales,
                        'products_cost_month' => $cost,
                        'expenses_month' => $expenses,
                        'monthly_owner_purchases' => $purchases,
                        'monthly_accountant_consumption' => $consumption,
                        'monthly_purchases_consumption' => $purchases + $consumption,
                        'profit_month' => $sales - $cost - $expenses - $purchases - $consumption,
                    ];
                }

                $salesMonth = (float) $salesByStore->sum();
                $productsCostMonth = array_sum(array_column($storeMetrics, 'products_cost_month'));
                $expensesMonth = (float) $expensesByStore->sum();
                $monthlyOwnerPurchases = (float) $ownerPurchasesByStore->sum();
                $monthlyAccountantConsumption = (float) $accountantConsumptionByStore->sum();
                $monthlyPurchasesAndConsumption = $monthlyOwnerPurchases + $monthlyAccountantConsumption;

                return [
                    'totals' => [
                        'salesMonth' => $salesMonth,
                        'expensesMonth' => $expensesMonth,
                        'profitMonth' => $salesMonth
                            - $productsCostMonth
                            - $expensesMonth
                            - $monthlyPurchasesAndConsumption,
                        'monthlyOwnerPurchases' => $monthlyOwnerPurchases,
                        'monthlyAccountantConsumption' => $monthlyAccountantConsumption,
                        'monthlyPurchasesAndConsumption' => $monthlyPurchasesAndConsumption,
                    ],
                    'store_metrics' => $storeMetrics,
                ];
            }
        );
    }

    /**
     * تجهيز الرواتب والسحوبات، بما فيها مجموع الرواتب لكل متجر باستعلام واحد.
     */
    private function buildSalarySummary($user, Collection $storeIds): array
    {
        $employeesWithoutSalary = $user->employees()
            ->with('store:id,name')
            ->where(function ($query) {
                $query->whereNull('salary')->orWhere('salary', '<=', 0);
            })
            ->orderBy('store_id')
            ->orderBy('name')
            ->get();

        $salariesByStore = DB::table('employees')
            ->whereIn('store_id', $storeIds)
            ->whereNull('deleted_at')
            ->groupBy('store_id')
            ->selectRaw('store_id, COALESCE(SUM(salary), 0) as aggregate')
            ->pluck('aggregate', 'store_id');

        $employeeMonthlyWithdrawals = DB::table('employees')
            ->leftJoin('employee_withdrawals', function ($join) {
                $join->on('employees.id', '=', 'employee_withdrawals.person_id')
                    ->where('employee_withdrawals.person_type', Employee::class)
                    ->whereYear('employee_withdrawals.created_at', now()->year)
                    ->whereMonth('employee_withdrawals.created_at', now()->month);
            })
            ->leftJoin('stores', 'employees.store_id', '=', 'stores.id')
            ->whereIn('employees.store_id', $storeIds)
            ->whereNull('employees.deleted_at')
            ->groupBy('employees.id', 'employees.name', 'employees.salary', 'stores.name')
            ->selectRaw('employees.id, employees.name, employees.salary, stores.name as store_name')
            ->selectRaw('COALESCE(SUM(employee_withdrawals.amount), 0) as withdrawals_total')
            ->get();

        $employeeSalaryRemainders = $employeeMonthlyWithdrawals
            ->map(fn ($employee) => [
                'id' => $employee->id,
                'name' => $employee->name,
                'store_name' => $employee->store_name,
                'salary' => (float) $employee->salary,
                'withdrawals_total' => (float) $employee->withdrawals_total,
                'salary_remaining' => max(
                    0,
                    (float) $employee->salary - (float) $employee->withdrawals_total
                ),
            ])
            ->values();

        $monthlySalaries = (float) $salariesByStore->sum();
        $monthlyWorkerWithdrawals = (float) DB::table('employee_withdrawals')
            ->whereIn('store_id', $storeIds)
            ->where('person_type', Employee::class)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return [
            'employeesCount' => $user->employees()->count(),
            'employeesWithoutSalary' => $employeesWithoutSalary,
            'employeesWithoutSalaryCount' => $employeesWithoutSalary->count(),
            'monthlySalaries' => $monthlySalaries,
            'monthlyWorkerWithdrawals' => $monthlyWorkerWithdrawals,
            'netMonthlySalaries' => max(0, $monthlySalaries - $monthlyWorkerWithdrawals),
            'employeeSalaryRemainders' => $employeeSalaryRemainders,
            'salariesByStore' => $salariesByStore,
        ];
    }

    /**
     * مؤشرات المديونيات من المصدر الفعلي employee_credit_sales.
     */
    private function buildCreditSummary(Collection $storeIds): array
    {
        return [
            'creditOpen' => CreditSale::whereIn('store_id', $storeIds)
                ->where('status', 'pending')
                ->where('remaining_amount', '>', 0)
                ->count(),
            'creditClosed' => CreditSale::whereIn('store_id', $storeIds)
                ->where('status', 'deducted')
                ->count(),
            'creditLate' => CreditSale::whereIn('store_id', $storeIds)
                ->where('status', 'pending')
                ->where('remaining_amount', '>', 0)
                ->whereDate('date', '<', now()->subDays(30))
                ->count(),
        ];
    }

    /**
     * قوائم المخزون المنخفض وأفضل المنتجات.
     */
    private function buildInventorySummary(int $userId, Collection $storeIds): array
    {
        $lowStockProducts = Product::with('store')
            ->whereIn('store_id', $storeIds)
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('sale_items')
                    ->whereColumn('sale_items.product_id', 'products.id');
            })
            ->lowStock()
            ->orderBy('quantity')
            ->get();

        $topSellingProducts = Cache::remember(
            "owner-dashboard:{$userId}:top-products:".now()->format('Y-m').':'.$storeIds->sort()->implode('-'),
            now()->addMinutes(5),
            fn () => DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->join('stores', 'sales.store_id', '=', 'stores.id')
                ->whereIn('sales.store_id', $storeIds)
                ->whereYear('sales.created_at', now()->year)
                ->whereMonth('sales.created_at', now()->month)
                ->whereIn('sales.sale_type', self::COLLECTED_SALE_TYPES)
                ->where(function ($query) {
                    $query->whereNull('sales.description')
                        ->orWhere('sales.description', '!=', 'manual_invoice_entry');
                })
                ->whereNull('products.deleted_at')
                ->groupBy('sales.store_id', 'stores.name', 'products.id', 'products.name')
                ->selectRaw('sales.store_id, stores.name as store_name, products.id, products.name')
                ->selectRaw('COUNT(DISTINCT sales.id) as operations_count')
                ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as sold_quantity')
                ->selectRaw('COALESCE(SUM(sale_items.total), 0) as sales_value')
                ->get()
                ->groupBy('store_id')
                ->flatMap(fn ($products) => $products
                    ->sortByDesc('sold_quantity')
                    ->take(5)
                    ->values())
                ->values()
        );

        return [
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount' => $lowStockProducts->count(),
            'topSellingProducts' => $topSellingProducts,
        ];
    }

    /**
     * بناء تفاصيل البطاقات من نتائج مجمعة بدل استعلامات داخل حلقة المتاجر.
     */
    private function buildStoreBreakdowns(
        Collection $stores,
        array $monthlyMetrics,
        Collection $salariesByStore
    ): array
    {
        $storeIds = $stores->pluck('id');
        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();
        $salesTodayByStore = $this->sumCollectedSalesByStore($storeIds, $todayStart, $todayEnd);
        $productsCostTodayByStore = $this->calculateProductsCostByStore(
            $storeIds,
            $todayStart,
            $todayEnd,
            self::COLLECTED_SALE_TYPES
        );
        $expensesTodayByStore = $this->sumByStoreForPeriod(
            'expenses',
            'amount',
            $storeIds,
            $todayStart,
            $todayEnd
        );
        return $stores->map(function ($store) use (
            $salesTodayByStore,
            $productsCostTodayByStore,
            $expensesTodayByStore,
            $salariesByStore,
            $monthlyMetrics
        ) {
            $storeId = $store->id;
            $salesToday = (float) ($salesTodayByStore[$storeId] ?? 0);
            $productsCostToday = (float) ($productsCostTodayByStore[$storeId] ?? 0);
            $month = $monthlyMetrics[$storeId] ?? [];

            return array_merge([
                'store_id' => $storeId,
                'store_name' => $store->name,
                // المصروفات تعرض منفصلة ولا تخصم من ربح اليوم.
                'profit_today' => $salesToday - $productsCostToday,
                'sales_today' => $salesToday,
                'expenses_today' => (float) ($expensesTodayByStore[$storeId] ?? 0),
                'products_cost_today' => $productsCostToday,
                'salaries_month' => (float) ($salariesByStore[$storeId] ?? 0),
            ], $month);
        })->values()->all();
    }

    /**
     * تجميع المبيعات المحصلة حسب المتجر لفترة محددة.
     */
    private function sumCollectedSalesByStore(Collection $storeIds, $start, $end): Collection
    {
        return Sale::query()
            ->collectedDashboardSales()
            ->whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('store_id')
            ->selectRaw('store_id, COALESCE(SUM(paid_amount), 0) as aggregate')
            ->pluck('aggregate', 'store_id');
    }

    /**
     * تجميع عمود مالي حسب المتجر لفترة محددة.
     */
    private function sumByStoreForPeriod(
        string $table,
        string $amountColumn,
        Collection $storeIds,
        $start,
        $end
    ): Collection {
        return DB::table($table)
            ->whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('store_id')
            ->selectRaw("store_id, COALESCE(SUM({$amountColumn}), 0) as aggregate")
            ->pluck('aggregate', 'store_id');
    }

    /**
     * حساب إجمالي تكلفة المنتجات لجميع المتاجر المطلوبة.
     */
    private function calculateProductsCost($storeIds, $start, $end, array $saleTypes): float
    {
        return array_sum($this->calculateProductsCostByStore($storeIds, $start, $end, $saleTypes));
    }

    /**
     * حساب تكلفة المنتجات مجمعة حسب store_id باستعلام واحد.
     */
    private function calculateProductsCostByStore($storeIds, $start, $end, array $saleTypes): array
    {
        static $hasStoredItemCosts;

        $storeIds = collect($storeIds)->map(fn ($id) => (int) $id)->filter()->values();
        if ($storeIds->isEmpty()) {
            return [];
        }

        $hasStoredItemCosts ??= Schema::hasColumn('sale_items', 'total_cost');

        if (! $hasStoredItemCosts) {
            return Sale::query()
                ->excludeManualInvoiceEntries()
                ->whereIn('store_id', $storeIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('sale_type', $saleTypes)
                ->where('products_total', '>', 0)
                ->groupBy('store_id')
                ->selectRaw('store_id, COALESCE(SUM((products_total + labor_total) - profit), 0) as aggregate')
                ->pluck('aggregate', 'store_id')
                ->map(fn ($value) => (float) $value)
                ->all();
        }

        $salesCosts = DB::table('sales')
            ->leftJoin('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->whereIn('sales.store_id', $storeIds)
            ->whereBetween('sales.created_at', [$start, $end])
            ->whereIn('sales.sale_type', $saleTypes)
            ->where(function ($query) {
                $query->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->groupBy(
                'sales.id',
                'sales.store_id',
                'sales.products_total',
                'sales.labor_total',
                'sales.profit'
            )
            ->selectRaw('sales.store_id')
            ->selectRaw('COUNT(sale_items.id) as items_count')
            ->selectRaw('SUM(CASE WHEN sale_items.total_cost IS NOT NULL THEN 1 ELSE 0 END) as costed_items_count')
            ->selectRaw('COALESCE(SUM(sale_items.total_cost), 0) as saved_items_cost')
            ->selectRaw('COALESCE((sales.products_total + sales.labor_total) - sales.profit, 0) as legacy_cost');

        return DB::query()
            ->fromSub($salesCosts, 'sales_costs')
            ->groupBy('store_id')
            ->selectRaw('store_id')
            ->selectRaw(
                'COALESCE(SUM(
                    CASE
                        WHEN items_count = 0 THEN 0
                        WHEN items_count = costed_items_count THEN saved_items_cost
                        ELSE legacy_cost
                    END
                ), 0) as aggregate'
            )
            ->pluck('aggregate', 'store_id')
            ->map(fn ($value) => (float) $value)
            ->all();
    }

    /**
     * تحويل آخر عملية إلى بنية مختصرة للواجهة.
     */
    private function buildLatestOperation(?Sale $latestSale): ?array
    {
        if (! $latestSale) {
            return null;
        }

        $description = trim((string) $latestSale->description);
        $isTintOperation = mb_stripos($description, 'تضليل') !== false
            || mb_stripos($description, 'تظليل') !== false;
        $productNames = $latestSale->items
            ->map(fn ($item) => optional($item->product)->name)
            ->filter()
            ->unique()
            ->values();
        $operationName = $isTintOperation
            ? $description
            : ($productNames->isNotEmpty()
                ? $productNames->implode(' - ')
                : ($description ?: ((float) $latestSale->labor_total > 0 ? 'شغل يد' : 'عملية بيع')));

        return [
            'id' => (int) $latestSale->id,
            'store_name' => $latestSale->store->name ?? 'متجر غير معروف',
            'description' => $operationName,
            'is_tint' => $isTintOperation,
            'amount' => (float) ($latestSale->paid_amount ?? 0),
            'time' => optional($latestSale->created_at)->format('h:i A'),
        ];
    }

    /**
     * تجهيز مخطط آخر 14 يومًا من المبيعات والمصروفات والدين المتبقي الفعلي.
     */
    private function prepareChartData(Collection $storeIds): array
    {
        $chartStart = now()->subDays(13)->startOfDay();
        $chartEnd = now()->endOfDay();

        $dailySales = Sale::query()
            ->collectedDashboardSales()
            ->selectRaw('DATE(created_at) as day, SUM(paid_amount) as total')
            ->whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $dailyExpenses = Expense::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $dailyRemainingCredit = CreditSale::selectRaw('DATE(date) as day, SUM(remaining_amount) as total')
            ->whereIn('store_id', $storeIds)
            ->where('status', 'pending')
            ->where('remaining_amount', '>', 0)
            ->whereBetween('date', [$chartStart->toDateString(), $chartEnd->toDateString()])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $sales = [];
        $expenses = [];
        $remainingCredit = [];

        for ($dayOffset = 0; $dayOffset < 14; $dayOffset++) {
            $date = $chartStart->copy()->addDays($dayOffset)->toDateString();
            $labels[] = $date;
            $sales[] = (float) ($dailySales[$date]->total ?? 0);
            $expenses[] = (float) ($dailyExpenses[$date]->total ?? 0);
            $remainingCredit[] = (float) ($dailyRemainingCredit[$date]->total ?? 0);
        }

        return [
            'chartLabels' => $labels,
            'chartSales' => $sales,
            'chartExpenses' => $expenses,
            'chartCredit' => $remainingCredit,
        ];
    }

    /**
     * بيانات آمنة عندما لا يملك المستخدم متاجر.
     */
    private function emptyStateData($user, Collection $stores): array
    {
        return [
            'stores' => $stores,
            'user' => $user,
            'selectedSummaryStore' => null,
            'employeesCount' => 0,
            'daysLeft' => 0,
            'salesToday' => 0,
            'salesMonth' => 0,
            'productsCostToday' => 0,
            'expensesToday' => 0,
            'expensesMonth' => 0,
            'profitToday' => 0,
            'profitMonth' => 0,
            'monthlySalaries' => 0,
            'monthlyWorkerWithdrawals' => 0,
            'netMonthlySalaries' => 0,
            'monthlyOwnerPurchases' => 0,
            'monthlyAccountantConsumption' => 0,
            'monthlyPurchasesAndConsumption' => 0,
            'creditOpen' => 0,
            'metricStoreBreakdowns' => [],
            'dailySalesOperationsCount' => 0,
            'lowStockCount' => 0,
            'lowStockProducts' => collect(),
            'topSellingProducts' => collect(),
            'employeeSalaryRemainders' => collect(),
            'employeesWithoutSalary' => collect(),
            'employeesWithoutSalaryCount' => 0,
            'creditClosed' => 0,
            'creditLate' => 0,
            'activities' => collect(),
            'chartLabels' => [],
            'chartSales' => [],
            'chartExpenses' => [],
            'chartCredit' => [],
        ];
    }
}
