@extends('dashboard.app')

@section('title', 'لوحة التحكم')

@section('content')
<div class="p-6 space-y-10">

    {{-- ========================================================= --}}
    {{--  القسم الأول: الهيدر الاحترافي --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-white">مرحباً، {{ $user->name }}</h1>
        <p class="text-gray-400 mt-1">نظرة عامة ذكية على أداء متاجرك.</p>
    </div>

    <div class="flex flex-col items-start md:items-end gap-2">
        {{-- تاريخ اليوم --}}
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>{{ now()->format('Y-m-d') }}</span>
        </div>

        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-900/40 text-indigo-300 text-xs">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            خطة الاشتراك: {{ $user->plan->name ?? 'بدون خطة' }}
        </span>

        @if(isset($daysLeft))
            @php $days = (int) $daysLeft; @endphp
            <span class="px-3 py-1 rounded-lg text-xs font-medium
                @if($days > 3) bg-emerald-900/40 text-emerald-300
                @elseif($days >= 0) bg-yellow-900/40 text-yellow-300
                @else bg-red-900/40 text-red-300 @endif">

                @if($days > 0)
                    متبقي {{ $days }} يوم
                @elseif($days == 0)
                    ينتهي اليوم
                @else
                    منتهي منذ {{ abs($days) }} يوم
                @endif
            </span>
        @endif
    </div>
</div>

    {{-- ========================================================= --}}
    {{--  القسم الثاني: التنبيهات الذكية --}}
    {{-- ========================================================= --}}
    <div class="space-y-3">

        @if($salesToday == 0)
            <div class="alert-box bg-yellow-900/40 border-yellow-700 text-yellow-200">
                ⚠️ لا توجد مبيعات اليوم حتى الآن
            </div>
        @endif

        @if($expensesMonth > $salesMonth)
            <div class="alert-box bg-red-900/40 border-red-700 text-red-200">
                🔥 مصروفات هذا الشهر أعلى من المبيعات بنسبة
                {{ number_format(($expensesMonth / max($salesMonth,1)) * 100, 1) }}%
            </div>
        @endif

        @if($creditLate > 0)
            <div class="alert-box bg-orange-900/40 border-orange-700 text-orange-200">
                ⚠️ لديك {{ $creditLate }} مديونيات متأخرة لأكثر من 30 يوم
            </div>
        @endif

        @if(($longOpenShifts ?? collect())->isNotEmpty())
            <div class="alert-box bg-orange-900/40 border-orange-700 text-orange-200">
                ⚠️ يوجد {{ $longOpenShifts->count() }} شفت مفتوح منذ مدة طويلة.
                <span class="block text-xs mt-1 text-orange-100/80">
                    {{ $longOpenShifts->take(3)->map(fn($shift) => $shift->store_name . ' - ' . $shift->hours_open . ' ساعة')->implode('، ') }}
                </span>
            </div>
        @endif

        @if(($employeesWithoutSalaryCount ?? 0) > 0)
            <div class="alert-box bg-red-900/40 border-red-700 text-red-200">
                ⚠️ يوجد {{ $employeesWithoutSalaryCount }} موظف بدون راتب محدد.
                @if(isset($employeesWithoutSalary) && $employeesWithoutSalary->isNotEmpty())
                    <span class="block text-xs mt-1 text-red-100/80">
                        {{ $employeesWithoutSalary->take(3)->map(fn($employee) => $employee->name . ($employee->store?->name ? ' - ' . $employee->store->name : ''))->implode('، ') }}
                        @if($employeesWithoutSalaryCount > 3)
                            ، وآخرون
                        @endif
                    </span>
                @endif
            </div>
        @endif

    </div>


{{-- ========================================================= --}}
{{--  بطاقات المتاجر (للتنقل) - نسخة مصغرة --}}
{{-- ========================================================= --}}
@if($stores->count() > 0)
    <div class="flex flex-wrap items-center gap-2">
        @foreach($stores as $store)
            <a href="{{ route('user.stores.show', $store->id) }}"
               class="group inline-flex items-center gap-2 px-3 py-1.5 bg-gray-900/50 border border-gray-800 hover:border-emerald-500/50 rounded-lg transition-all duration-200">

                {{-- أيقونة المتجر --}}
                <div class="w-6 h-6 rounded-md bg-gradient-to-br from-emerald-900/50 to-gray-900 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>

                {{-- اسم المتجر (كاملاً بدون اختصار) --}}
                <span class="text-xs text-gray-300 group-hover:text-emerald-400 transition-colors whitespace-nowrap">
                    {{ $store->name }}
                </span>
            </a>
        @endforeach
    </div>
@else
    <div class="text-sm text-gray-400">
        لا يوجد متاجر بعد
        <a href="{{ route('user.stores.create') }}" class="text-emerald-400 hover:text-emerald-300 mr-1">
            إضافة متجر
        </a>
    </div>
@endif
    {{--  القسم الرابع: الإحصائيات العامة (دمج بين الداشبوردين) --}}
    {{-- ========================================================= --}}
    <div class="mt-1 mb-3 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-400">الملخص المالي اليومي</p>
            <p class="mt-1 text-[11px] text-gray-500">
                النطاق الحالي:
                <span class="font-semibold text-cyan-300">{{ $selectedSummaryStore?->name ?? 'جميع المتاجر' }}</span>
            </p>
            <p class="mt-1 text-[11px] text-gray-500">
                العمليات الداخلة: <span class="text-gray-300">{{ number_format($dailySalesOperationsCount ?? 0) }}</span>
                — كاش: <span class="text-emerald-300">{{ number_format($dailyCashSales ?? 0, 2) }}</span>
                — شبكة: <span class="text-cyan-300">{{ number_format($dailyCardSales ?? 0, 2) }}</span>
            </p>
        </div>
        <form method="GET" action="{{ route('user.dashboard') }}" class="flex items-end gap-2">
            <label class="text-[11px] text-gray-400">
                المقارنة مع صفحة مبيعات متجر
                <select name="summary_store_id" class="mt-1 block min-w-48 rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-xs text-white" onchange="this.form.submit()">
                    <option value="">جميع المتاجر</option>
                    @foreach($stores as $summaryStore)
                        <option value="{{ $summaryStore->id }}" @selected(($selectedSummaryStore?->id ?? null) === $summaryStore->id)>
                            {{ $summaryStore->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            @if($selectedSummaryStore)
                <a href="{{ route('user.dashboard') }}" class="rounded-lg border border-gray-700 px-3 py-2 text-xs text-gray-300 hover:border-gray-500">إلغاء الفلتر</a>
            @endif
        </form>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- الربح المحتسب اليوم كما يظهر في صفحة المبيعات اليومية --}}
        <button type="button" class="text-right metric-card" data-metric="profit_today" title="للمزيد من التفاصيل اضغط: الربح المحتسب اليوم حسب كل متجر">
            <x-stat-card title="الربح المحتسب اليوم"
                value="{{ number_format($profitToday, 2) }}"
                color="{{ $profitToday >= 0 ? 'emerald' : 'red' }}" />
        </button>

        {{-- [تعديل آمن] مبيعات اليوم محسوبة من المحصّل الفعلي --}}
        <button type="button" class="text-right metric-card" data-metric="sales_today" title="للمزيد من التفاصيل اضغط: قيمة المبيعات اليوم حسب كل متجر">
            <x-stat-card title="قيمة المبيعات اليوم" value="{{ number_format($salesToday, 2) }}" color="emerald" />
        </button>

        {{-- مصروفات اليوم --}}
        <button type="button" class="text-right metric-card" data-metric="expenses_today" title="للمزيد من التفاصيل اضغط: مصروفات اليوم حسب كل متجر">
            <x-stat-card title="مصروفات اليوم" value="{{ number_format($expensesToday, 2) }}" color="red" />
        </button>

        <button type="button" class="text-right metric-card" data-metric="products_cost_today" title="للمزيد من التفاصيل اضغط: تكلفة المنتجات المباعة اليوم حسب كل متجر">
            <x-stat-card title="تكلفة المنتجات المباعة اليوم" value="{{ number_format($productsCostToday, 2) }}" color="yellow" />
        </button>

    </div>

    {{-- الصف الثاني --}}
    <p class="text-xs font-semibold text-gray-400 mt-5 mb-2">الملخص الشهري</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        <button type="button" class="text-right metric-card" data-metric="profit_month" title="للمزيد من التفاصيل اضغط: المتبقي بعد التكاليف الشهرية حسب كل متجر">
            <x-stat-card title="المتبقي بعد التكاليف الشهرية"
                value="{{ number_format($profitMonth, 2) }}"
                color="{{ $profitMonth >= 0 ? 'emerald' : 'red' }}" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="sales_month" title="للمزيد من التفاصيل اضغط: مبيعات الشهر حسب كل متجر">
            <x-stat-card title="مبيعات الشهر" value="{{ number_format($salesMonth, 2) }}" color="emerald" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="expenses_month" title="للمزيد من التفاصيل اضغط: مصروفات الشهر حسب كل متجر">
            <x-stat-card title="مصروفات الشهر" value="{{ number_format($expensesMonth, 2) }}" color="red" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="products_cost_month" title="للمزيد من التفاصيل اضغط: تكلفة المنتجات المباعة خلال الشهر حسب كل متجر">
            <x-stat-card title="تكلفة المنتجات المباعة (شهري)" value="{{ number_format($productsCostMonth ?? 0, 2) }}" color="yellow" />
        </button>
    </div>

    <p class="text-xs font-semibold text-gray-400 mt-5 mb-2">التشغيل والاستهلاك</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat-card title="عدد المتاجر" value="{{ $stores->count() }}" color="indigo" />
        <x-stat-card title="عدد الموظفين" value="{{ $employeesCount }}" color="yellow" />

        <button type="button" class="text-right metric-card" data-metric="salaries_month" title="للمزيد من التفاصيل اضغط: الرواتب الشهرية حسب كل متجر (للتوضيح فقط)">
            <x-stat-card title="الرواتب الشهرية (للتوضيح)" value="{{ number_format($monthlySalaries ?? 0, 2) }}" color="indigo" />
        </button>

        <button type="button" class="text-right metric-card" data-metric="withdrawals_month" title="عرض الموظفين وسحوباتهم خلال الشهر">
            <x-stat-card title="سحوبات الموظفين (شهري)" value="{{ number_format($monthlyWorkerWithdrawals ?? 0, 2) }}" color="red" />
        </button>

        <button type="button" class="text-right metric-card" data-metric="salary_remaining_month" title="عرض المتبقي لكل موظف بعد السحوبات">
            <x-stat-card title="المتبقي من الرواتب بعد السحوبات" value="{{ number_format($netMonthlySalaries ?? 0, 2) }}" color="emerald" />
        </button>

        <x-stat-card title="مشتريات المالك (شهري)"
            value="{{ number_format($monthlyOwnerPurchases ?? 0, 2) }} ر.س"
            color="blue" />

        <x-stat-card title="استهلاك داخلي (المحاسب)"
            value="{{ number_format($monthlyAccountantConsumption ?? 0, 2) }} ر.س"
            color="yellow" />
    </div>

    <div class="grid grid-cols-1 gap-4 mt-4">
        <button type="button" class="text-right metric-card" data-metric="monthly_purchases_consumption" title="للمزيد من التفاصيل اضغط: المشتريات والاستهلاك الداخلي حسب كل متجر">
            <x-stat-card title="المشتريات والاستهلاك (شهري)"
                value="{{ number_format($monthlyPurchasesAndConsumption, 2) }} ر.س"
                color="purple" />
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-center justify-between gap-3 mb-4">
                <div>
                    <p class="text-sm font-semibold text-white">المنتجات منخفضة المخزون</p>
                    <p class="text-xs text-gray-500 mt-1">المنتجات المتبقية التي وصلت إلى الحد الأدنى؛ المنتجات النافدة غير معروضة</p>
                </div>
                <div class="text-left rounded-xl bg-amber-500/10 border border-amber-500/20 px-4 py-2">
                    <p class="text-amber-300 font-black text-xl">{{ number_format($lowStockCount ?? 0) }}</p>
                    <p class="text-[11px] text-gray-500">منتج منخفض</p>
                </div>
            </div>
            <div class="space-y-2">
                @forelse(($lowStockProducts ?? collect()) as $product)
                    <div class="flex items-center justify-between text-xs border-b border-gray-800 pb-2 last:border-0">
                        <div><span class="text-gray-200">{{ $product->name }}</span><span class="text-gray-500 mr-1">— {{ $product->store?->name }}</span></div>
                        <span class="text-amber-300 font-bold">{{ number_format($product->quantity, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-emerald-300">المخزون ضمن الحدود المحددة.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
            <div class="mb-4">
                <p class="text-sm font-semibold text-white">أفضل منتج في كل متجر</p>
                <p class="text-xs text-gray-500 mt-1">المنتج الأعلى مبيعًا خلال الشهر لكل متجر تابع</p>
            </div>
            <div class="space-y-3">
                @forelse(($topSellingProducts ?? collect()) as $index => $product)
                    <div class="flex items-center gap-3 border-b border-gray-800 pb-3 last:border-0">
                        <span class="w-7 h-7 rounded-full bg-cyan-500/10 text-cyan-300 flex items-center justify-center text-xs font-bold">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-100 truncate">{{ $product->name }}</p>
                            <p class="text-[11px] text-cyan-400">{{ $product->store_name }}</p>
                            <p class="text-[10px] text-gray-500">{{ number_format($product->operations_count) }} عملية بيع</p>
                        </div>
                        <div class="text-left">
                            <p class="text-emerald-300 font-bold text-sm">{{ number_format($product->sold_quantity, 2) }}</p>
                            <p class="text-[10px] text-gray-500">{{ number_format($product->sales_value, 2) }} ر.س</p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">لا توجد مبيعات منتجات في المتاجر خلال الشهر.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if($bestStorePerformance)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4">
            <p class="text-xs text-emerald-300">أفضل متجر هذا الشهر</p>
            <p class="text-white font-bold mt-1">{{ $bestStorePerformance['store_name'] }}</p>
            <p class="text-sm text-emerald-200 mt-2">المتبقي بعد التكاليف: {{ number_format($bestStorePerformance['profit_month'], 2) }} ر.س</p>
            <p class="text-xs text-gray-400 mt-1">المبيعات: {{ number_format($bestStorePerformance['sales_month'], 2) }} ر.س</p>
        </div>
        @if($worstStorePerformance)
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-4">
            <p class="text-xs text-rose-300">أضعف متجر هذا الشهر</p>
            <p class="text-white font-bold mt-1">{{ $worstStorePerformance['store_name'] }}</p>
            <p class="text-sm text-rose-200 mt-2">المتبقي بعد التكاليف: {{ number_format($worstStorePerformance['profit_month'], 2) }} ر.س</p>
            <p class="text-xs text-gray-400 mt-1">المبيعات: {{ number_format($worstStorePerformance['sales_month'], 2) }} ر.س</p>
        </div>
        @endif
    </div>
    @endif

    {{-- ========================================================= --}}
    {{--  القسم الخامس: تحليل المديونيات --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-stat-card title="مديونيات مفتوحة" value="{{ $creditOpen }}" color="yellow" />
        <x-stat-card title="مديونيات مسددة" value="{{ $creditClosed }}" color="emerald" />
        <x-stat-card title="مديونيات متأخرة" value="{{ $creditLate }}" color="red" />
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السادس: المخطط الذكي --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
        <p class="text-sm font-semibold text-white mb-1">أداء آخر 14 يوم</p>
        <p class="text-xs text-gray-400 mb-3">
            {{-- [تعديل آمن] المخطط يستخدم نفس تعريف التقرير الشهري: المبيعات المحصلة، المصروفات، وتكلفة المنتجات المباعة. --}}
        </p>

        <div class="flex flex-wrap gap-4 mb-3 text-xs">
            <span class="inline-flex items-center gap-2 text-emerald-300"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>مبيعات</span>
            <span class="inline-flex items-center gap-2 text-red-300"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>مصروفات</span>
            <span class="inline-flex items-center gap-2 text-blue-300"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>تكلفة المنتجات</span>
        </div>

        <canvas id="smartChart" class="w-full h-64"></canvas>
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السابع: آخر العمليات --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
    <div class="flex items-center justify-between gap-3 mb-3">
        <p class="text-sm font-semibold text-white">آخر العمليات</p>
        <a href="{{ route('user.logs.index') }}" class="text-xs text-cyan-300 hover:text-cyan-200">عرض السجل الكامل</a>
    </div>

    <div class="space-y-4 max-h-72 overflow-y-auto custom-scrollbar">

        @forelse ($activities as $activity)
            <div class="border-b border-gray-800 pb-3 last:border-none">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="rounded-md bg-cyan-500/10 px-2 py-1 text-[10px] font-semibold text-cyan-300">{{ $activity->action_label }}</span>
                        <span class="text-xs text-emerald-400 font-semibold">{{ $activity->store?->name ?? 'متجر غير معروف' }}</span>
                    </div>
                    <span class="text-[10px] text-gray-500">{{ $activity->created_at->locale('ar')->diffForHumans() }}</span>
                </div>
                <p class="text-xs text-gray-300 mt-2 leading-relaxed">{{ $activity->snippet ?: 'عملية مسجلة بدون وصف' }}</p>
                <div class="mt-2 flex flex-wrap items-center gap-3 text-[10px] text-gray-500">
                    <span>بواسطة: <span class="text-gray-300">{{ $activity->actor_display_name }}</span></span>
                    <span>{{ $activity->created_at->format('Y-m-d H:i') }}</span>
                    @if($activity->model_id)
                        <span>مرجع #{{ $activity->model_id }}</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-500">لا توجد عمليات مسجلة.</p>
        @endforelse

    </div>
</div>

{{-- نافذة تفاصيل البطاقات --}}
<div id="metric-modal" class="hidden fixed inset-0 z-50 bg-black/60 p-4">
    <div class="max-w-xl mx-auto mt-20 bg-gray-900 border border-gray-700 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 id="metric-modal-title" class="text-white font-bold text-lg"></h3>
            <button type="button" id="metric-modal-close" class="text-gray-400 hover:text-white">✕</button>
        </div>
        <p id="metric-modal-value" class="text-2xl font-black text-emerald-400 mb-2"></p>
        <p id="metric-modal-details" class="text-sm text-gray-300 leading-7"></p>
    </div>
</div>

</div>

{{-- ========================================================= --}}
{{--  سكربت المخطط --}}
{{-- ========================================================= --}}
<script>
(function () {
    const labels   = @json($chartLabels);
    const sales    = @json($chartSales);
    const expenses = @json($chartExpenses);
    const productCosts = @json($chartProductCosts);

    const canvas = document.getElementById('smartChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    function drawChart() {
        const dpr = window.devicePixelRatio || 1;
        const cssWidth = canvas.clientWidth || 600;
        const cssHeight = canvas.clientHeight || 260;

        canvas.width = Math.floor(cssWidth * dpr);
        canvas.height = Math.floor(cssHeight * dpr);

        // [تعديل آمن] منع تراكم الـ scale عند كل resize لضمان دقة الرسم.
        ctx.setTransform(1, 0, 0, 1, 0, 0);
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        const margin = { top: 20, right: 16, bottom: 36, left: 46 };
        const innerWidth  = cssWidth  - margin.left - margin.right;
        const innerHeight = cssHeight - margin.top  - margin.bottom;

        if (innerWidth <= 0 || innerHeight <= 0) return;

        const maxValue = Math.max(
            10,
            ...sales,
            ...expenses,
            ...productCosts
        );

        const stepX = innerWidth / Math.max(labels.length - 1, 1);

        function yScale(value) {
            return margin.top + innerHeight - (value / maxValue) * innerHeight;
        }

        // شبكة خلفية (محور Y)
        ctx.strokeStyle = 'rgba(148, 163, 184, 0.18)';
        ctx.lineWidth = 1;
        const ticks = 4;
        for (let i = 0; i <= ticks; i++) {
            const y = margin.top + (innerHeight / ticks) * i;
            ctx.beginPath();
            ctx.moveTo(margin.left, y);
            ctx.lineTo(margin.left + innerWidth, y);
            ctx.stroke();

            const val = Math.round(maxValue - (maxValue / ticks) * i);
            ctx.fillStyle = 'rgba(148, 163, 184, 0.75)';
            ctx.font = '11px sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(val.toLocaleString('en-US'), margin.left - 6, y + 3);
        }

        // محور X (عرض تواريخ متباعدة لتجنب التزاحم)
        ctx.fillStyle = 'rgba(148, 163, 184, 0.75)';
        ctx.font = '10px sans-serif';
        ctx.textAlign = 'center';
        const labelStep = Math.max(1, Math.ceil(labels.length / 6));
        labels.forEach((label, i) => {
            if (i % labelStep !== 0 && i !== labels.length - 1) return;
            const x = margin.left + i * stepX;
            ctx.fillText(label.slice(5), x, margin.top + innerHeight + 16);
        });

        function drawLine(data, color) {
            ctx.strokeStyle = color;
            ctx.lineWidth = 2;
            ctx.beginPath();

            data.forEach((v, i) => {
                const x = margin.left + i * stepX;
                const y = yScale(v);
                if (i === 0) ctx.moveTo(x, y);
                else ctx.lineTo(x, y);
            });

            ctx.stroke();

            // نقاط البيانات
            ctx.fillStyle = color;
            data.forEach((v, i) => {
                const x = margin.left + i * stepX;
                const y = yScale(v);
                ctx.beginPath();
                ctx.arc(x, y, 2.5, 0, Math.PI * 2);
                ctx.fill();
            });
        }

        drawLine(sales, '#34d399');    // مبيعات
        drawLine(expenses, '#f87171'); // مصروفات
        drawLine(productCosts, '#60a5fa'); // تكلفة المنتجات
    }

    drawChart();
    window.addEventListener('resize', drawChart);
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const storeBreakdowns = @json($metricStoreBreakdowns ?? []);
    const selectedSummaryStoreId = @json($selectedSummaryStore?->id);
    const employeeWithdrawals = @json($employeeMonthlyWithdrawals ?? []);
    const employeeSalaryRemainders = @json($employeeSalaryRemainders ?? []);
    const metricDefinitions = {
        profit_today: { title: 'الربح المحتسب اليوم', value: '{{ number_format($profitToday, 2) }} ر.س', details: 'يطابق الربح المحتسب في صفحة المبيعات اليومية، ولا تُخصم منه المصروفات لأنها معروضة في بطاقة مستقلة.' },
        sales_today: { title: 'قيمة المبيعات اليوم', value: '{{ number_format($salesToday, 2) }} ر.س', details: 'يطابق حقل قيمة المبيعات في صفحة المبيعات اليومية للنطاق المختار، ولا يضيف تحصيلات الآجل المستقلة.' },
        expenses_today: { title: 'مصروفات اليوم', value: '{{ number_format($expensesToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        products_cost_today: { title: 'تكلفة المنتجات المباعة اليوم', value: '{{ number_format($productsCostToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        profit_month: { title: 'المتبقي بعد التكاليف الشهرية', value: '{{ number_format($profitMonth, 2) }} ر.س', details: 'المحصل - (تكلفة المنتجات + الاستهلاك الداخلي + مشتريات المالك + المصروفات). الرواتب والسحوبات لا تدخل في هذه المعادلة، مثل التقرير الشهري.' },
        sales_month: { title: 'مبيعات الشهر', value: '{{ number_format($salesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        expenses_month: { title: 'مصروفات الشهر', value: '{{ number_format($expensesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        products_cost_month: { title: 'تكلفة المنتجات المباعة (شهري)', value: '{{ number_format($productsCostMonth ?? 0, 2) }} ر.س', details: 'تكلفة البضاعة المباعة خلال الشهر، وهي أحد البنود المخصومة للوصول إلى المتبقي بعد التكاليف.' },
        salaries_month: { title: 'الرواتب الشهرية (للتوضيح)', value: '{{ number_format($monthlySalaries ?? 0, 2) }} ر.س', details: 'تعرض للمراجعة فقط ولا تُخصم من صافي النتيجة، مثل التقرير الشهري. السحوبات المسجلة: {{ number_format($monthlyWorkerWithdrawals ?? 0, 2) }} ر.س.' },
        withdrawals_month: { title: 'سحوبات الموظفين (شهري)', value: '{{ number_format($monthlyWorkerWithdrawals ?? 0, 2) }} ر.س', details: 'تفصيل السحوبات حسب الموظفين.' },
        salary_remaining_month: { title: 'المتبقي من الرواتب بعد السحوبات', value: '{{ number_format($netMonthlySalaries ?? 0, 2) }} ر.س', details: 'راتب كل موظف مطروحاً منه سحوباته المسجلة خلال الشهر.' },
        monthly_purchases_consumption: { title: 'المشتريات والاستهلاك (شهري)', value: '{{ number_format($monthlyPurchasesAndConsumption, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
    };

    const modal = document.getElementById('metric-modal');
    const closeBtn = document.getElementById('metric-modal-close');
    const titleEl = document.getElementById('metric-modal-title');
    const valueEl = document.getElementById('metric-modal-value');
    const detailsEl = document.getElementById('metric-modal-details');

    function formatByMetric(metricKey, amount) {
        const numeric = Number(amount || 0);
        if (metricKey === 'expenses_today' || metricKey === 'expenses_month') {
            return `<span class="text-rose-300 font-bold">${numeric.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        }
        if (metricKey === 'profit_today' || metricKey === 'profit_month') {
            const color = numeric >= 0 ? 'text-emerald-300' : 'text-red-300';
            return `<span class="${color} font-bold">${numeric.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
        }
        return `<span class="text-cyan-300 font-bold">${numeric.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`;
    }

    document.querySelectorAll('.metric-card').forEach((card) => {
        card.addEventListener('click', () => {
            const key = card.dataset.metric;
            const data = metricDefinitions[key];
            if (!data) return;
            titleEl.textContent = data.title;
            valueEl.textContent = data.value;
            let rows = '';
            if (key === 'withdrawals_month') {
                rows = employeeWithdrawals.map((employee) => `<li class="border-b border-gray-800 py-2">
                    <div class="flex justify-between"><span class="text-gray-200">${employee.name} - ${employee.store_name}</span><span class="text-rose-300 font-bold">${Number(employee.withdrawals_total).toLocaleString('en-US', {minimumFractionDigits: 2})} ر.س</span></div>
                </li>`).join('');
            } else if (key === 'salary_remaining_month') {
                rows = employeeSalaryRemainders.map((employee) => `<li class="border-b border-gray-800 py-2">
                    <div class="flex justify-between"><span class="text-gray-200">${employee.name} - ${employee.store_name || ''}</span><span class="text-emerald-300 font-bold">${Number(employee.salary_remaining).toLocaleString('en-US', {minimumFractionDigits: 2})} ر.س</span></div>
                    <div class="text-[11px] text-gray-500 mt-1">الراتب ${Number(employee.salary).toLocaleString('en-US')} - السحوبات ${Number(employee.withdrawals_total).toLocaleString('en-US')}</div>
                </li>`).join('');
            } else {
                const dailyMetrics = ['profit_today', 'sales_today', 'expenses_today', 'products_cost_today'];
                const visibleBreakdowns = selectedSummaryStoreId && dailyMetrics.includes(key)
                    ? storeBreakdowns.filter((store) => Number(store.store_id) === Number(selectedSummaryStoreId))
                    : storeBreakdowns;
                rows = visibleBreakdowns.map((store) => `<li class="flex items-center justify-between border-b border-gray-800 py-2">
                    <span class="text-gray-200">${store.store_name}</span>
                    <span>${formatByMetric(key, store[key])} <span class="text-gray-500 text-xs">ر.س</span></span>
                </li>`).join('');
            }

            const employeeMetric = key === 'withdrawals_month' || key === 'salary_remaining_month';
            detailsEl.innerHTML = `
                <p class="mb-2">${data.details}</p>
                <p class="text-xs text-gray-400 mb-1">${employeeMetric ? 'تفصيل حسب الموظفين:' : 'تفصيل حسب كل متجر:'}</p>
                <ul class="max-h-52 overflow-y-auto pr-1">${rows || (employeeMetric ? '<li class="text-gray-500 py-2">لا توجد بيانات موظفين.</li>' : '<li class="text-gray-500 py-2">لا توجد متاجر متاحة.</li>')}</ul>
            `;
            modal.classList.remove('hidden');
        });
    });

    closeBtn?.addEventListener('click', () => modal.classList.add('hidden'));
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) modal.classList.add('hidden');
    });
});
</script>

@endsection
