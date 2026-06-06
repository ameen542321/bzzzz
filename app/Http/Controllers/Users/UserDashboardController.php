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
use Illuminate\Support\Carbon;

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
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        // نبني ملخص كل متجر من نفس فترات الشفتات ونفس العمليات التي تعتمدها
        // صفحة المبيعات اليومية، ثم نجمعها لبطاقات لوحة المالك.
        $dailyStoreSummaries = $stores->mapWithKeys(function ($store) use ($todayStart) {
            return [$store->id => $this->dailyStoreFinancialSummary($store->id, $todayStart)];
        });
        $salesToday = (float) $dailyStoreSummaries->sum('collected_total');
        $productsCostToday = (float) $dailyStoreSummaries->sum('total_cost');
        $profitToday = (float) $dailyStoreSummaries->sum('recognized_profit');
        $expensesToday = (float) $dailyStoreSummaries->sum('expenses');

        // الملخص الشهري يبقى معتمداً على إجمالي المبلغ المستلم الفعلي في التقارير.
        $salesMonth = $this->receivedSalesTotal($storeIds, $monthStart, $monthEnd, $includedSaleTypes);
        $productsCostMonth = $this->soldProductsCost($storeIds, $monthStart, $monthEnd, $includedSaleTypes);

        // مشتريات المالك لها بطاقة مستقلة، لذلك نستبعد قيود owner_purchase من المصروفات
        // حتى لا تُخصم مرتين من صافي الربح الشهري.
        $expensesMonth = $this->expensesTotal($storeIds, $monthStart, $monthEnd);

        // إجمالي رواتب جميع موظفي متاجر المالك (حمولة ثابتة شهرية)
        $monthlySalaries = $user->employees()->sum('salary') ?? 0;
        $monthlyWorkerWithdrawals = (float) Withdrawal::whereIn('store_id', $storeIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('amount');
        $netMonthlySalaries = max(0, (float) $monthlySalaries - $monthlyWorkerWithdrawals);

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
            $storeDailySummary = $dailyStoreSummaries->get($storeId, [
                'collected_total' => 0,
                'total_cost' => 0,
                'recognized_profit' => 0,
                'expenses' => 0,
            ]);
            $storeSalesToday = $storeDailySummary['collected_total'];
            $storeProductsCostToday = $storeDailySummary['total_cost'];
            $storeProfitToday = $storeDailySummary['recognized_profit'];
            $storeExpensesToday = $storeDailySummary['expenses'];

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

        $visibleSaleIds = (clone $salesQuery)->pluck('sales.id')->map(fn ($id) => (int) $id)->all();
        $summary = $salesQuery
            ->selectRaw('COALESCE(SUM(COALESCE(sales.paid_amount, 0)), 0) as collected_total')
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
            // صفحة المبيعات تضيف تحصيلات الآجل المستقلة إلى إجمالي المستلم فقط.
            'collected_total' => (float) ($summary->collected_total ?? 0)
                + $this->dailyCreditCollectionsTotal($storeId, $windows, $visibleSaleIds),
            'total_cost' => (float) ($summary->total_cost ?? 0),
            'recognized_profit' => (float) ($summary->recognized_profit ?? 0),
            'expenses' => (float) $expensesQuery->sum('amount'),
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

    private function dailyCreditCollectionsTotal(int $storeId, $windows, array $visibleSaleIds): float
    {
        $endTime = $windows->max('end');
        $collections = DB::table('employee_credit_sales')
            ->where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->where('created_at', '<=', $endTime)
            ->whereColumn('remaining_amount', '<', 'amount')
            ->get(['amount', 'remaining_amount', 'partial_payments', 'updated_at', 'description']);

        return (float) $collections->sum(function ($collection) use ($windows, $visibleSaleIds) {
            if (preg_match('/#(\d+)/', (string) ($collection->description ?? ''), $matches) === 1
                && in_array((int) $matches[1], $visibleSaleIds, true)) {
                return 0;
            }

            $payments = is_string($collection->partial_payments)
                ? json_decode($collection->partial_payments, true)
                : $collection->partial_payments;

            if (!is_array($payments) || empty($payments)) {
                $payments = [[
                    'amount' => (float) $collection->amount - (float) $collection->remaining_amount,
                    'date' => $collection->updated_at,
                ]];
            }

            return collect($payments)->sum(function ($payment) use ($windows) {
                if (empty($payment['date']) || (float) ($payment['amount'] ?? 0) <= 0) {
                    return 0;
                }

                $paymentDate = Carbon::parse($payment['date']);
                $insideDisplayedPeriod = $windows->contains(
                    fn ($window) => $paymentDate->betweenIncluded($window['start'], $window['end'])
                );

                return $insideDisplayedPeriod ? (float) $payment['amount'] : 0;
            });
        });
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
