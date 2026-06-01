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
    <p class="text-xs font-semibold text-gray-400 mt-1 mb-2">الملخص اليومي</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- صافي الربح اليوم --}}
        <button type="button" class="text-right metric-card" data-metric="profit_today" title="للمزيد من التفاصيل اضغط: صافي الربح اليومي حسب كل متجر">
            <x-stat-card title="صافي الربح اليوم"
                value="{{ number_format($profitToday) }}"
                color="{{ $profitToday >= 0 ? 'emerald' : 'red' }}" />
        </button>

        {{-- [تعديل آمن] مبيعات اليوم محسوبة من المحصّل الفعلي --}}
        <button type="button" class="text-right metric-card" data-metric="sales_today" title="للمزيد من التفاصيل اضغط: مبيعات اليوم حسب كل متجر">
            <x-stat-card title="مبيعات اليوم" value="{{ number_format($salesToday) }}" color="emerald" />
        </button>

        {{-- مصروفات اليوم --}}
        <button type="button" class="text-right metric-card" data-metric="expenses_today" title="للمزيد من التفاصيل اضغط: مصروفات اليوم حسب كل متجر">
            <x-stat-card title="مصروفات اليوم" value="{{ number_format($expensesToday) }}" color="red" />
        </button>

        <button type="button" class="text-right metric-card" data-metric="products_cost_today" title="للمزيد من التفاصيل اضغط: تكلفة المنتجات المباعة اليوم حسب كل متجر">
            <x-stat-card title="تكلفة المنتجات المباعة اليوم" value="{{ number_format($productsCostToday, 2) }}" color="yellow" />
        </button>

    </div>

    {{-- الصف الثاني --}}
    <p class="text-xs font-semibold text-gray-400 mt-5 mb-2">الملخص الشهري</p>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        <button type="button" class="text-right metric-card" data-metric="profit_month" title="للمزيد من التفاصيل اضغط: صافي الربح الشهري حسب كل متجر">
            <x-stat-card title="صافي الربح الشهري (مطابق للتقرير)"
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
            <x-stat-card title="صافي الرواتب (شهري)" value="{{ number_format($netMonthlySalaries ?? 0) }}" color="indigo" />
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
            <span class="inline-flex items-center gap-2 text-blue-300"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span>مديونيات</span>
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
    const storeBreakdowns = @json($metricStoreBreakdowns ?? []);
    const metricDefinitions = {
        profit_today: { title: 'صافي الربح اليوم', value: '{{ number_format($profitToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        sales_today: { title: 'مبيعات اليوم', value: '{{ number_format($salesToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        expenses_today: { title: 'مصروفات اليوم', value: '{{ number_format($expensesToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        products_cost_today: { title: 'تكلفة المنتجات المباعة اليوم', value: '{{ number_format($productsCostToday, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        profit_month: { title: 'صافي الربح الشهري', value: '{{ number_format($profitMonth, 2) }} ر.س', details: 'نفس معادلة التقرير الشهري: المحصل - تكلفة المنتجات المباعة - المصروفات - المشتريات والاستهلاك. الرواتب تظهر كمؤشر مستقل ولا تخصم من الربح.' },
        sales_month: { title: 'مبيعات الشهر', value: '{{ number_format($salesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        expenses_month: { title: 'مصروفات الشهر', value: '{{ number_format($expensesMonth, 2) }} ر.س', details: 'تفصيل القيمة حسب المتاجر.' },
        salaries_month: { title: 'صافي الرواتب (شهري)', value: '{{ number_format($netMonthlySalaries ?? 0, 2) }} ر.س', details: 'بعد خصم سحوبات العمال. إجمالي الرواتب: {{ number_format($monthlySalaries ?? 0, 2) }} ر.س - السحوبات: {{ number_format($monthlyWorkerWithdrawals ?? 0, 2) }} ر.س.' },
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
            const rows = storeBreakdowns.map((store) => {
                return `<li class="flex items-center justify-between border-b border-gray-800 py-2">
                    <span class="text-gray-200">${store.store_name}</span>
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

@endsection
