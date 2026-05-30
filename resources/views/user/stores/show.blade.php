@extends('dashboard.app')
@section('title', 'متجر — ' . $store->name)
@section('content')

@php
    // =============================================
    // جميع المتغيرات والإحصائيات في كتلة واحدة
    // =============================================

    // 1. متغيرات التنبيهات والمشاكل
    $hasIssues = false;
    $issues = [];

    // 2. إحصائيات سريعة
    // ملاحظة ربط: نستخدم القيم القادمة من الكنترولر عند توفرها لتفادي ازدواج الحساب داخل الـ Blade.
    $accountantsCount = $accountantsCount ?? $store->accountants()->count();
    $employeesCount = $employeesCount ?? $store->employees()->count();
    $pendingEmployeesCount = $store->employees()->whereDoesntHave('accountant')->count();
    $productsCount = $productsCount ?? $store->products()->count();
    $lowStockProducts = $store->products()->where('quantity', '<', 3)->count();
    $invoicesCount = $invoicesCount ?? $store->sales()->count();
    $categoriesCount = $categoriesCount ?? $store->categories()->count();
    $consumptionCount = $consumptionCount ?? $store->sales()->where('sale_type', 'internal_use')->count();
    // توحيد مصدر الاستهلاك الداخلي مع صفحة الاستهلاك (تعتمد على sales.total)
    $monthlyAccountantConsumption = (float) \DB::table('sales')
        ->where('store_id', $store->id)
        ->where('sale_type', 'internal_use')
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->where(function ($query) {
            $query->whereNull('description')
                ->orWhere('description', '!=', 'manual_invoice_entry');
        })
        ->selectRaw('COALESCE(SUM(COALESCE(total, paid_amount, products_total, 0)), 0) as internal_use_total')
        ->value('internal_use_total');

    // نفس مصدر صفحة "تقرير الاستهلاك" لبطاقات المقارنة
    $monthlyOwnerPurchases = \App\Models\Purchase::where('store_id', $store->id)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->sum('cost');
    $monthlyOperationalPurchases = \App\Models\Purchase::where('store_id', $store->id)
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereNull('product_id')
        ->sum('cost');
    $monthlyPurchasesAndConsumption = (float) $monthlyAccountantConsumption + (float) $monthlyOwnerPurchases;

    // 3. المبيعات (المحصل الفعلي + عدد العمليات) لتفادي أي لبس في البطاقات
    $includedSaleTypes = ['cash', 'card', 'credit', 'mixed'];
    $todaySalesCount = $store->sales()
        ->whereDate('created_at', today())
        ->whereIn('sale_type', $includedSaleTypes)
        ->count();
    $monthSalesCount = $store->sales()
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereIn('sale_type', $includedSaleTypes)
        ->count();

    $todaySales = $todaySales ?? $store->sales()
        ->whereDate('created_at', today())
        ->whereIn('sale_type', $includedSaleTypes)
        ->sum('paid_amount');
    $monthSales = $monthSales ?? $store->sales()
        ->whereYear('created_at', now()->year)
        ->whereMonth('created_at', now()->month)
        ->whereIn('sale_type', $includedSaleTypes)
        ->sum('paid_amount');

    // 4. بيانات الرسم البياني (آخر 7 أيام)
    if (!isset($chartData, $chartLabels, $profitData)) {
        $chartData = [];
        $chartLabels = [];
        $profitData = [];
        for($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('d/m');
            $daySales = $store->sales()
                ->whereDate('created_at', $date)
                ->whereIn('sale_type', $includedSaleTypes)
                ->get();
            $chartData[] = $daySales->sum('paid_amount');
            $profitData[] = $daySales->sum(fn($sale) => (float) ($sale->paid_amount ?? 0) - (((float) ($sale->products_total ?? 0) + (float) ($sale->labor_total ?? 0)) - (float) ($sale->profit ?? 0)));
        }
    }

    // 5. أفضل المنتجات مبيعاً
    $topProducts = $topProducts ?? \DB::table('sale_items')
        ->join('products', 'sale_items.product_id', '=', 'products.id')
        ->where('products.store_id', $store->id)
        ->where('sale_items.created_at', '>=', now()->subDays(30))
        ->select('products.name', 'products.price', \DB::raw('SUM(sale_items.quantity) as total_sold'))
        ->groupBy('products.id', 'products.name', 'products.price')
        ->orderBy('total_sold', 'desc')
        ->limit(10)
        ->get();

    // 6. آخر العمليات
    $operations = $operations ?? \App\Models\Log::where('store_id', $store->id)
        ->with('user')
        ->latest()
        ->limit(10)
        ->get();

    // 7. تحديد المشاكل والتنبيهات
    if($employeesCount == 0) {
        $hasIssues = true;
        $issues[] = 'لا يوجد موظفين في المتجر';
    }
    if($productsCount == 0) {
        $hasIssues = true;
        $issues[] = 'لم يتم إضافة أي منتجات بعد';
    }
    if($store->status == 'suspended') {
        $hasIssues = true;
        $issues[] = 'المتجر غير مفعل حالياً' . ($store->suspension_reason ? ': ' . $store->suspension_reason : '');
    }
    if($lowStockProducts > 0) {
        $hasIssues = true;
        $issues[] = "هناك $lowStockProducts منتج منخفض المخزون";
    }

    $monthStart = now()->copy()->startOfMonth();
    $monthEnd = now()->copy()->endOfMonth();
    $daysInMonth = (int) now()->daysInMonth;

    // 8. بطاقات التنقل السريع
    $cards = [
        [
            'title' => 'المحاسبين',
            'icon' => 'fa-user-tie',
            'color' => 'blue',
            'count' => $accountantsCount,
            'desc' => $accountantsCount == 0 ? 'لا يوجد محاسبين' : 'إدارة محاسبي المتجر',
            'tooltip' => 'عرض وإدارة حسابات المحاسبين في هذا المتجر',
            'url' => route('user.stores.accountants.index', $store->id)
        ],
        [
            'title' => 'الموظفين',
            'icon' => 'fa-users',
            'color' => 'green',
            'count' => $employeesCount,
            'desc' => $pendingEmployeesCount > 0 ? $pendingEmployeesCount.' موظف جديد' : 'إدارة الموظفين',
            'tooltip' => 'متابعة الموظفين الجدد والقدامى وإدارة بياناتهم',
            'url' => route('user.stores.employees.index', $store->id)
        ],
        [
            'title' => 'المبيعات',
            'icon' => 'fa-chart-line',
            'color' => 'yellow',
            'count' => $todaySalesCount,
            'desc' => 'محصل اليوم '.number_format($todaySales, 2).' ر.س',
            'tooltip' => 'الانتقال لصفحة مبيعات اليوم والتعديلات اليومية',
            'url' => route('user.stores.daily', $store->id)
        ],
        [
            'title' => 'التقارير',
            'icon' => 'fa-file-alt',
            'color' => 'blue',
            'count' => '3',
            'desc' => 'تقارير المتجر',
            'tooltip' => 'فتح مركز التقارير (يومي / شهري / PDF)',
            'url' => route('user.stores.reports.index', $store->id)
        ],
        [
            'title' => 'الأقسام والمنتجات',
            'icon' => 'fa-layer-group',
            'color' => 'orange',
            'count' => $categoriesCount.' قسم',
            'desc' => $productsCount.' منتج',
            'tooltip' => 'إدارة الأقسام والمنتجات وربطها ببيانات المخزون',
            'url' => route('user.stores.catalog', $store->id)
        ],
        [
            'title' => 'التوريد والمخزون',
            'icon' => 'fa-truck-loading',
            'color' => 'teal',
            'count' => $lowStockProducts,
            'desc' => $lowStockProducts > 0 ? 'منتجات منخفضة' : 'مخزون كافٍ',
            'tooltip' => 'متابعة التوريد والمنتجات منخفضة المخزون',
            'url' => route('user.stores.supply.index', $store->id)
        ],
        [
            'title' => 'المشتريات والاستهلاك',
            'icon' => 'fa-bolt',
            'color' => 'purple',
            'count' => number_format($monthlyPurchasesAndConsumption, 2) . ' ر.س',
            'desc' => 'الإجمالي الشهري',
            'tooltip' => 'استهلاك المحاسب + مشتريات المالك للاستهلاك (نفس صفحة تقرير الاستهلاك)',
            'url' => route('user.stores.internal-use.report.view', $store->id)
        ]
    ];

    // 9. بطاقات "مبيعات اليوم" (بدون السحب)
    $todayExpenses = (float) \App\Models\Expense::where('store_id', $store->id)
        ->whereDate('created_at', today())
        ->sum('amount');

    $todayCash = (float) $store->sales()
        ->whereDate('created_at', today())
        ->whereIn('sale_type', $includedSaleTypes)
        ->sum('cash_amount');

    $todayCard = (float) $store->sales()
        ->whereDate('created_at', today())
        ->whereIn('sale_type', $includedSaleTypes)
        ->sum('card_amount');

    $todayCredit = (float) $store->sales()
        ->whereDate('created_at', today())
        ->whereIn('sale_type', $includedSaleTypes)
        ->sum('remaining_amount');

    $todayFinanceStats = [
        ['title'=>'محصل اليوم','value'=>number_format($todaySales,2),'icon'=>'fa-calendar-day','color'=>'blue','desc'=>now()->translatedFormat('Y/m/d').' • '.$todaySalesCount.' عملية (المحصل)','tooltip'=>'إجمالي المبالغ المحصلة فعليًا اليوم','unit'=>'ر.س'],
        ['title'=>'مصروف اليوم','value'=>number_format($todayExpenses,2),'icon'=>'fa-file-invoice-dollar','color'=>'red','desc'=>'مصروفات اليوم','tooltip'=>'إجمالي المصروفات المسجلة خلال اليوم','unit'=>'ر.س'],
        ['title'=>'كاش اليوم','value'=>number_format($todayCash,2),'icon'=>'fa-money-bill-wave','color'=>'green','desc'=>'نقدي','tooltip'=>'إجمالي المدفوعات النقدية اليوم','unit'=>'ر.س'],
        ['title'=>'شبكة اليوم','value'=>number_format($todayCard,2),'icon'=>'fa-credit-card','color'=>'cyan','desc'=>'مدفوعات شبكة','tooltip'=>'إجمالي المدفوعات بالشبكة اليوم','unit'=>'ر.س'],
        ['title'=>'آجل اليوم','value'=>number_format($todayCredit,2),'icon'=>'fa-hourglass-half','color'=>'yellow','desc'=>'آجل جديد','tooltip'=>'قيمة الآجل الجديد الناتج عن مبيعات اليوم','unit'=>'ر.س'],
    ];

    // 10. بطاقات ملخص الشهر
    $monthExpenses = (float) \App\Models\Expense::where('store_id', $store->id)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
    $monthlySalaries = (float) $store->employees()->sum('salary');
    $monthlyWithdrawals = (float) \App\Models\Withdrawal::where('store_id', $store->id)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
    $netMonthlySalaries = max(0, (float) $monthlySalaries - (float) $monthlyWithdrawals);

    if (\Illuminate\Support\Facades\Schema::hasColumn('sale_items', 'total_cost')) {
        $monthlySoldProductsCost = (float) \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.store_id', $store->id)
            ->whereBetween('sales.created_at', [$monthStart, $monthEnd])
            ->whereIn('sales.sale_type', $includedSaleTypes)
            ->sum(\DB::raw('COALESCE(sale_items.total_cost, 0)'));
    } else {
        $monthlySoldProductsCost = (float) $store->sales()
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereIn('sale_type', $includedSaleTypes)
            ->sum('products_total');
    }

    $monthNetProfit = (float) $monthSales - ((float) $monthExpenses + (float) $monthlyAccountantConsumption + (float) $monthlySoldProductsCost + (float) $monthlyOperationalPurchases + (float) $netMonthlySalaries);

    $monthFinanceStats = [
        ['title'=>'المحصل الشهري','value'=>number_format($monthSales,2),'icon'=>'fa-wallet','color'=>'green','desc'=>now()->translatedFormat('Y/m').' • '.$monthSalesCount.' عملية (المحصل)','tooltip'=>'إجمالي المحصل الفعلي خلال الشهر','unit'=>'ر.س'],
        ['title'=>'إجمالي المصروفات','value'=>number_format($monthExpenses,2),'icon'=>'fa-receipt','color'=>'red','desc'=>'مصروفات المتجر','tooltip'=>'إجمالي المصروفات المسجلة خلال الشهر','unit'=>'ر.س'],
        ['title'=>'استهلاك داخلي (المحاسب)','value'=>number_format($monthlyAccountantConsumption,2),'icon'=>'fa-box-open','color'=>'yellow','desc'=>$monthlyAccountantConsumption>0?'استهلاك داخلي بسعر التكلفة':'لا يوجد استهلاك محاسب هذا الشهر','tooltip'=>'الاستهلاك التشغيلي يُعرض منفصلًا عن بطاقة مشتريات المالك للاستهلاك','unit'=>'ر.س'],
        ['title'=>'تكلفة المنتجات المباعة','value'=>number_format($monthlySoldProductsCost,2),'icon'=>'fa-cart-shopping','color'=>'cyan','desc'=>'تُخصم من الربح','tooltip'=>'إجمالي تكلفة المنتجات المباعة بسعر التكلفة','unit'=>'ر.س'],
        ['title'=>'مشتريات المالك للاستهلاك','value'=>number_format($monthlyOwnerPurchases,2),'icon'=>'fa-truck-ramp-box','color'=>'blue','desc'=>'مطابق لصفحة الاستهلاك','tooltip'=>'نفس قيمة مشتريات المالك للاستهلاك في تقرير الاستهلاك','unit'=>'ر.س'],
        ['title'=>'صافي الرواتب','value'=>number_format($netMonthlySalaries,2),'icon'=>'fa-user-tie','color'=>'purple','desc'=>'الرواتب بعد خصم سحب العمال','tooltip'=>'صافي الرواتب = إجمالي الرواتب - سحب العمال خلال الشهر','unit'=>'ر.س'],
        ['title'=>$monthNetProfit<0?'إجمالي الخسارة':'إجمالي الربح','value'=>number_format(abs($monthNetProfit),2),'icon'=>'fa-chart-line','color'=>$monthNetProfit>=0?'emerald':'rose','desc'=>$monthNetProfit<0?'خسارة بعد التكاليف':'ربح بعد التكاليف','tooltip'=>$monthNetProfit<0?'إجمالي الخسارة = (المصروفات + الاستهلاك + تكلفة المبيعات + المشتريات + صافي الرواتب) - المحصل':'إجمالي الربح = المحصل - (المصروفات + الاستهلاك + تكلفة المبيعات + المشتريات + صافي الرواتب)','unit'=>'ر.س'],
    ];

    // 11. بطاقة مستحقات الموظفين
    $employeeMonthlyWithdrawals = (float) \App\Models\Withdrawal::where('store_id', $store->id)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
    $employeeMonthlyDebts = (float) \App\Models\Debt::where('store_id', $store->id)->whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
    $employees = $store->employees()->get(['id', 'salary']);
    $absenceEmployeeColumn = null;
    if (\Illuminate\Support\Facades\Schema::hasColumn('employee_absences', 'employee_id')) $absenceEmployeeColumn = 'employee_id';
    elseif (\Illuminate\Support\Facades\Schema::hasColumn('employee_absences', 'person_id')) $absenceEmployeeColumn = 'person_id';

    if ($absenceEmployeeColumn) {
        $absenceRows = \App\Models\Absence::where('store_id', $store->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->selectRaw("{$absenceEmployeeColumn} as employee_ref, COUNT(*) as absence_days")
            ->groupBy($absenceEmployeeColumn)->get();
    } else {
        $absenceRows = collect();
    }

    $absenceDaysByEmployee = $absenceRows->pluck('absence_days', 'employee_ref')->map(fn($days) => (int) $days);
    $employeeMonthlyAbsenceCost = 0.0;
    foreach ($employees as $employee) {
        $days = (int) ($absenceDaysByEmployee[$employee->id] ?? 0);
        if ($days > 0) {
            $dailySalary = ((float) $employee->salary) / max($daysInMonth, 1);
            $employeeMonthlyAbsenceCost += $dailySalary * $days;
        }
    }

    $employeeRemainingSalary = (float) $monthlySalaries - ((float) $employeeMonthlyWithdrawals + (float) $employeeMonthlyAbsenceCost + (float) $employeeMonthlyDebts);
    $employeePayrollStats = [
        ['title'=>'راتب الموظف (الإجمالي)','value'=>number_format($monthlySalaries,2),'icon'=>'fa-sack-dollar','color'=>'blue','desc'=>'إجمالي رواتب الشهر قبل السحب','tooltip'=>'الراتب الأساسي الإجمالي لكل الموظفين قبل خصم السحبيات','unit'=>'ر.س'],
        ['title'=>'سحبيات الموظف','value'=>number_format($employeeMonthlyWithdrawals,2),'icon'=>'fa-hand-holding-dollar','color'=>'yellow','desc'=>'إجمالي السحبيات الشهرية','tooltip'=>'مجموع سحبيات الموظفين خلال الشهر','unit'=>'ر.س'],
        ['title'=>'صافي الرواتب','value'=>number_format($netMonthlySalaries,2),'icon'=>'fa-wallet','color'=>'indigo','desc'=>'بعد خصم السحبيات','tooltip'=>'صافي الرواتب المعتمد في الربحية الشهرية','unit'=>'ر.س'],
        ['title'=>'قيمة غياب الموظف','value'=>number_format($employeeMonthlyAbsenceCost,2),'icon'=>'fa-user-clock','color'=>'red','desc'=>'محتسبة: الراتب ÷ أيام الشهر × أيام الغياب','tooltip'=>'خصم الغياب محسوب بناءً على الأجر اليومي لكل موظف','unit'=>'ر.س'],
        ['title'=>'المديونية','value'=>number_format($employeeMonthlyDebts,2),'icon'=>'fa-file-invoice','color'=>'orange','desc'=>'إجمالي المديونيات الشهرية','tooltip'=>'إجمالي المديونيات المسجلة على الموظفين','unit'=>'ر.س'],
        ['title'=>'المتبقي من الراتب','value'=>number_format($employeeRemainingSalary,2),'icon'=>'fa-coins','color'=>$employeeRemainingSalary>=0?'emerald':'rose','desc'=>'الراتب - (السحبيات + الغياب + المديونية)','tooltip'=>'المتبقي المستحق بعد الخصومات والسحبيات والمديونيات','unit'=>'ر.س'],
    ];

@endphp

<style>
/* ========== تحسينات التجاوب الشاملة ========== */
@media (max-width: 768px) {
    .max-w-7xl.mx-auto.py-8 {
        padding: 1rem !important;
        padding-top: 1.5rem !important;
    }

    .flex-col.md\\:flex-row {
        flex-direction: column !important;
        gap: 1.5rem !important;
        align-items: flex-start !important;
    }

    .flex-col.md\\:flex-row .flex {
        width: 100%;
        justify-content: space-between;
        gap: 0.75rem;
    }

    h1.text-2xl {
        font-size: 1.5rem !important;
        line-height: 1.3;
    }

    /* تحسين البطاقات الرئيسية */
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-5 {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem !important;
    }

    /* تحسين البطاقات الإحصائية */
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4 {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 1rem !important;
    }

    /* تحسين البطاقات نفسها */
    .bg-gradient-to-br.from-gray-900.to-gray-800 {
        padding: 1.25rem !important;
        border-radius: 1rem !important;
    }

    /* تحسين معلومات المتجر - تصغيرها */
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-4 {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.75rem !important;
        padding-top: 0.75rem !important;
        margin-top: 0.75rem !important;
    }

    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-4 div {
        padding: 0.5rem 0 !important;
    }

    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-4 p.text-gray-500 {
        font-size: 0.75rem !important;
        margin-bottom: 0.15rem;
    }

    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-4 p.text-white {
        font-size: 0.9rem !important;
    }

    /* تصغير معلومات الاتصال */
    .lg\\:w-96 {
        width: 100% !important;
    }

    .space-y-4 {
        gap: 0.5rem !important;
    }

    .p-3.bg-gray-800\\/20 {
        padding: 0.5rem !important;
    }

    .w-10.h-10 {
        width: 2rem !important;
        height: 2rem !important;
    }

    .text-xs {
        font-size: 0.7rem !important;
    }

    .text-white.font-medium {
        font-size: 0.85rem !important;
    }

    /* تحسين أزرار التنبيهات */
    .bg-gray-800.hover\\:bg-gray-700,
    .bg-blue-900\\/30.hover\\:bg-blue-800 {
        padding: 0.75rem 1rem !important;
        font-size: 0.95rem !important;
        min-height: 44px;
    }
}

@media (max-width: 480px) {
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3.xl\\:grid-cols-5,
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4,
    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-3 {
        grid-template-columns: 1fr !important;
    }

    .grid.grid-cols-1.sm\\:grid-cols-2.lg\\:grid-cols-4.gap-4 {
        grid-template-columns: 1fr 1fr !important;
    }

    h1.text-3xl {
        font-size: 1.4rem !important;
    }
}

/* إصلاحات للأجهزة التي تعمل باللمس */
@media (hover: none) and (pointer: coarse) {
    .group:hover .group-hover\\:text-\\w+ {
        color: inherit !important;
    }

    .hover\\:bg-gray-800:hover {
        background-color: transparent !important;
    }

    a, button {
        min-height: 44px !important;
        min-width: 44px !important;
    }
}

/* منع التكبير في iOS */
input, select, textarea, button {
    font-size: 16px !important;
}
</style>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- ===== شريط العنوان والأزرار (مصغر) ===== --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            @if($store->logo)
                <img src="{{ Storage::url($store->logo) }}"
                     alt="{{ $store->name }}"
                     class="w-10 h-10 rounded-lg object-cover border border-gray-700">
            @else
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-store text-white text-sm"></i>
                </div>
            @endif
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-white">
                    {{ $store->name }}
                </h1>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold
                    {{ $store->status == 'active' ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                    {{ $store->status == 'active' ? 'نشط' : 'معطل' }}
                </span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('user.stores.edit', $store->id) }}"
               class="flex items-center gap-1 bg-blue-900/30 hover:bg-blue-800 text-blue-400 hover:text-white border border-blue-800 px-3 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-edit text-xs"></i>
                <span>تعديل</span>
            </a>

            <a href="{{ route('user.stores.index') }}"
               class="flex items-center gap-1 bg-gray-800 hover:bg-gray-700 text-white px-3 py-2 rounded-lg text-sm transition">
                <i class="fa-solid fa-arrow-right text-xs"></i>
                <span>رجوع</span>
            </a>
        </div>
    </div>

    {{-- ===== معلومات المتجر (مصغرة جداً) ===== --}}
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
            <div class="rounded-lg border border-blue-800/40 bg-blue-900/15 py-2">
                <p class="text-blue-300 text-[11px] font-semibold mb-1">رقم المتجر</p>
                <p class="text-white text-sm font-mono flex items-center justify-center gap-1">
                    <i class="fa-solid fa-hashtag text-blue-400 text-[10px]"></i>
                    {{ str_pad($store->id, 4, '0', STR_PAD_LEFT) }}
                </p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">تاريخ الإنشاء</p>
                <p class="text-white text-sm">{{ $store->created_at->translatedFormat('Y/m/d') }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">الورديات</p>
                <p class="text-white text-sm">{{ $store->number_of_shifts }} وردية</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">المنتجات</p>
                <p class="text-white text-sm">{{ $productsCount }}</p>
            </div>
        </div>

        {{-- معلومات الاتصال (مصغرة) - تظهر فقط إذا كان هناك بيانات --}}
        @if($store->phone || $store->address)
        <div class="flex flex-wrap gap-3 mt-3 pt-3 border-t border-gray-700/50 text-xs">
            @if($store->phone)
            <div class="flex items-center gap-1">
                <i class="fa-solid fa-phone text-gray-500"></i>
                <span class="text-gray-400">{{ $store->phone }}</span>
            </div>
            @endif
            @if($store->address)
            <div class="flex items-center gap-1">
                <i class="fa-solid fa-location-dot text-gray-500"></i>
                <span class="text-gray-400 truncate max-w-[200px]">{{ $store->address }}</span>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ===== تنبيهات النظام (إذا وجدت) ===== --}}
    @if($hasIssues)
    <div class="mb-6">
        <div class="bg-gradient-to-r from-yellow-900/20 to-yellow-800/10 border border-yellow-700/30 rounded-xl p-3">
            <div class="flex items-start gap-2">
                <div class="w-8 h-8 bg-yellow-500/20 rounded-lg flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-exclamation-triangle text-yellow-400 text-sm"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-bold text-sm mb-2">تنبيهات</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($issues as $issue)
                        <span class="px-2 py-1 bg-yellow-500/5 rounded text-yellow-300 text-xs">{{ $issue }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ===== الأقسام القابلة للفتح (Accordion) ===== --}}
    <div class="space-y-3 mb-8">
        <details class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl group">
            <summary class="list-none cursor-pointer p-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-compass text-blue-400"></i>
                    <h3 class="text-white font-bold text-sm">التنقل السريع</h3>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="px-4 pb-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach($cards as $card)
                    <a href="{{ $card['url'] }}"
                       title="{{ $card['tooltip'] ?? $card['desc'] }}"
                       class="group bg-gray-900/40 border border-gray-700 rounded-xl p-3 transition-all hover:border-{{ $card['color'] }}-500 hover:scale-[1.02]">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 bg-{{ $card['color'] }}-500/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $card['icon'] }} text-{{ $card['color'] }}-400 text-sm"></i>
                            </div>
                            <span class="text-{{ $card['color'] }}-400 font-bold text-lg">{{ $card['count'] }}</span>
                        </div>
                        <h4 class="text-white font-bold text-sm mb-1">{{ $card['title'] }}</h4>
                        <p class="text-gray-500 text-xs">{{ $card['desc'] }}</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </details>

        <details class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl group">
            <summary class="list-none cursor-pointer p-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-cyan-400"></i>
                    <h3 class="text-white font-bold text-sm">مبيعات اليوم</h3>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="px-4 pb-4">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($todayFinanceStats as $stat)
                    <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-{{ $stat['color'] }}-500/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $stat['icon'] }} text-{{ $stat['color'] }}-400 text-sm"></i>
                            </div>
                        </div>
                        <p class="text-gray-400 text-xs mb-1" title="{{ $stat['tooltip'] ?? $stat['desc'] }}">{{ $stat['title'] }}</p>
                        <p class="text-{{ $stat['color'] }}-400 font-bold text-lg">{{ $stat['value'] }}
                            @if(!empty($stat['unit']))
                            <span class="text-gray-500 text-xs mr-1">{{ $stat['unit'] }}</span>
                            @endif
                        </p>
                        <p class="text-gray-500 text-xs">{{ $stat['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </details>

        <details class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl group">
            <summary class="list-none cursor-pointer p-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-calendar text-emerald-400"></i>
                    <h3 class="text-white font-bold text-sm">ملخص الشهر</h3>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="px-4 pb-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach($monthFinanceStats as $stat)
                    <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-{{ $stat['color'] }}-500/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $stat['icon'] }} text-{{ $stat['color'] }}-400 text-sm"></i>
                            </div>
                        </div>
                        <p class="text-gray-400 text-xs mb-1" title="{{ $stat['tooltip'] ?? $stat['desc'] }}">{{ $stat['title'] }}</p>
                        <p class="text-{{ $stat['color'] }}-400 font-bold text-lg">{{ $stat['value'] }}
                            @if(!empty($stat['unit']))
                            <span class="text-gray-500 text-xs mr-1">{{ $stat['unit'] }}</span>
                            @endif
                        </p>
                        <p class="text-gray-500 text-xs">{{ $stat['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    طريقة الحساب: صافي النتيجة = المحصل الشهري - (المصروفات + الاستهلاك الداخلي + تكلفة المنتجات المباعة + المشتريات التشغيلية + صافي الرواتب بعد خصم سحب العمال)،
                    وإذا كانت أقل من الصفر فهي تمثل إجمالي الخسارة.
                </p>
            </div>
        </details>

        <details class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl group">
            <summary class="list-none cursor-pointer p-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-users text-yellow-400"></i>
                    <h3 class="text-white font-bold text-sm">مستحقات الموظفين</h3>
                </div>
                <i class="fa-solid fa-chevron-down text-gray-400 transition-transform group-open:rotate-180"></i>
            </summary>
            <div class="px-4 pb-4">
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                    @foreach($employeePayrollStats as $stat)
                    <div class="bg-gray-900/40 border border-gray-700 rounded-xl p-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 bg-{{ $stat['color'] }}-500/10 rounded-lg flex items-center justify-center">
                                <i class="fa-solid {{ $stat['icon'] }} text-{{ $stat['color'] }}-400 text-sm"></i>
                            </div>
                        </div>
                        <p class="text-gray-400 text-xs mb-1" title="{{ $stat['tooltip'] ?? $stat['desc'] }}">{{ $stat['title'] }}</p>
                        <p class="text-{{ $stat['color'] }}-400 font-bold text-lg">{{ $stat['value'] }}
                            @if(!empty($stat['unit']))
                            <span class="text-gray-500 text-xs mr-1">{{ $stat['unit'] }}</span>
                            @endif
                        </p>
                        <p class="text-gray-500 text-xs">{{ $stat['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </details>
    </div>

    {{-- ===== الرسوم البيانية والإحصائيات ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-8">
        {{-- مبيعات آخر 7 أيام --}}
        <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-white font-bold text-base">مبيعات آخر 7 أيام</h3>
                    <p class="text-gray-400 text-xs">إجمالي المبيعات اليومية</p>
                </div>
                @if(array_sum($chartData) > 0)
                <div class="bg-gray-800/30 px-2 py-1 rounded text-xs">
                    <span class="text-gray-400">الإجمالي: </span>
                    <span class="text-white font-bold">{{ number_format(array_sum($chartData), 2) }} ر.س</span>
                </div>
                @endif
            </div>

            <div class="h-56">
                <div id="salesChartContainer" class="h-full">
                    @if(array_sum($chartData) > 0)
                    <div class="h-full flex items-center justify-center">
                        <div class="w-12 h-12 border-4 border-gray-700 border-t-blue-500 rounded-full animate-spin"></div>
                    </div>
                    @else
                    <div class="h-full flex flex-col items-center justify-center">
                        <div class="w-12 h-12 bg-gray-800/50 rounded-xl flex items-center justify-center mb-2">
                            <i class="fa-solid fa-chart-line text-gray-500 text-lg"></i>
                        </div>
                        <p class="text-gray-400 text-sm">لا توجد بيانات مبيعات</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- أفضل المنتجات مبيعاً --}}
        <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl p-4">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-white font-bold text-base">أفضل المنتجات (بالكمية)</h3>
                    <p class="text-gray-400 text-xs">في آخر 30 يوم</p>
                </div>
                @if($topProducts->count() > 0)
                <div class="bg-gray-800/30 px-2 py-1 rounded text-xs">
                    <span class="text-gray-400">المجموع: </span>
                    <span class="text-white font-bold">{{ $topProducts->sum('total_sold') }} وحدة</span>
                </div>
                @endif
            </div>

            <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                @if($topProducts->count() > 0)
                    @foreach($topProducts as $index => $product)
                    <div class="flex items-center justify-between p-2 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 flex items-center justify-center bg-blue-500/10 text-blue-400 rounded text-xs font-bold">{{ $index + 1 }}</span>
                            <span class="text-white text-xs truncate max-w-[120px]">{{ $product->name }}</span>
                        </div>
                        <span class="text-green-400 font-bold text-sm">{{ number_format((float) ($product->total_sold ?? 0), 0) }} وحدة</span>
                    </div>
                    @endforeach
                @else
                <div class="h-40 flex flex-col items-center justify-center">
                    <div class="w-12 h-12 bg-gray-800/50 rounded-xl flex items-center justify-center mb-2">
                        <i class="fa-solid fa-box text-gray-500 text-lg"></i>
                    </div>
                    <p class="text-gray-400 text-sm">لا توجد مبيعات</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== آخر العمليات (مصغرة) ===== --}}
    <div class="bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-700/50 bg-gray-900/30">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1 h-6 bg-blue-500 rounded-full"></div>
                    <h3 class="text-white font-bold text-sm">آخر العمليات</h3>
                </div>
                @if($operations->count() > 0)
                <span class="text-gray-500 text-xs">{{ $operations->count() }} عملية</span>
                @endif
            </div>
        </div>

        <div class="divide-y divide-gray-700/50 max-h-80 overflow-y-auto">
            @forelse($operations as $op)
            @php
                $actorName = 'نظام';
                if($op->actor_type && class_exists($op->actor_type) && $op->actor_id) {
                    try {
                        $actor = $op->actor_type::find($op->actor_id);
                        $actorName = $actor ? $actor->name : 'غير معروف';
                    } catch (\Exception $e) {
                        $actorName = 'غير معروف';
                    }
                } elseif($op->user) {
                    $actorName = $op->user->name;
                }

                $actionColors = [
                    'create' => 'text-green-400',
                    'update' => 'text-blue-400',
                    'delete' => 'text-red-400',
                    'sale' => 'text-emerald-400',
                ];
                $color = $actionColors[$op->action] ?? 'text-gray-400';
            @endphp
            <div class="px-4 py-2 hover:bg-gray-800/30 transition text-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="{{ $color }} text-xs">{{ $actorName }}</span>
                        <span class="text-gray-500 text-xs">{{ $op->created_at->diffForHumans() }}</span>
                    </div>
                    <span class="text-gray-400 text-xs text-right">{{ $op->description ?? '' }}</span>
                </div>
            </div>
            @empty
            <div class="p-6 text-center">
                <p class="text-gray-500 text-sm">لا توجد عمليات حديثة</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- JavaScript للرسوم البيانية --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);
    const chartLabels = @json($chartLabels);
    const profitData = @json($profitData);

    if (!chartData || chartData.length === 0 || chartData.every(v => v === 0)) return;

    function loadChartJS() {
        return new Promise((resolve, reject) => {
            if (typeof Chart !== 'undefined') { resolve(); return; }
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    async function createChart() {
        const container = document.getElementById('salesChartContainer');
        if (!container) return;

        try {
            await loadChartJS();
            const canvas = document.createElement('canvas');
            container.innerHTML = '';
            container.appendChild(canvas);

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'المبيعات',
                        data: chartData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { callback: v => v + ' ر.س' } } }
                }
            });
        } catch (error) {
            console.error('Chart error:', error);
        }
    }

    setTimeout(createChart, 500);
});
</script>

@endsection
