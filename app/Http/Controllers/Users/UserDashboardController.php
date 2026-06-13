<?php

namespace App\Http\Controllers\Users;

use App\Models\Log;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\CreditSale;
use App\Models\Withdrawal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = auth('web')->user();

        // جلب المتاجر
        $stores = $user->stores;
        $storeIds = $stores->pluck('id');

        // قيمة افتراضية آمنة للمتجر المعروض في ملخص اللوحة.
        // بعض نسخ واجهة لوحة المالك تستخدم هذا المتغير لاختيار متجر الملخص،
        // لذلك يجب إرساله دائمًا حتى عند عدم تمرير اختيار صريح من الطلب.
        $selectedSummaryStore = $stores->first();

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
        $salesToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('paid_amount');

        // تكلفة المنتجات المباعة اليوم (لنفس نطاق المبيعات المعتمد أعلاه)
        $productsCostToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->whereIn('sale_type', $includedSaleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM((products_total + labor_total) - profit), 0) as products_cost')
            ->value('products_cost');

        // عندما تكون تكلفة أسطر البيع محفوظة نستخدمها مباشرة، خصوصاً للرولات
        // التي يحتوي cost_price فيها على تكلفة الرول الكامل وليس تكلفة المتر.
        $productsCostToday = $this->calculateSavedSaleItemsCost(
            $storeIds,
            today()->startOfDay(),
            today()->endOfDay(),
            $includedSaleTypes,
            (float) $productsCostToday
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

        /* المصروفات - نستخدم عمود amount كما هو في جدولك */
        $expensesToday = Expense::whereIn('store_id', $storeIds)
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
        $profitToday = Sale::whereIn('store_id', $storeIds)
            ->whereDate('created_at', today())
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as adjusted_profit')
            ->value('adjusted_profit') - $expensesToday;

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

        // ربح المبيعات الشهري بعد تكلفة المنتجات المباعة (هامش البيع التشغيلي قبل المصروفات الثابتة/الإضافية)
        $monthlyAdjustedSalesProfit = Sale::whereIn('store_id', $storeIds)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as adjusted_profit')
            ->value('adjusted_profit');

        // صافي الربح الشهري النهائي = ربح المبيعات المعدّل - المصروفات - الرواتب - المشتريات والاستهلاك
        $profitMonth = $monthlyAdjustedSalesProfit
            - $expensesMonth
            - $netMonthlySalaries
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

            $storeProductsCostToday = Sale::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->whereIn('sale_type', $includedSaleTypes)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->selectRaw('COALESCE(SUM((products_total + labor_total) - profit), 0) as products_cost')
                ->value('products_cost');

            $storeProductsCostToday = $this->calculateSavedSaleItemsCost(
                [$storeId],
                today()->startOfDay(),
                today()->endOfDay(),
                $includedSaleTypes,
                (float) $storeProductsCostToday
            );

            $storeExpensesToday = Expense::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->sum('amount');

            $storeProfitTodayRaw = Sale::where('store_id', $storeId)
                ->whereDate('created_at', today())
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as adjusted_profit')
                ->value('adjusted_profit');
            $storeProfitToday = (float) $storeProfitTodayRaw - (float) $storeExpensesToday;

            $storeSalesMonth = Sale::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->whereIn('sale_type', $includedSaleTypes)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('paid_amount');

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

            $storeMonthlyAdjustedProfit = Sale::where('store_id', $storeId)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->selectRaw('COALESCE(SUM(paid_amount - ((products_total + labor_total) - profit)), 0) as adjusted_profit')
                ->value('adjusted_profit');

            $storeProfitMonth = (float) $storeMonthlyAdjustedProfit
                - (float) $storeExpensesMonth
                - (float) $storeNetSalariesMonth
                - (float) $storeOwnerPurchasesMonth
                - (float) $storeAccountantConsumptionMonth;

            $metricStoreBreakdowns[] = [
                'store_name' => $store->name,
                'profit_today' => (float) $storeProfitToday,
                'sales_today' => (float) $storeSalesToday,
                'expenses_today' => (float) $storeExpensesToday,
                'products_cost_today' => (float) $storeProductsCostToday,
                'profit_month' => (float) $storeProfitMonth,
                'sales_month' => (float) $storeSalesMonth,
                'expenses_month' => (float) $storeExpensesMonth,
                'salaries_month' => (float) $storeNetSalariesMonth,
                'monthly_owner_purchases' => (float) $storeOwnerPurchasesMonth,
                'monthly_accountant_consumption' => (float) $storeAccountantConsumptionMonth,
                'monthly_purchases_consumption' => (float) $storeOwnerPurchasesMonth + (float) $storeAccountantConsumptionMonth,
            ];
        }

        // بعض نسخ واجهة لوحة المالك تعرض المتجر الأفضل أداءً بصورة مستقلة.
        // نجهز بياناته من نفس ملخص المتاجر حتى لا تعتمد الواجهة على متغير غير مرسل.
        $bestStorePerformance = collect($metricStoreBreakdowns)
            ->sortByDesc('profit_month')
            ->first();

        return view('dashboard.user.index', array_merge(compact(
            'stores', 'accountantsCount', 'employeesCount', 'daysLeft', 'salesToday', 'salesMonth', 'productsCostToday',
            'expensesToday', 'expensesMonth', 'profitToday', 'profitMonth',
            'monthlySalaries', 'monthlyWorkerWithdrawals', 'netMonthlySalaries',
            'monthlyOwnerPurchases', 'monthlyAccountantConsumption', 'monthlyPurchasesAndConsumption',
            'creditOpen', 'metricStoreBreakdowns',
            'selectedSummaryStore',
            'bestStorePerformance',
            'creditClosed', 'creditLate', 'user', 'activities'
        ), $chartData));
    }

    /**
     * يجمع تكلفة أسطر البيع المحفوظة دون تغيير معادلات اللوحة الأخرى.
     * إذا وُجدت عملية لا تحتوي total_cost لجميع أسطرها نضيف تكلفتها القديمة
     * من sales بدلاً من إسقاطها من الإجمالي.
     */
    private function calculateSavedSaleItemsCost($storeIds, $start, $end, array $saleTypes, float $legacyFallback): float
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('sale_items', 'total_cost')) {
            return $legacyFallback;
        }

        $storeIds = collect($storeIds)->map(fn ($id) => (int) $id)->filter()->values()->all();
        if (empty($storeIds)) {
            return 0.0;
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
            ->selectRaw('sales.id')
            ->selectRaw('COUNT(sale_items.id) as items_count')
            ->selectRaw('SUM(CASE WHEN sale_items.total_cost IS NOT NULL THEN 1 ELSE 0 END) as costed_items_count')
            ->selectRaw('COALESCE(SUM(sale_items.total_cost), 0) as saved_items_cost')
            ->selectRaw('COALESCE((sales.products_total + sales.labor_total) - sales.profit, 0) as legacy_cost');

        return (float) DB::query()
            ->fromSub($salesCosts, 'sales_costs')
            ->selectRaw(
                'COALESCE(SUM(
                    CASE
                        WHEN items_count > 0 AND items_count = costed_items_count
                        THEN saved_items_cost
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
            'daysLeft' => 0, 'salesToday' => 0, 'salesMonth' => 0, 'productsCostToday' => 0, 'expensesToday' => 0,
            'expensesMonth' => 0, 'profitToday' => 0, 'profitMonth' => 0, 'monthlyPurchasesAndConsumption' => 0, 'creditOpen' => 0,
            'metricStoreBreakdowns' => [],
            'selectedSummaryStore' => null,
            'bestStorePerformance' => null,
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [], 'chartCredit' => []
        ];
    }
}
