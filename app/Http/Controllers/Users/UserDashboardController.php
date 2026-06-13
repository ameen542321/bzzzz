<?php

namespace App\Http\Controllers\Users;

use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();

        // جلب المتاجر
        $stores = $user->stores;
        $storeIds = $stores->pluck('id');

        // الفلتر اليومي الاختياري الموجود في واجهة لوحة المالك.
        // لا يُقبل إلا متجر تابع للمالك الحالي.
        $selectedSummaryStore = null;
        if ($requestedStoreId = request()->integer('summary_store_id')) {
            $selectedSummaryStore = $stores->firstWhere('id', $requestedStoreId);
        }
        $dailyStoreIds = $selectedSummaryStore
            ? collect([$selectedSummaryStore->id])
            : $storeIds;

        // حالة المتاجر 0: إذا لم يكن هناك متاجر، نرسل بيانات صفرية لتجنب أخطاء SQL
        if ($storeIds->isEmpty()) {
            return view('dashboard.user.index', $this->emptyStateData($user, $stores));
        }

        // المحاسبين والموظفين
        $accountantsCount = $user->accountants()->count();
        $employeesCount   = $user->employees()->count();

        // الاشتراك
        $subscriptionEnd  = $user->subscription_end_at;
        $daysLeft         = $subscriptionEnd ? now()->diffInDays($subscriptionEnd, false) : null;

        /*
        |--------------------------------------------------------------------------
        | المبيعات (تم التعديل بناءً على هيكل جداولك)
        |--------------------------------------------------------------------------
        */

        // [تعديل آمن] المبيعات في الداشبورد تُحسب من المبالغ المُحصّلة فعليًا (paid_amount)
        // بدل إجمالي الفواتير، لتطابق الواقع النقدي الفعلي للمتاجر التابعة.
        $includedSaleTypes = ['cash', 'card', 'credit', 'mixed'];

        // مبيعات اليوم (محصّل فعلي) مع استبعاد القيود المرتبطة بالفاتورة اليدوية
        $salesToday = Sale::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        $dailySalesOperationsCount = Sale::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->count();

        $dailyCashSales = Sale::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('cash_amount');

        $dailyCardSales = Sale::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('card_amount');

        // تكلفة المنتجات فقط من تكاليف أسطر البيع المحفوظة؛ عملية شغل اليد
        // التي لا تحتوي منتجات تكون تكلفتها صفر ولا تُعامل كتكلفة منتج.
        $productsCostToday = $this->calculateProductsCost(
            $dailyStoreIds,
            today()->startOfDay(),
            today()->endOfDay(),
            $includedSaleTypes
        );


        // مبيعات الشهر (محصّل فعلي) مع استبعاد الفواتير اليدوية من المؤشر
        $salesMonth = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        $productsCostMonth = $this->calculateProductsCost(
            $storeIds,
            now()->startOfMonth(),
            now()->endOfMonth(),
            $includedSaleTypes
        );

        /* المصروفات - نستخدم عمود amount كما هو في جدولك */
        $expensesToday = Expense::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->sum('amount');

        $expensesMonth = Expense::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // إجمالي رواتب جميع موظفي متاجر المالك (حمولة ثابتة شهرية)
        $monthlySalaries = $user->employees()->sum('salary') ?? 0;
        $monthlyWorkerWithdrawals = (float) Withdrawal::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');
        $netMonthlySalaries = max(0, (float) $monthlySalaries - $monthlyWorkerWithdrawals);

        /* صافي الربح - في هذا النظام المبلغ المحصل هو أساس البيع */
        $profitToday = Sale::whereIn('store_id', $dailyStoreIds)
            ->whereDate('created_at', today())
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount') - $productsCostToday;

        $monthlyOwnerPurchases = Purchase::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('cost');

        // استهلاك المحاسب الشهري (internal_use) بعد استبعاد قيود الفواتير اليدوية
        $monthlyAccountantConsumption = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('total');

        $monthlyPurchasesAndConsumption = (float) $monthlyOwnerPurchases + (float) $monthlyAccountantConsumption;

        // مطابق لوصف الواجهة: المحصل ناقص تكلفة المنتجات والمصروفات
        // ومشتريات المالك والاستهلاك الداخلي. الرواتب معروضة للتوضيح فقط.
        $profitMonth = (float) $salesMonth
            - (float) $productsCostMonth
            - $expensesMonth
            - $monthlyPurchasesAndConsumption;

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

        // لا يوجد في المشروع الحالي جدول مستقل للشفتات المفتوحة؛ نحافظ على
        // توافق الواجهة بقائمة فارغة بدل إنشاء مصدر بيانات غير موجود.
        $longOpenShifts = collect();

        $employeesWithoutSalary = Employee::with('store')
            ->whereIn('store_id', $storeIds)
            ->where(function ($query) {
                $query->whereNull('salary')->orWhere('salary', '<=', 0);
            })
            ->get();
        $employeesWithoutSalaryCount = $employeesWithoutSalary->count();

        $lowStockProducts = Product::with('store')
            ->whereIn('store_id', $storeIds)
            ->where('quantity', '>', 0)
            ->lowStock()
            ->orderBy('quantity')
            ->get();
        $lowStockCount = $lowStockProducts->count();

        $employeeMonthlyWithdrawals = DB::table('employees')
            ->leftJoin('employee_withdrawals', function ($join) {
                $join->on('employees.id', '=', 'employee_withdrawals.employee_id')
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
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'store_name' => $employee->store_name,
                    'salary' => (float) $employee->salary,
                    'withdrawals_total' => (float) $employee->withdrawals_total,
                    'salary_remaining' => max(
                        0,
                        (float) $employee->salary - (float) $employee->withdrawals_total
                    ),
                ];
            })
            ->values();

        $topSellingProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('stores', 'sales.store_id', '=', 'stores.id')
            ->whereIn('sales.store_id', $storeIds)
            ->whereYear('sales.created_at', now()->year)
            ->whereMonth('sales.created_at', now()->month)
            ->whereIn('sales.sale_type', $includedSaleTypes)
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
            ->map(fn ($products) => $products->sortByDesc('sold_quantity')->first())
            ->filter()
            ->values();

        // تفاصيل كل مؤشر لكل متجر (لاستخدامها في نافذة تفاصيل البطاقات)
        $metricStoreBreakdowns = [];
        foreach ($stores as $store) {
            $storeId = $store->id;

            $storeSalesToday = Sale::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->whereIn('sale_type', $includedSaleTypes)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('paid_amount');

            $storeProductsCostToday = $this->calculateProductsCost(
                [$storeId],
                today()->startOfDay(),
                today()->endOfDay(),
                $includedSaleTypes
            );

            $storeExpensesToday = Expense::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->sum('amount');

            $storePaidToday = Sale::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('paid_amount');
            $storeProfitToday = (float) $storePaidToday - (float) $storeProductsCostToday;

            $storeSalesMonth = Sale::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->whereIn('sale_type', $includedSaleTypes)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('paid_amount');

            $storeProductsCostMonth = $this->calculateProductsCost(
                [$storeId],
                now()->startOfMonth(),
                now()->endOfMonth(),
                $includedSaleTypes
            );

            $storeExpensesMonth = Expense::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount');

            $storeSalariesMonth = (float) $store->employees()->sum('salary');
            $storeWorkerWithdrawalsMonth = (float) Withdrawal::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount');
            $storeNetSalariesMonth = max(0, $storeSalariesMonth - $storeWorkerWithdrawalsMonth);

            $storeOwnerPurchasesMonth = Purchase::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('cost');

            $storeAccountantConsumptionMonth = Sale::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->where('sale_type', 'internal_use')
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('total');

            $storeProfitMonth = (float) $storeSalesMonth
                - (float) $storeProductsCostMonth
                - (float) $storeExpensesMonth
                - (float) $storeOwnerPurchasesMonth
                - (float) $storeAccountantConsumptionMonth;

            $metricStoreBreakdowns[] = [
                'store_id' => $storeId,
                'store_name' => $store->name,
                'profit_today' => (float) $storeProfitToday,
                'sales_today' => (float) $storeSalesToday,
                'expenses_today' => (float) $storeExpensesToday,
                'products_cost_today' => (float) $storeProductsCostToday,
                'profit_month' => (float) $storeProfitMonth,
                'sales_month' => (float) $storeSalesMonth,
                'expenses_month' => (float) $storeExpensesMonth,
                'products_cost_month' => (float) $storeProductsCostMonth,
                'salaries_month' => (float) $storeNetSalariesMonth,
                'withdrawals_month' => (float) $storeWorkerWithdrawalsMonth,
                'salary_remaining_month' => (float) $storeNetSalariesMonth,
                'monthly_owner_purchases' => (float) $storeOwnerPurchasesMonth,
                'monthly_accountant_consumption' => (float) $storeAccountantConsumptionMonth,
                'monthly_purchases_consumption' => (float) $storeOwnerPurchasesMonth + (float) $storeAccountantConsumptionMonth,
            ];
        }

        $storePerformance = collect($metricStoreBreakdowns)->sortByDesc('profit_month')->values();
        $bestStorePerformance = $storePerformance->first();
        $worstStorePerformance = $storePerformance->count() > 1
            ? $storePerformance->last()
            : null;

        return view('dashboard.user.index', array_merge(compact(
            'stores', 'accountantsCount', 'employeesCount', 'daysLeft', 'salesToday', 'salesMonth', 'productsCostToday',
            'productsCostMonth', 'expensesToday', 'expensesMonth', 'profitToday', 'profitMonth',
            'monthlySalaries', 'monthlyWorkerWithdrawals', 'netMonthlySalaries',
            'monthlyOwnerPurchases', 'monthlyAccountantConsumption', 'monthlyPurchasesAndConsumption',
            'creditOpen', 'metricStoreBreakdowns', 'selectedSummaryStore',
            'dailySalesOperationsCount', 'dailyCashSales', 'dailyCardSales',
            'longOpenShifts', 'employeesWithoutSalaryCount', 'employeesWithoutSalary',
            'lowStockCount', 'lowStockProducts', 'topSellingProducts',
            'bestStorePerformance', 'worstStorePerformance',
            'employeeMonthlyWithdrawals', 'employeeSalaryRemainders',
            'creditClosed', 'creditLate', 'user', 'activities'
        ), $chartData));
    }

    /**
     * حساب تكلفة المنتجات من التكلفة المحفوظة وقت البيع.
     *
     * عمليات شغل اليد التي لا تحتوي sale_items لا تدخل في تكلفة المنتجات.
     * إذا كان سطر قديم لا يحتوي total_cost نستخدم معادلة العملية السابقة
     * لذلك السطر فقط، دون اعتبار عمليات شغل اليد منتجات.
     */
    private function calculateProductsCost($storeIds, $start, $end, array $saleTypes): float
    {
        $storeIds = collect($storeIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        if (empty($storeIds)) {
            return 0.0;
        }

        if (! Schema::hasColumn('sale_items', 'total_cost')) {
            return (float) Sale::whereIn('store_id', $storeIds)
                ->whereBetween('created_at', [$start, $end])
                ->whereIn('sale_type', $saleTypes)
                ->where('products_total', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->selectRaw('COALESCE(SUM((products_total + labor_total) - profit), 0) as products_cost')
                ->value('products_cost');
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
                'sales.products_total',
                'sales.labor_total',
                'sales.profit'
            )
            ->selectRaw('COUNT(sale_items.id) as items_count')
            ->selectRaw('SUM(CASE WHEN sale_items.total_cost IS NOT NULL THEN 1 ELSE 0 END) as costed_items_count')
            ->selectRaw('COALESCE(SUM(sale_items.total_cost), 0) as saved_items_cost')
            ->selectRaw('COALESCE((sales.products_total + sales.labor_total) - sales.profit, 0) as legacy_cost');

        return (float) DB::query()
            ->fromSub($salesCosts, 'sales_costs')
            ->selectRaw(
                'COALESCE(SUM(
                    CASE
                        WHEN items_count = 0 THEN 0
                        WHEN items_count = costed_items_count THEN saved_items_cost
                        ELSE legacy_cost
                    END
                ), 0) as total_cost'
            )
            ->value('total_cost');
    }

    private function prepareChartData($storeIds)
    {
        $chartStart = now()->subDays(13)->startOfDay();
        $chartEnd   = now()->endOfDay();

        $dailySales = Sale::selectRaw('DATE(created_at) as day, SUM(paid_amount) as total, SUM(CASE WHEN sale_type = "credit" THEN paid_amount ELSE 0 END) as credit')
            ->whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$chartStart, $chartEnd])
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->groupBy('day')->get()->keyBy('day');

        $dailyExpenses = Expense::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereIn('store_id', $storeIds)->whereBetween('created_at', [$chartStart, $chartEnd])
            ->groupBy('day')->get()->keyBy('day');

        $labels = []; $sales = []; $exps = []; $credits = []; $productCosts = [];
        $includedSaleTypes = ['cash', 'card', 'credit', 'mixed'];

        for ($i = 0; $i < 14; $i++) {
            $date = $chartStart->copy()->addDays($i)->toDateString();
            $labels[]  = $date;
            $sales[]   = $dailySales[$date]->total ?? 0;
            $credits[] = $dailySales[$date]->credit ?? 0;
            $exps[]    = $dailyExpenses[$date]->total ?? 0;
            $productCosts[] = $this->calculateProductsCost(
                $storeIds,
                $chartStart->copy()->addDays($i)->startOfDay(),
                $chartStart->copy()->addDays($i)->endOfDay(),
                $includedSaleTypes
            );
        }

        return [
            'chartLabels' => $labels,
            'chartSales' => $sales,
            'chartExpenses' => $exps,
            'chartCredit' => $credits,
            'chartProductCosts' => $productCosts,
        ];
    }

    private function emptyStateData($user, $stores)
    {
        return [
            'stores' => $stores, 'user' => $user, 'accountantsCount' => 0, 'employeesCount' => 0,
            'daysLeft' => 0, 'salesToday' => 0, 'salesMonth' => 0, 'productsCostToday' => 0, 'expensesToday' => 0,
            'productsCostMonth' => 0, 'expensesMonth' => 0, 'profitToday' => 0, 'profitMonth' => 0,
            'monthlySalaries' => 0, 'monthlyWorkerWithdrawals' => 0, 'netMonthlySalaries' => 0,
            'monthlyOwnerPurchases' => 0, 'monthlyAccountantConsumption' => 0,
            'monthlyPurchasesAndConsumption' => 0, 'creditOpen' => 0,
            'metricStoreBreakdowns' => [],
            'selectedSummaryStore' => null,
            'dailySalesOperationsCount' => 0, 'dailyCashSales' => 0, 'dailyCardSales' => 0,
            'longOpenShifts' => collect(),
            'employeesWithoutSalaryCount' => 0, 'employeesWithoutSalary' => collect(),
            'lowStockCount' => 0, 'lowStockProducts' => collect(), 'topSellingProducts' => collect(),
            'bestStorePerformance' => null, 'worstStorePerformance' => null,
            'employeeMonthlyWithdrawals' => collect(), 'employeeSalaryRemainders' => collect(),
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [],
            'chartCredit' => [], 'chartProductCosts' => []
        ];
    }
}
