@extends('dashboard.app')

@section('title', 'لوحة التحكم')

@section('content')
@php
    /*
     * قيم توافق آمنة لنسخة واجهة لوحة المالك الموسعة.
     * تبقى الواجهة قابلة للعمل مع الكنترولر الأصلي، وتعرض القيم الفارغة فقط
     * للبيانات الإضافية التي لا يرسلها الكنترولر بدل ظهور Undefined variable.
     */
    $dailySalesOperationsCount = $dailySalesOperationsCount ?? 0;
    $lowStockCount = $lowStockCount ?? 0;
    $lowStockProducts = $lowStockProducts ?? collect();
    $topSellingProducts = $topSellingProducts ?? collect();
    $employeeSalaryRemainders = $employeeSalaryRemainders ?? [];
    // موظفون بلا راتب: يستخدمان في التنبيه والقائمة التفصيلية أدناه.
    $employeesWithoutSalary = $employeesWithoutSalary ?? collect();
    $employeesWithoutSalaryCount = $employeesWithoutSalaryCount ?? $employeesWithoutSalary->count();
@endphp
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

        {{-- تنبيه إداري مهم: الموظفون الذين راتبهم غير مسجل أو يساوي صفرًا. --}}
        @if($employeesWithoutSalaryCount > 0)
            <div class="alert-box bg-purple-900/40 border-purple-700 text-purple-100">
                <div class="flex flex-col gap-2">
                    <p class="font-semibold">
                        ⚠️ يوجد {{ $employeesWithoutSalaryCount }} موظف لم يُسجّل له راتب.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($employeesWithoutSalary as $employee)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-black/20 px-2 py-1 text-xs">
                                <span>{{ $employee->name }}</span>
                                <span class="text-purple-300">— {{ $employee->store->name ?? 'متجر غير معروف' }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
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
    <div class="flex flex-wrap items-center justify-between gap-2 mt-1 mb-2">
        <div class="flex items-center gap-3">
            <p class="text-xs font-semibold text-gray-400">الملخص اليومي</p>
            <label for="daily-summary-store-filter" class="sr-only">تصفية الملخص اليومي حسب المتجر</label>
            <select id="daily-summary-store-filter"
                    class="bg-gray-900 border border-gray-700 rounded-lg px-2 py-1 text-[11px] text-gray-300 focus:border-cyan-500 focus:outline-none">
                <option value="">جميع المتاجر</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" @selected(optional($selectedSummaryStore ?? null)->id === $store->id)>
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="inline-flex items-center gap-2 text-[11px] text-gray-400">
            <span id="live-status-dot" class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>العمليات اليوم:</span>
            <strong id="live-operations-count" class="text-cyan-300">{{ number_format($dailySalesOperationsCount) }}</strong>
            <span id="live-updated-at" class="text-gray-600">تحديث مباشر</span>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

        {{-- صافي الربح اليوم --}}
        <button id="daily-profit-card" type="button" class="text-right metric-card" data-metric="profit_today" title="للمزيد من التفاصيل اضغط: صافي الربح اليومي حسب كل متجر">
            <x-stat-card title="صافي الربح اليوم"
                value="{{ number_format($profitToday) }}"
                value-id="daily-profit-value"
                color="{{ $profitToday >= 0 ? 'emerald' : 'red' }}" />
        </button>

        {{-- [تعديل آمن] مبيعات اليوم محسوبة من المحصّل الفعلي --}}
        <button id="daily-sales-card" type="button" class="text-right metric-card" data-metric="sales_today" title="للمزيد من التفاصيل اضغط: مبيعات اليوم حسب كل متجر">
            <x-stat-card title="مبيعات اليوم" value="{{ number_format($salesToday) }}" value-id="daily-sales-value" color="emerald" />
        </button>

        {{-- مصروفات اليوم --}}
        <button id="daily-expenses-card" type="button" class="text-right metric-card" data-metric="expenses_today" title="للمزيد من التفاصيل اضغط: مصروفات اليوم حسب كل متجر">
            <x-stat-card title="مصروفات اليوم" value="{{ number_format($expensesToday) }}" value-id="daily-expenses-value" color="red" />
        </button>

        <button id="daily-products-cost-card" type="button" class="text-right metric-card" data-metric="products_cost_today" title="للمزيد من التفاصيل اضغط: تكلفة المنتجات المباعة اليوم حسب كل متجر">
            <x-stat-card title="تكلفة المنتجات المباعة اليوم" value="{{ number_format($productsCostToday, 2) }}" value-id="daily-products-cost-value" color="yellow" />
        </button>

        <div id="live-operation-card" class="relative overflow-hidden bg-gray-900/70 border border-gray-800 rounded-2xl px-4 py-3 transition-colors duration-500">
            <span id="live-operation-amount" class="absolute left-3 top-2 text-[10px] font-bold text-emerald-300">0.00</span>
            <div class="flex items-center gap-3 min-h-[52px] pl-12">
                <span class="w-8 h-8 shrink-0 rounded-lg bg-cyan-500/15 text-cyan-300 flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-xs"></i>
                </span>
                <div class="min-w-0 text-right">
                    <p id="live-operation-product" class="text-sm font-bold text-white truncate">جاري متابعة العمليات...</p>
                    <p class="text-[10px] text-gray-500 mt-1 truncate">
                        <span id="live-operation-store">—</span>
                        <span class="mx-1">•</span>
                        <span id="live-operation-time">--:--</span>
                    </p>
                </div>
            </div>
        </div>

    </div>

    {{-- الصف الثاني --}}
    <p class="text-xs font-semibold text-gray-400 mt-5 mb-2">الملخص الشهري</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">

        <button type="button" class="text-right metric-card" data-metric="profit_month" title="للمزيد من التفاصيل اضغط: صافي الربح الشهري حسب كل متجر">
            <x-stat-card title="صافي الربح الشهري (بعد الخصومات)"
                value="{{ number_format($profitMonth) }}"
                color="{{ $profitMonth >= 0 ? 'emerald' : 'red' }}" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="sales_month" title="للمزيد من التفاصيل اضغط: مبيعات الشهر حسب كل متجر">
            <x-stat-card title="مبيعات الشهر" value="{{ number_format($salesMonth) }}" color="emerald" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="expenses_month" title="للمزيد من التفاصيل اضغط: مصروفات الشهر حسب كل متجر">
            <x-stat-card title="مصروفات الشهر" value="{{ number_format($expensesMonth) }}" color="red" />
        </button>
        <button type="button" class="text-right metric-card" data-metric="salaries_month" title="للمزيد من التفاصيل اضغط: الرواتب الشهرية حسب كل متجر">
            <x-stat-card title="الرواتب الشهرية" value="{{ number_format($monthlySalaries ?? 0) }}" color="indigo" />
        </button>
        <button id="salary-after-withdrawals-card" type="button" class="text-right" title="عرض المتبقي من الرواتب والسحوبات حسب المتجر والموظف">
            <x-stat-card title="الرواتب بعد السحب" value="{{ number_format($netMonthlySalaries ?? 0) }}" color="blue" />
        </button>
    </div>

    <p class="text-xs font-semibold text-gray-400 mt-5 mb-2">التشغيل والاستهلاك</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat-card title="عدد المتاجر" value="{{ $stores->count() }}" color="indigo" />
        <x-stat-card title="عدد الموظفين" value="{{ $employeesCount }}" color="yellow" />

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

    {{-- ========================================================= --}}
    {{--  القسم الخامس: تحليل المديونيات --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-stat-card title="مديونيات مفتوحة" value="{{ $creditOpen }}" color="yellow" />
        <x-stat-card title="مديونيات مسددة" value="{{ $creditClosed }}" color="emerald" />
        <x-stat-card title="مديونيات متأخرة" value="{{ $creditLate }}" color="red" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-semibold text-white">المنتجات منخفضة المخزون</p>
                <span class="text-xs text-yellow-300">{{ number_format($lowStockCount) }} منتج</span>
            </div>
            <div class="max-h-72 overflow-y-auto custom-scrollbar space-y-2 pr-1">
                @forelse($lowStockProducts as $product)
                    <div class="flex items-center justify-between gap-3 border-b border-gray-800 pb-2">
                        <div>
                            <p class="text-sm text-gray-200">{{ $product->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $product->store->name ?? 'متجر غير معروف' }}</p>
                        </div>
                        <span class="text-xs font-bold text-yellow-300">{{ number_format((float) $product->quantity, 2) }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">لا توجد منتجات منخفضة المخزون.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-white">الأكثر مبيعًا حسب المتجر</p>
                    <p class="text-[11px] text-gray-500 mt-1">أفضل 5 منتجات خلال الشهر الحالي</p>
                </div>
                <i class="fa-solid fa-ranking-star text-amber-300"></i>
            </div>
            <div class="max-h-72 overflow-y-auto custom-scrollbar space-y-5 pr-1">
                @forelse($topSellingProducts->groupBy('store_id') as $storeProducts)
                    @php
                        $highestQuantity = max(1, (float) $storeProducts->max('sold_quantity'));
                    @endphp
                    <div class="rounded-xl border border-gray-800 bg-gray-950/40 p-3">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-bold text-emerald-300">{{ $storeProducts->first()->store_name }}</p>
                            <span class="text-[10px] text-gray-500">{{ $storeProducts->count() }} منتجات</span>
                        </div>
                        <div class="space-y-3">
                            @foreach($storeProducts as $index => $product)
                                <div>
                                    <div class="flex items-center justify-between gap-3 text-xs mb-1.5">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="w-5 h-5 shrink-0 rounded-full bg-gray-800 text-gray-400 flex items-center justify-center text-[10px]">{{ $index + 1 }}</span>
                                            <span class="text-gray-200 truncate">{{ $product->name }}</span>
                                        </div>
                                        <div class="text-left shrink-0">
                                            <span class="text-cyan-300 font-bold">{{ number_format((float) $product->sold_quantity, 2) }}</span>
                                            <span class="text-[10px] text-gray-600 mr-1">{{ number_format((float) $product->sales_value, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-gray-800 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-l from-cyan-400 to-emerald-400"
                                             style="width: {{ min(100, ((float) $product->sold_quantity / $highestQuantity) * 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-500">لا توجد مبيعات منتجات خلال الشهر الحالي.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السادس: المخطط الذكي --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
        <p class="text-sm font-semibold text-white mb-1">أداء آخر 14 يوم</p>
        <p class="text-xs text-gray-400 mb-3">
            {{-- [تعديل آمن] القيم في هذا المخطط تعرض اتجاه الأداء اليومي للفواتير والمصروفات والآجل لتسهيل القراءة السريعة. --}}
        </p>

        <div class="flex flex-wrap gap-4 mb-3 text-xs">
            <span class="inline-flex items-center gap-2 text-emerald-300"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>مبيعات</span>
            <span class="inline-flex items-center gap-2 text-red-300"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>مصروفات</span>
            <span class="inline-flex items-center gap-2 text-blue-300"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>الديون المتبقية</span>
        </div>

        <canvas id="smartChart" class="w-full h-64"></canvas>
    </div>

    {{-- ========================================================= --}}
    {{--  القسم السابع: آخر العمليات --}}
    {{-- ========================================================= --}}
    <div class="bg-gray-900/70 border border-gray-800 rounded-2xl p-5">
    <p class="text-sm font-semibold text-white mb-3">آخر العمليات</p>

    <div class="space-y-4 max-h-72 overflow-y-auto custom-scrollbar">

        @forelse ($activities as $activity)
            @php
                $store = $activity->store;
                $employeeName = null;

                // استخراج اسم الموظف من الوصف إذا كان موجودًا
                if (preg_match('/الْمُوَظَّف\s+([^\s]+)/u', $activity->description, $matches)) {
                    $employeeName = $matches[1];
                }
            @endphp

            <div class="border-b border-gray-800 pb-3 last:border-none">

                {{-- اسم المتجر --}}
                <p class="text-xs text-emerald-400 font-semibold">
                    {{ $store->name ?? 'متجر غير معروف' }}
                </p>

                {{-- اسم الموظف إن وجد --}}
                @if($employeeName)
                    <p class="text-xs text-gray-400">
                        الموظف: {{ $employeeName }}
                    </p>
                @endif

                {{-- وصف العملية --}}
                <p class="text-xs text-gray-300 mt-1 leading-relaxed">
                    {{ $activity->description }}
                </p>

                {{-- الوقت --}}
                <p class="text-[11px] text-gray-500 mt-1">
                    {{ $activity->created_at->format('Y-m-d H:i') }}
                </p>
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

{{-- نافذة الرواتب بعد السحب: المتاجر أولاً، ثم موظفو المتجر عند الضغط عليه. --}}
<div id="salary-withdrawals-modal" class="hidden fixed inset-0 z-50 bg-black/70 p-4 overflow-y-auto">
    <div class="max-w-3xl mx-auto mt-10 mb-10 bg-gray-900 border border-gray-700 rounded-2xl overflow-hidden">
        <div class="p-5 border-b border-gray-800 flex items-center justify-between gap-3">
            <div>
                <h3 class="text-white font-bold text-lg">الرواتب بعد السحب</h3>
                <p class="text-xs text-gray-400 mt-1">اضغط اسم المتجر لعرض الموظفين وإجمالي سحب كل موظف والمتبقي من راتبه.</p>
            </div>
            <button type="button" id="salary-withdrawals-close" class="text-gray-400 hover:text-white">✕</button>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-3 border-b border-gray-800 bg-gray-950/40">
            <div class="rounded-xl border border-gray-800 p-3">
                <p class="text-[11px] text-gray-500">إجمالي الرواتب</p>
                <p class="text-indigo-300 font-bold mt-1">{{ number_format($monthlySalaries ?? 0, 2) }}</p>
            </div>
            <div class="rounded-xl border border-gray-800 p-3">
                <p class="text-[11px] text-gray-500">إجمالي السحوبات</p>
                <p class="text-red-300 font-bold mt-1">{{ number_format($monthlyWorkerWithdrawals ?? 0, 2) }}</p>
            </div>
            <div class="rounded-xl border border-gray-800 p-3">
                <p class="text-[11px] text-gray-500">المتبقي من الرواتب</p>
                <p class="text-emerald-300 font-bold mt-1">{{ number_format($netMonthlySalaries ?? 0, 2) }}</p>
            </div>
        </div>
        <div id="salary-withdrawals-stores" class="p-5 space-y-3"></div>
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
    const credit   = @json($chartCredit);

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
            ...credit
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
        drawLine(credit, '#60a5fa');   // مديونيات
    }

    drawChart();
    window.addEventListener('resize', drawChart);
})();
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // تفاصيل كل بطاقة موزعة حسب المتجر، مرسلة من الكنترولر كـ JSON.
    const storeBreakdowns = @json($metricStoreBreakdowns ?? []);
    // تعريف العناوين والقيم والنص التوضيحي لكل بطاقة قابلة للنقر.
    const metricDefinitions = {
        profit_today: { title: 'صافي الربح اليوم', value: '{{ number_format($profitToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        sales_today: { title: 'مبيعات اليوم', value: '{{ number_format($salesToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        expenses_today: { title: 'مصروفات اليوم', value: '{{ number_format($expensesToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        products_cost_today: { title: 'تكلفة المنتجات المباعة اليوم', value: '{{ number_format($productsCostToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        profit_month: { title: 'صافي الربح الشهري', value: '{{ number_format($profitMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        sales_month: { title: 'مبيعات الشهر', value: '{{ number_format($salesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        expenses_month: { title: 'مصروفات الشهر', value: '{{ number_format($expensesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        salaries_month: { title: 'الرواتب الشهرية', value: '{{ number_format($monthlySalaries ?? 0, 2) }} ر.س', details: 'إجمالي الرواتب كاملة دون خصم السحوبات. تفاصيل السحوبات والمتبقي من الراتب تظهر في نافذة السحوبات المخصصة.' },
        monthly_purchases_consumption: { title: 'المشتريات والاستهلاك (شهري)', value: '{{ number_format($monthlyPurchasesAndConsumption, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
    };
    window.ownerDashboardMetricDefinitions = metricDefinitions;

    const modal = document.getElementById('metric-modal');
    const closeBtn = document.getElementById('metric-modal-close');
    const titleEl = document.getElementById('metric-modal-title');
    const valueEl = document.getElementById('metric-modal-value');
    const detailsEl = document.getElementById('metric-modal-details');

    // تعقيم النصوص القادمة من قاعدة البيانات قبل إدراجها داخل innerHTML.
    // يمنع تفسير اسم المتجر كوسوم HTML أو JavaScript غير موثوق.
    function escapeMetricHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

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
            const rows = storeBreakdowns.map((store) => {
                return `<li class="flex items-center justify-between border-b border-gray-800 py-2">
                    <span class="text-gray-200">${escapeMetricHtml(store.store_name)}</span>
                    <span>${formatByMetric(key, store[key])} <span class="text-gray-500 text-xs">ر.س</span></span>
                </li>`;
            }).join('');

            detailsEl.innerHTML = `
                <p class="mb-2">${data.details}</p>
                <p class="text-xs text-gray-400 mb-1">تفصيل حسب كل متجر:</p>
                <ul class="max-h-52 overflow-y-auto pr-1">${rows || '<li class="text-gray-500 py-2">لا توجد متاجر متاحة.</li>'}</ul>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    // بيانات الموظفين الجاهزة لحساب وعرض الراتب والسحب والمتبقي لكل متجر.
    const salaryRows = @json($employeeSalaryRemainders ?? []);
    const modal = document.getElementById('salary-withdrawals-modal');
    const openButton = document.getElementById('salary-after-withdrawals-card');
    const closeButton = document.getElementById('salary-withdrawals-close');
    const storesContainer = document.getElementById('salary-withdrawals-stores');

    // حماية النصوص القادمة من قاعدة البيانات قبل إدراجها داخل HTML ديناميكي.
    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    // توحيد عرض جميع مبالغ نافذة الرواتب إلى منزلتين عشريتين.
    function formatSalary(value) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    // تجميع الموظفين حسب المتجر وبناء جدول قابل للفتح لكل متجر.
    function renderSalaryStores() {
        if (!storesContainer) return;

        const groupedStores = salaryRows.reduce((stores, employee) => {
            const storeName = employee.store_name || 'متجر غير معروف';
            if (!stores[storeName]) stores[storeName] = [];
            stores[storeName].push(employee);
            return stores;
        }, {});

        const storeEntries = Object.entries(groupedStores);
        if (!storeEntries.length) {
            storesContainer.innerHTML = '<p class="text-sm text-gray-500 text-center py-6">لا توجد بيانات رواتب متاحة.</p>';
            return;
        }

        storesContainer.innerHTML = storeEntries.map(([storeName, employees], storeIndex) => {
            const salaryTotal = employees.reduce((total, employee) => total + Number(employee.salary || 0), 0);
            const withdrawalsTotal = employees.reduce((total, employee) => total + Number(employee.withdrawals_total || 0), 0);
            const remainingTotal = employees.reduce((total, employee) => total + Number(employee.salary_remaining || 0), 0);
            const rows = employees.map((employee) => `
                <tr class="border-b border-gray-800/70 last:border-0">
                    <td class="py-3 px-2 text-gray-200">${escapeHtml(employee.name)}</td>
                    <td class="py-3 px-2 text-indigo-300">${formatSalary(employee.salary)}</td>
                    <td class="py-3 px-2 text-red-300">${formatSalary(employee.withdrawals_total)}</td>
                    <td class="py-3 px-2 text-emerald-300 font-bold">${formatSalary(employee.salary_remaining)}</td>
                </tr>
            `).join('');

            return `
                <div class="rounded-xl border border-gray-800 overflow-hidden">
                    <button type="button"
                            class="salary-store-toggle w-full p-4 flex items-center justify-between gap-3 text-right hover:bg-white/5 transition"
                            data-target="salary-store-${storeIndex}">
                        <div>
                            <p class="text-sm font-bold text-white">${escapeHtml(storeName)}</p>
                            <p class="text-[11px] text-gray-500 mt-1">${employees.length} موظف — المتبقي ${formatSalary(remainingTotal)}</p>
                        </div>
                        <div class="flex items-center gap-3 text-[11px]">
                            <span class="text-indigo-300">الرواتب ${formatSalary(salaryTotal)}</span>
                            <span class="text-red-300">السحب ${formatSalary(withdrawalsTotal)}</span>
                            <i class="fa-solid fa-chevron-down text-gray-500"></i>
                        </div>
                    </button>
                    <div id="salary-store-${storeIndex}" class="hidden border-t border-gray-800 overflow-x-auto">
                        <table class="w-full min-w-[560px] text-xs text-right">
                            <thead class="bg-gray-950/60 text-gray-500">
                                <tr>
                                    <th class="py-2 px-2">الموظف</th>
                                    <th class="py-2 px-2">الراتب</th>
                                    <th class="py-2 px-2">إجمالي السحب</th>
                                    <th class="py-2 px-2">المتبقي</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            `;
        }).join('');
    }

    openButton?.addEventListener('click', function () {
        renderSalaryStores();
        modal?.classList.remove('hidden');
    });
    closeButton?.addEventListener('click', () => modal?.classList.add('hidden'));
    modal?.addEventListener('click', function (event) {
        if (event.target === modal) modal.classList.add('hidden');
    });
    storesContainer?.addEventListener('click', function (event) {
        const toggle = event.target.closest('.salary-store-toggle');
        if (!toggle) return;
        const details = document.getElementById(toggle.dataset.target);
        details?.classList.toggle('hidden');
        toggle.querySelector('.fa-chevron-down')?.classList.toggle('rotate-180');
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // مسار JSON الذي يحدّث بطاقات اليوم وآخر عملية دون إعادة تحميل الصفحة.
    const snapshotUrl = @json(route('user.dashboard.daily-snapshot'));
    // فلتر المتجر الحالي إن اختار المالك متجرًا محددًا للملخص اليومي.
    let summaryStoreId = @json(optional($selectedSummaryStore ?? null)->id);
    const storeFilter = document.getElementById('daily-summary-store-filter');
    const statusDot = document.getElementById('live-status-dot');
    // آخر معرف عُرض؛ يستخدم لتفعيل وميض البطاقة عند وصول عملية جديدة فقط.
    let latestOperationId = null;
    let consecutiveSnapshotFailures = 0;
    let snapshotTimer = null;

    // تنسيق الأرقام الحية مع منزلتين كحد أقصى.
    function formatNumber(value) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    }

    // تحديث قيمة بطاقة واحدة بواسطة معرف عنصر القيمة داخل المكوّن.
    function updateCardValue(valueId, value) {
        const valueElement = document.getElementById(valueId);
        if (valueElement) valueElement.textContent = formatNumber(value);
    }

    function updateConnectionStatus(isConnected, updatedAt = null) {
        const updatedElement = document.getElementById('live-updated-at');

        statusDot?.classList.toggle('bg-emerald-400', isConnected);
        statusDot?.classList.toggle('bg-orange-400', !isConnected);
        statusDot?.classList.toggle('animate-pulse', isConnected);

        if (!updatedElement) return;
        if (isConnected && updatedAt) {
            updatedElement.textContent = `آخر تحديث: ${updatedAt}`;
        } else if (!isConnected) {
            if (!updatedElement.textContent.startsWith('تعذر التحديث')) {
                updatedElement.textContent = `تعذر التحديث — ${updatedElement.textContent}`;
            }
        }
    }

    // جلب اللقطة اليومية وتحديث البطاقات والعداد وآخر عملية كل ثلاث ثوانٍ.
    async function refreshDailySnapshot() {
        if (document.hidden) return;

        try {
            const url = new URL(snapshotUrl, window.location.origin);
            if (summaryStoreId) url.searchParams.set('summary_store_id', summaryStoreId);
            url.searchParams.set('_', Date.now().toString());

            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!response.ok) {
                throw new Error(`Snapshot request failed: ${response.status}`);
            }

            const data = await response.json();
            consecutiveSnapshotFailures = 0;
            updateCardValue('daily-profit-value', data.profit_today);
            updateCardValue('daily-sales-value', data.sales_today);
            updateCardValue('daily-expenses-value', data.expenses_today);
            updateCardValue('daily-products-cost-value', data.products_cost_today);

            const metricDefinitions = window.ownerDashboardMetricDefinitions;
            if (metricDefinitions) {
                metricDefinitions.profit_today.value = `${formatNumber(data.profit_today)} ر.س`;
                metricDefinitions.sales_today.value = `${formatNumber(data.sales_today)} ر.س`;
                metricDefinitions.expenses_today.value = `${formatNumber(data.expenses_today)} ر.س`;
                metricDefinitions.products_cost_today.value = `${formatNumber(data.products_cost_today)} ر.س`;
            }

            const countElement = document.getElementById('live-operations-count');
            if (countElement) countElement.textContent = formatNumber(data.operations_count);

            const latestCard = document.getElementById('live-operation-card');
            const productElement = document.getElementById('live-operation-product');
            const storeElement = document.getElementById('live-operation-store');
            const amountElement = document.getElementById('live-operation-amount');
            const timeElement = document.getElementById('live-operation-time');
            if (data.latest_operation) {
                if (productElement) {
                    productElement.textContent = data.latest_operation.description;
                    productElement.classList.toggle('text-cyan-200', Boolean(data.latest_operation.is_tint));
                }
                if (storeElement) storeElement.textContent = data.latest_operation.store_name;
                if (amountElement) amountElement.textContent = formatNumber(data.latest_operation.amount);
                if (timeElement) timeElement.textContent = data.latest_operation.time || '--:--';
            } else {
                if (productElement) productElement.textContent = 'لا توجد عمليات بيع اليوم حتى الآن.';
                if (storeElement) storeElement.textContent = '—';
                if (amountElement) amountElement.textContent = '0.00';
                if (timeElement) timeElement.textContent = '--:--';
            }
            if (latestCard && data.latest_operation?.id && latestOperationId !== data.latest_operation.id) {
                latestCard.classList.add('border-cyan-500', 'bg-cyan-950/30');
                window.setTimeout(() => {
                    latestCard.classList.remove('border-cyan-500', 'bg-cyan-950/30');
                }, 1800);
                latestOperationId = data.latest_operation.id;
            }

            updateConnectionStatus(true, data.updated_at);
        } catch (error) {
            consecutiveSnapshotFailures++;
            if (consecutiveSnapshotFailures >= 2) {
                updateConnectionStatus(false);
            }
        }
    }

    storeFilter?.addEventListener('change', function () {
        summaryStoreId = this.value || null;
        latestOperationId = null;
        refreshDailySnapshot();
    });

    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            refreshDailySnapshot();
        }
    });

    refreshDailySnapshot();
    snapshotTimer = window.setInterval(refreshDailySnapshot, 5000);
    window.addEventListener('beforeunload', () => window.clearInterval(snapshotTimer));
});
</script>

@endsection
