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

        $todayStart = today()->startOfDay();
        $todayEnd = today()->endOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // جميع مؤشرات لوحة المالك تستخدم نفس نطاق المبيعات المعتمد في التقارير:
        // الأنواع التجارية فقط، استبعاد الفواتير اليدوية، والمبلغ المستلم فعلياً.
        $salesToday = $this->receivedSalesTotal($storeIds, $todayStart, $todayEnd, $includedSaleTypes);
        $salesMonth = $this->receivedSalesTotal($storeIds, $monthStart, $monthEnd, $includedSaleTypes);
        $productsCostToday = $this->soldProductsCost($storeIds, $todayStart, $todayEnd, $includedSaleTypes);
        $productsCostMonth = $this->soldProductsCost($storeIds, $monthStart, $monthEnd, $includedSaleTypes);

        // مشتريات المالك لها بطاقة مستقلة، لذلك نستبعد قيود owner_purchase من المصروفات
        // حتى لا تُخصم مرتين من صافي الربح الشهري.
        $expensesToday = $this->expensesTotal($storeIds, $todayStart, $todayEnd);
        $expensesMonth = $this->expensesTotal($storeIds, $monthStart, $monthEnd);

        // إجمالي رواتب جميع موظفي متاجر المالك (حمولة ثابتة شهرية)
        $monthlySalaries = $user->employees()->sum('salary') ?? 0;
        $monthlyWorkerWithdrawals = (float) Withdrawal::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount');
        $netMonthlySalaries = max(0, (float) $monthlySalaries - $monthlyWorkerWithdrawals);

        // صافي اليوم = المحصل الفعلي - تكلفة المنتجات المباعة - المصروفات التشغيلية.
        $profitToday = (float) $salesToday - (float) $productsCostToday - (float) $expensesToday;

        $monthlyOwnerPurchases = Purchase::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('cost');

        // الاستهلاك الداخلي ليس مبيعات، لذلك لا يدخل في salesMonth أو هامش المبيعات.
        $monthlyAccountantConsumption = Sale::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('sale_type', 'internal_use')
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->sum('total');

        $monthlyPurchasesAndConsumption = (float) $monthlyOwnerPurchases + (float) $monthlyAccountantConsumption;
        $monthlySalesProfit = (float) $salesMonth - (float) $productsCostMonth;

        // صافي الربح الشهري النهائي دون تكرار خصم المشتريات أو الاستهلاك الداخلي.
        $profitMonth = $monthlySalesProfit
            - (float) $expensesMonth
            - (float) $netMonthlySalaries
            - (float) $monthlyPurchasesAndConsumption;

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
            $storeSalesToday = $this->receivedSalesTotal($singleStoreIds, $todayStart, $todayEnd, $includedSaleTypes);
            $storeProductsCostToday = $this->soldProductsCost($singleStoreIds, $todayStart, $todayEnd, $includedSaleTypes);
            $storeExpensesToday = $this->expensesTotal($singleStoreIds, $todayStart, $todayEnd);
            $storeProfitToday = (float) $storeSalesToday - (float) $storeProductsCostToday - (float) $storeExpensesToday;

            $storeSalesMonth = $this->receivedSalesTotal($singleStoreIds, $monthStart, $monthEnd, $includedSaleTypes);
            $storeProductsCostMonth = $this->soldProductsCost($singleStoreIds, $monthStart, $monthEnd, $includedSaleTypes);
            $storeExpensesMonth = $this->expensesTotal($singleStoreIds, $monthStart, $monthEnd);

            $storeSalariesMonth = (float) $store->employees()->sum('salary');
            $storeWorkerWithdrawalsMonth = (float) Withdrawal::where('store_id', $storeId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
            $storeNetSalariesMonth = max(0, $storeSalariesMonth - $storeWorkerWithdrawalsMonth);

            $storeOwnerPurchasesMonth = Purchase::where('store_id', $storeId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('cost');

            $storeAccountantConsumptionMonth = Sale::where('store_id', $storeId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('sale_type', 'internal_use')
                ->where(function ($query) {
                    $query->whereNull('description')
                        ->orWhere('description', '!=', 'manual_invoice_entry');
                })
                ->sum('total');

            $storeProfitMonth = (float) $storeSalesMonth
                - (float) $storeProductsCostMonth
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

    private function receivedSalesTotal($storeIds, $start, $end, array $saleTypes): float
    {
        return (float) Sale::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('sale_type', $saleTypes)
            ->where(function ($query) {
                $query->whereNull('description')
                    ->orWhere('description', '!=', 'manual_invoice_entry');
            })
            ->selectRaw('COALESCE(SUM(CASE
                WHEN (COALESCE(cash_amount, 0) + COALESCE(card_amount, 0)) > COALESCE(paid_amount, 0)
                THEN (COALESCE(cash_amount, 0) + COALESCE(card_amount, 0))
                ELSE COALESCE(paid_amount, 0)
            END), 0) as received_total')
            ->value('received_total');
    }

    private function soldProductsCost($storeIds, $start, $end, array $saleTypes): float
    {
        return (float) DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sales.store_id', $storeIds)
            ->whereBetween('sales.created_at', [$start, $end])
            ->whereIn('sales.sale_type', $saleTypes)
            ->where(function ($query) {
                $query->whereNull('sales.description')
                    ->orWhere('sales.description', '!=', 'manual_invoice_entry');
            })
            ->sum(DB::raw('COALESCE(products.cost_price, 0) * COALESCE(sale_items.custom_consumption, sale_items.quantity, 0)'));
    }

    private function expensesTotal($storeIds, $start, $end): float
    {
        return (float) Expense::whereIn('store_id', $storeIds)
            ->where(function ($query) {
                $query->whereNull('actor_type')
                    ->orWhere('actor_type', '!=', 'owner_purchase');
            })
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
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
            'monthlySalaries' => 0, 'monthlyWorkerWithdrawals' => 0, 'netMonthlySalaries' => 0,
            'monthlyOwnerPurchases' => 0, 'monthlyAccountantConsumption' => 0, 'monthlyPurchasesAndConsumption' => 0,
            'creditOpen' => 0,
            'metricStoreBreakdowns' => [],
            'creditClosed' => 0, 'creditLate' => 0, 'activities' => collect(),
            'chartLabels' => [], 'chartSales' => [], 'chartExpenses' => [], 'chartCredit' => []
        ];
    }
}
