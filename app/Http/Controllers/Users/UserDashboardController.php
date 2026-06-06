<?php

namespace App\Http\Controllers\Users;

use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\CreditSale;
use App\Models\DailyBalance;
use App\Models\Withdrawal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('web')->user();

        // جلب المتاجر
        $stores = $user->stores;
        $storeIds = $stores->pluck('id');

        // حالة المتاجر 0: إذا لم يكن هناك متاجر، نرسل بيانات صفرية لتجنب أخطاء SQL
        if ($storeIds->isEmpty()) {
            return view('dashboard.user.index', $this->emptyStateData($user, $stores));
        }

        // المحاسبين والموظفين
        $accountantsCount = $user->accountants()->count();
        $employeesCount   = $user->employees()->count();
        $employeesWithoutSalary = $user->employees()
            ->where(function ($query) {
                $query->whereNull('employees.salary')
                    ->orWhere('employees.salary', '<=', 0);
            })
            ->with('store:id,name')
            ->select('employees.id', 'employees.store_id', 'employees.name', 'employees.salary')
            ->orderBy('employees.name')
            ->get();
        $employeesWithoutSalaryCount = $employeesWithoutSalary->count();

        // الاشتراك
        $subscriptionEnd  = $user->subscription_end_at;
        $daysLeft         = $subscriptionEnd ? now()->diffInDays($subscriptionEnd, false) : null;

        /*
        |--------------------------------------------------------------------------
        | المبيعات (تم التعديل بناءً على هيكل جداولك)
        |--------------------------------------------------------------------------
        */

        $todayStart = today()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // يمكن للمالك مقارنة متجر محدد مباشرة بصفحة مبيعاته اليومية.
        // عند عدم اختيار متجر تبقى البطاقات إجمالاً لجميع المتاجر.
        $requestedSummaryStoreId = $request->integer('summary_store_id');
        $selectedSummaryStore = $requestedSummaryStoreId > 0
            ? $stores->firstWhere('id', $requestedSummaryStoreId)
            : null;
        $summaryStores = $selectedSummaryStore ? collect([$selectedSummaryStore]) : $stores;

        // نبني ملخص كل متجر من نفس فترات الشفتات ونفس العمليات التي تعتمدها
        // صفحة المبيعات اليومية، ثم نجمع النطاق المختار فقط في البطاقات.
        $dailyStoreSummaries = $stores->mapWithKeys(function ($store) use ($todayStart) {
            return [$store->id => $this->dailyStoreFinancialSummary($store->id, $todayStart)];
        });
        $selectedDailySummaries = $dailyStoreSummaries->only($summaryStores->pluck('id')->all());
        $salesToday = (float) $selectedDailySummaries->sum('sales_value');
        $productsCostToday = (float) $selectedDailySummaries->sum('total_cost');
        $profitToday = (float) $selectedDailySummaries->sum('recognized_profit');
        $expensesToday = (float) $selectedDailySummaries->sum('expenses');
        $dailySalesOperationsCount = (int) $selectedDailySummaries->sum('operations_count');
        $dailyCashSales = (float) $selectedDailySummaries->sum('cash_sales');
        $dailyCardSales = (float) $selectedDailySummaries->sum('card_sales');

        // الملخص الشهري يستخدم نفس مصادر ومعادلة تقرير المتجر الشهري.
        $monthlyStoreSummaries = $stores->mapWithKeys(function ($store) use ($monthStart, $monthEnd) {
            return [$store->id => $this->monthlyStoreFinancialSummary($store->id, $monthStart, $monthEnd)];
        });
        $salesMonth = (float) $monthlyStoreSummaries->sum('total_sales');
        $productsCostMonth = (float) $monthlyStoreSummaries->sum('products_cost');
        $expensesMonth = (float) $monthlyStoreSummaries->sum('expenses');

        // إجمالي رواتب جميع موظفي متاجر المالك (حمولة ثابتة شهرية)
        $monthlySalaries = $user->employees()->sum('salary') ?? 0;
        $monthlyWorkerWithdrawals = (float) Withdrawal::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount');
        $netMonthlySalaries = max(0, (float) $monthlySalaries - $monthlyWorkerWithdrawals);

        $monthlyOwnerPurchases = (float) $monthlyStoreSummaries->sum('owner_purchases');
        $monthlyAccountantConsumption = (float) $monthlyStoreSummaries->sum('internal_use');
        $monthlyPurchasesAndConsumption = $monthlyOwnerPurchases + $monthlyAccountantConsumption;

        // نفس معادلة التقرير الشهري: الرواتب والسحوبات للتوضيح فقط ولا تُخصم من النتيجة.
        $profitMonth = $salesMonth
            - $productsCostMonth
            - $monthlyAccountantConsumption
            - $monthlyOwnerPurchases
            - $expensesMonth;

        /* [تعديل آمن] تحليل المديونيات من جدول employee_credit_sales (المصدر الفعلي للآجل) */
        $creditOpen = CreditSale::whereIn('store_id', $storeIds)
            ->where('status', 'pending')
            ->where('remaining_amount', '>', 0)
            ->count();

        $creditClosed = CreditSale::whereIn('store_id', $storeIds)
            ->where('status', 'deducted')
            ->count();

        $creditLate = CreditSale::whereIn('store_id', $storeIds)
            ->where('status', 'pending')
            ->where('remaining_amount', '>', 0)
            ->whereDate('date', '<', now()->subDays(30))
            ->count();

        /* المخطط البياني للـ 14 يوم الأخيرة */
        $chartData = $this->prepareChartData($storeIds);

        /* آخر العمليات */
        $activities = Log::with('store')->whereIn('store_id', $storeIds)->latest()->limit(10)->get();

        // تفاصيل كل مؤشر لكل متجر (لاستخدامها في نافذة تفاصيل البطاقات)
        $metricStoreBreakdowns = [];
        foreach ($stores as $store) {
            $storeId = $store->id;

            $singleStoreIds = [$storeId];
            $storeDailySummary = $dailyStoreSummaries->get($storeId, [
                'sales_value' => 0,
                'total_cost' => 0,
                'recognized_profit' => 0,
                'expenses' => 0,
                'operations_count' => 0,
                'cash_sales' => 0,
                'card_sales' => 0,
            ]);
            $storeSalesToday = $storeDailySummary['sales_value'];
            $storeProductsCostToday = $storeDailySummary['total_cost'];
            $storeProfitToday = $storeDailySummary['recognized_profit'];
            $storeExpensesToday = $storeDailySummary['expenses'];

            $storeMonthlySummary = $monthlyStoreSummaries->get($storeId, [
                'total_sales' => 0,
                'products_cost' => 0,
                'expenses' => 0,
                'owner_purchases' => 0,
                'internal_use' => 0,
            ]);
            $storeSalesMonth = $storeMonthlySummary['total_sales'];
            $storeProductsCostMonth = $storeMonthlySummary['products_cost'];
            $storeExpensesMonth = $storeMonthlySummary['expenses'];

            $storeSalariesMonth = (float) $store->employees()->sum('salary');
            $storeWorkerWithdrawalsMonth = (float) Withdrawal::where('store_id', $storeId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
            $storeNetSalariesMonth = max(0, $storeSalariesMonth - $storeWorkerWithdrawalsMonth);

            $storeOwnerPurchasesMonth = $storeMonthlySummary['owner_purchases'];
            $storeAccountantConsumptionMonth = $storeMonthlySummary['internal_use'];

            $storeProfitMonth = (float) $storeSalesMonth
                - (float) $storeProductsCostMonth
                - (float) $storeExpensesMonth
                - (float) $storeOwnerPurchasesMonth
                - (float) $storeAccountantConsumptionMonth;

            $metricStoreBreakdowns[] = [
                'store_id' => (int) $store->id,
                'store_name' => $store->name,
                'profit_today' => (float) $storeProfitToday,
                'sales_today' => (float) $storeSalesToday,
                'expenses_today' => (float) $storeExpensesToday,
                'products_cost_today' => (float) $storeProductsCostToday,
                'profit_month' => (float) $storeProfitMonth,
                'sales_month' => (float) $storeSalesMonth,
                'expenses_month' => (float) $storeExpensesMonth,
                'salaries_month' => (float) $storeSalariesMonth,
                'monthly_owner_purchases' => (float) $storeOwnerPurchasesMonth,
                'monthly_accountant_consumption' => (float) $storeAccountantConsumptionMonth,
                'monthly_purchases_consumption' => (float) $storeOwnerPurchasesMonth + (float) $storeAccountantConsumptionMonth,
            ];
        }

        return view('dashboard.user.index', array_merge(compact(
            'stores', 'accountantsCount', 'employeesCount', 'employeesWithoutSalary', 'employeesWithoutSalaryCount',
            'daysLeft', 'salesToday', 'salesMonth', 'productsCostToday',
            'expensesToday', 'expensesMonth', 'profitToday', 'profitMonth',
            'dailySalesOperationsCount', 'dailyCashSales', 'dailyCardSales',
            'monthlySalaries', 'monthlyWorkerWithdrawals', 'netMonthlySalaries',
            'monthlyOwnerPurchases', 'monthlyAccountantConsumption', 'monthlyPurchasesAndConsumption',
            'creditOpen', 'metricStoreBreakdowns',
            'creditClosed', 'creditLate', 'user', 'activities', 'selectedSummaryStore'
        ), $chartData));
    }

    /**
     * يعيد ملخص متجر واحد مطابقاً لبطاقات صفحة المبيعات اليومية عند عرض تاريخ اليوم.
     */
    private function dailyStoreFinancialSummary(int $storeId, Carbon $selectedDate): array
    {
        $windows = $this->dailySalesWindows($storeId, $selectedDate);
        $saleItemsCosts = DB::table('sale_items')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->selectRaw('sale_items.sale_id, SUM(COALESCE(products.cost_price, 0) * COALESCE(sale_items.custom_consumption, sale_items.quantity, 0)) as total_cost')
            ->groupBy('sale_items.sale_id');

        $salesQuery = DB::table('sales')
            ->leftJoinSub($saleItemsCosts, 'sale_item_costs', function ($join) {
                $join->on('sales.id', '=', 'sale_item_costs.sale_id');
            })
            ->where('sales.store_id', $storeId)
            ->where(function ($query) {
                $query->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            });
        $this->applyDailyWindows($salesQuery, $windows, 'sales.created_at');

        $summary = $salesQuery
            ->selectRaw('COUNT(sales.id) as operations_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.sale_type = "cash" THEN sales.paid_amount WHEN sales.sale_type = "mixed" THEN sales.cash_amount ELSE 0 END), 0) as cash_sales')
            ->selectRaw('COALESCE(SUM(CASE WHEN sales.sale_type = "card" THEN sales.paid_amount WHEN sales.sale_type = "mixed" THEN sales.card_amount ELSE 0 END), 0) as card_sales')
            ->selectRaw('COALESCE(SUM(CASE
                WHEN (COALESCE(sales.cash_amount, 0) + COALESCE(sales.card_amount, 0)) > COALESCE(sales.paid_amount, 0)
                THEN COALESCE(sales.cash_amount, 0) + COALESCE(sales.card_amount, 0)
                ELSE COALESCE(sales.paid_amount, 0)
            END), 0) as sales_value')
            ->selectRaw('COALESCE(SUM(COALESCE(sale_item_costs.total_cost, 0)), 0) as total_cost')
            ->selectRaw('COALESCE(SUM(CASE
                WHEN COALESCE(sales.remaining_amount, 0) > 0
                    AND (sales.sale_type IN ("credit", "mixed") OR COALESCE(sales.has_partial_credit, 0) = 1)
                THEN 0
                ELSE COALESCE(sales.paid_amount, 0) - COALESCE(sale_item_costs.total_cost, 0)
            END), 0) as recognized_profit')
            ->first();

        $expensesQuery = Expense::where('store_id', $storeId)
            ->where('actor_type', '!=', 'owner_purchase');
        $this->applyDailyWindows($expensesQuery, $windows, 'created_at');

        return [
            // يطابق حقل "قيمة المبيعات" في ملخص صفحة المبيعات، ولا يضيف تحصيلات الآجل المستقلة.
            'sales_value' => (float) ($summary->sales_value ?? 0),
            'total_cost' => (float) ($summary->total_cost ?? 0),
            'recognized_profit' => (float) ($summary->recognized_profit ?? 0),
            'expenses' => (float) $expensesQuery->sum('amount'),
            'operations_count' => (int) ($summary->operations_count ?? 0),
            'cash_sales' => (float) ($summary->cash_sales ?? 0),
            'card_sales' => (float) ($summary->card_sales ?? 0),
        ];
    }

    private function dailySalesWindows(int $storeId, Carbon $selectedDate)
    {
        $dayStart = $selectedDate->copy()->startOfDay();
        $dayEnd = $selectedDate->copy()->endOfDay();

        $windows = DailyBalance::where('store_id', $storeId)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->whereDate('created_at', $selectedDate->toDateString())
            ->orderBy('start_time')
            ->get()
            ->map(fn ($balance) => [
                'start' => Carbon::parse($balance->start_time),
                'end' => Carbon::parse($balance->end_time),
            ]);

        $hasClosedWindows = $windows->isNotEmpty();

        if ($selectedDate->isToday()) {
            $lastClosed = DailyBalance::where('store_id', $storeId)
                ->whereNotNull('end_time')
                ->latest('end_time')
                ->first();
            $openStart = $lastClosed ? Carbon::parse($lastClosed->end_time) : $dayStart;

            if ($openStart->lt(now())) {
                $windows->push(['start' => $openStart, 'end' => now()]);
            }
        }

        if ($windows->isEmpty()) {
            return collect([['start' => $dayStart, 'end' => $dayEnd]]);
        }

        $windows = $windows->sortBy('start')->values();

        // نفس fallback في صفحة المبيعات: إذا كانت هناك شفتات مغلقة ولكنها لم تُرجع
        // أي عملية، تُعرض الفترة اليومية التقويمية للتاريخ المحدد.
        if ($hasClosedWindows) {
            $salesExist = Sale::where('store_id', $storeId)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                });
            $this->applyDailyWindows($salesExist, $windows, 'created_at');

            if (!$salesExist->exists()) {
                return collect([['start' => $dayStart, 'end' => $dayEnd]]);
            }
        }

        return $windows;
    }

    private function applyDailyWindows($query, $windows, string $column): void
    {
        $query->where(function ($periodQuery) use ($windows, $column) {
            foreach ($windows as $window) {
                $periodQuery->orWhereBetween($column, [$window['start'], $window['end']]);
            }
        });
    }

    /**
     * نفس مصادر التقرير الشهري في StoreController::reportsMonthly.
     */
    private function monthlyStoreFinancialSummary(int $storeId, Carbon $start, Carbon $end): array
    {
        $salesQuery = Sale::where('store_id', $storeId)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type', ['cash', 'card', 'credit', 'mixed']);

        if (Schema::hasColumn('sale_items', 'total_cost')) {
            $productsCost = (float) DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->where('sales.store_id', $storeId)
                ->whereBetween('sales.created_at', [$start, $end])
                ->whereIn('sales.sale_type', ['cash', 'card', 'credit', 'mixed'])
                ->where(function ($query) {
                    $query->whereNull('sales.description')
                        ->orWhere('sales.description', '!=', 'manual_invoice_entry');
                })
                ->sum(DB::raw('COALESCE(sale_items.total_cost, 0)'));
        } else {
            $productsCost = (float) Sale::where('store_id', $storeId)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('sale_type', ['cash', 'card', 'credit', 'mixed'])
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('products_total');
        }

        $internalUse = (float) Sale::where('store_id', $storeId)
            ->where('sale_type', 'internal_use')
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('total');

        return [
            'total_sales' => (float) (clone $salesQuery)->sum('paid_amount'),
            'products_cost' => $productsCost,
            'expenses' => (float) Expense::where('store_id', $storeId)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'owner_purchases' => (float) Purchase::where('store_id', $storeId)
                ->whereBetween('created_at', [$start, $end])
                ->sum('cost'),
            'internal_use' => $internalUse,
        ];
    }

    private function prepareChartData($storeIds)
    {
        $chartStart = now()->subDays(13)->startOfDay();
        $chartEnd   = now()->endOfDay();

        $dailySales = Sale::selectRaw('DATE(created_at) as day, SUM(CASE WHEN (COALESCE(cash_amount, 0) + COALESCE(card_amount, 0)) > COALESCE(paid_amount, 0) THEN (COALESCE(cash_amount, 0) + COALESCE(card_amount, 0)) ELSE COALESCE(paid_amount, 0) END) as total, SUM(CASE WHEN sale_type = "credit" THEN paid_amount ELSE 0 END) as credit')
            ->whereIn('store_id', $storeIds)
            ->whereIn('sale_type', ['cash', 'card', 'credit', 'mixed'])
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->groupBy('day')->get()->keyBy('day');

        $dailyExpenses = Expense::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereIn('store_id', $storeIds)
            ->where(function ($query) {
                $query->whereNull('actor_type')
                    ->orWhere('actor_type', '!=', 'owner_purchase');
            })
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')->get()->keyBy('day');

        $labels = []; $sales = []; $exps = []; $credits = [];

        for ($i = 0; $i < 14; $i++) {
            $date = $chartStart->copy()->addDays($i)->toDateString();
            $labels[]  = $date;
            $sales[]   = $dailySales[$date]->total ?? 0;
            $credits[] = $dailySales[$date]->credit ?? 0;
            $exps[]    = $dailyExpenses[$date]->total ?? 0;
        }

        return ['chartLabels' => $labels, 'chartSales' => $sales, 'chartExpenses' => $exps, 'chartCredit' => $credits];
    }

    private function emptyStateData($user, $stores)
    {
        return [
            'stores' => $stores, 'user' => $user, 'accountantsCount' => 0, 'employeesCount' => 0,
            'employeesWithoutSalary' => collect(), 'employeesWithoutSalaryCount' => 0,
            'daysLeft' => 0, 'salesToday' => 0, 'salesMonth' => 0, 'productsCostToday' => 0, 'expensesToday' => 0,
            'expensesMonth' => 0, 'profitToday' => 0, 'profitMonth' => 0,
            'dailySalesOperationsCount' => 0, 'dailyCashSales' => 0, 'dailyCardSales' => 0,
            'monthlySalaries' => 0, 'monthlyWorkerWithdrawals' => 0, 'netMonthlySalaries' => 0,
            'monthlyOwnerPurchases' => 0, 'monthlyAccountantConsumption' => 0, 'monthlyPurchasesAndConsumption' => 0,
            'creditOpen' => 0,
            'metricStoreBreakdowns' => [], 'selectedSummaryStore' => null,
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [], 'chartCredit' => []
        ];
    }
}
