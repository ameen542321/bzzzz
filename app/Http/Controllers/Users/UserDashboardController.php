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

        return view('dashboard.user.index', array_merge(compact(
            'stores', 'accountantsCount', 'employeesCount', 'employeesWithoutSalary', 'employeesWithoutSalaryCount',
            'daysLeft', 'salesToday', 'salesMonth', 'productsCostToday',
            'expensesToday', 'expensesMonth', 'profitToday', 'profitMonth',
            'monthlySalaries', 'monthlyWorkerWithdrawals', 'netMonthlySalaries',
            'monthlyOwnerPurchases', 'monthlyAccountantConsumption', 'monthlyPurchasesAndConsumption',
            'creditOpen', 'metricStoreBreakdowns',
            'creditClosed', 'creditLate', 'user', 'activities'
        ), $chartData));
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
            'employeesWithoutSalary' => collect(), 'employeesWithoutSalaryCount' => 0,
            'daysLeft' => 0, 'salesToday' => 0, 'salesMonth' => 0, 'productsCostToday' => 0, 'expensesToday' => 0,
            'expensesMonth' => 0, 'profitToday' => 0, 'profitMonth' => 0, 'monthlyPurchasesAndConsumption' => 0, 'creditOpen' => 0,
            'metricStoreBreakdowns' => [],
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [], 'chartCredit' => []
        ];
    }
}
