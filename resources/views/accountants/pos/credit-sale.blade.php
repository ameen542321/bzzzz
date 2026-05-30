@extends('dashboard.app')
@section('title', 'تسجيل بيع آجل')

@section('content')
<div class="max-w-7xl mx-auto px-3 py-3" dir="rtl"> {{-- تصغير المسافات الخارجية --}}

    {{-- رسائل النظام المصغرة --}}
    @if(session('success'))
        <div class="mb-2 p-2 bg-green-500/10 border border-green-500/50 rounded-lg text-green-400 flex items-center gap-1 text-xs">
            <span>✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- الهيدر المدمج --}}
    <div class="flex items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-1.5">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center shadow">
                <span class="text-white text-sm">💳</span>
            </div>
            <div>
                <h1 class="text-base font-bold text-white">تسجيل بيع آجل</h1>
                <p class="text-slate-400 text-[10px]">إدارة مبيعات الآجل</p>
            </div>
        </div>
        <a href="{{ route('accountant.dashboard') }}"
           class="inline-flex items-center gap-1 bg-slate-800 hover:bg-slate-700 text-slate-200 px-2.5 py-1.5 rounded-lg text-xs transition-all border border-slate-700">
            <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            الرجوع
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3"> {{-- تصغير المسافات بين الأعمدة --}}

        {{-- قسم اختيار الموظف المدمج --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-3 backdrop-blur-sm shadow">

                {{-- شريط البحث والعنوان المدمج --}}
                <div class="flex items-center justify-between gap-2 mb-2">
                    <h2 class="text-sm font-bold text-white flex items-center gap-1">
                        <span class="w-1.5 h-4 bg-indigo-500 rounded-full"></span>
                        الموظفين
                    </h2>
                    <div class="relative w-48">
                        <input type="text" id="employeeSearch"
                               placeholder="بحث..."
                               class="w-full bg-slate-900/50 border border-slate-700 text-slate-300 text-xs rounded-lg py-1.5 px-3 focus:ring-1 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                {{-- كروت الموظفين المدمجة --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-[500px] overflow-y-auto custom-scrollbar pr-1" id="employeesList">
                    @foreach($people as $emp)
                    <div class="employee-card bg-slate-900/40 border border-slate-700/50 rounded-lg p-2 hover:border-indigo-500/50 transition-all"
                         data-name="{{ $emp->name }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <div class="w-7 h-7 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-indigo-400 font-bold text-xs flex-shrink-0">
                                    {{ mb_substr($emp->name, 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-white text-xs font-medium truncate">{{ $emp->name }}</h3>
                                    <span class="text-[8px] px-1 py-0.5 rounded-md bg-slate-800 text-slate-500">
                                        {{ $emp->role === 'accountant' ? 'محاسب' : 'موظف' }}
                                    </span>
                                </div>
                            </div>
                            <button onclick="openCreditSaleModal({{ $emp->id }}, '{{ $emp->name }}')"
                                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-2 py-1 rounded-lg text-[10px] font-medium transition-all shadow flex-shrink-0">
                                بيع آجل
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- قسم السجل الجانبي المدمج --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 sticky top-3 shadow-2xl">
                <h2 class="text-sm font-bold text-white mb-2 flex items-center gap-1">
                    <span class="text-indigo-500">🕒</span>
                    آخر العمليات
                </h2>

                <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                    @forelse($lastCreditSales as $sale)
                    <div class="group relative pr-2 border-r-2 border-slate-800 hover:border-indigo-500 transition-colors">
                        <div class="flex justify-between items-start">
                            <h4 class="text-slate-200 text-xs font-medium">{{ $sale->person->name ?? '—' }}</h4>
                            <span class="text-emerald-400 font-bold text-xs">{{ number_format($sale->amount, 2) }}</span>
                        </div>
                        <p class="text-slate-500 text-[9px] mt-0.5">{{ $sale->date }}</p>
                    </div>
                    @empty
                    <div class="text-center py-4 opacity-40">
                        <p class="text-slate-400 text-xs">لا توجد سجلات</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- المودال المدمج والاحترافي --}}
<div id="creditSaleModal"
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-3 transition-all duration-300">

    {{-- خلفية داكنة --}}
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         onclick="closeCreditSaleModal()"></div>

    {{-- محتوى المودال --}}
    <div class="relative bg-slate-900 border border-slate-800 rounded-xl shadow-2xl w-full max-w-md
                max-h-[85vh] overflow-y-auto custom-scrollbar transform transition-all">

        {{-- رأس المودال المدمج --}}
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-3 py-2.5 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-4 bg-indigo-600 rounded-full"></span>
                <h3 class="text-sm font-bold text-white">بيع آجل</h3>
                <span class="text-slate-400 text-[10px] mr-1" id="empNameDisplay"></span>
            </div>
            <button onclick="closeCreditSaleModal()"
                    class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- محتوى الفورم المدمج --}}
        <form id="creditSaleForm" method="POST" class="p-3 space-y-2.5">
            @csrf

            {{-- المبلغ --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">المبلغ</label>
                <div class="relative">
                    <input type="number" name="amount" step="0.01" min="0.01" required
                           class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg p-2.5 pl-8
                                  focus:ring-1 focus:ring-indigo-600 outline-none transition-all text-base font-bold"
                           placeholder="0.00">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs">ر.س</span>
                </div>
            </div>

            {{-- التاريخ --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">التاريخ</label>
                <input type="date" name="date"
                       value="{{ date('Y-m-d') }}"
                       max="{{ date('Y-m-d') }}"
                       required
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg p-2.5
                              focus:ring-1 focus:ring-indigo-600 outline-none transition-all text-sm"
                       style="direction: ltr;">
            </div>

            {{-- الوصف --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">ملاحظات (اختياري)</label>
                <textarea name="description" rows="2"
                          class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg p-2.5
                                 focus:ring-1 focus:ring-indigo-600 outline-none transition-all resize-none text-sm"
                          placeholder="تفاصيل إضافية..."></textarea>
            </div>

            {{-- الأزرار --}}
            <div class="flex gap-2 pt-2">
                <button type="submit" id="submitBtn"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2 rounded-lg
                               text-sm transition-all transform active:scale-[0.98] flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ
                </button>
                <button type="button" onclick="closeCreditSaleModal()"
                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2 rounded-lg
                               text-sm transition-all border border-slate-700/50">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }

    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(0.8);
        cursor: pointer;
    }
</style>

<script>
// ميزة البحث
document.getElementById('employeeSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.employee-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(term) ? 'block' : 'none';
    });
});

function openCreditSaleModal(empId, empName) {
    document.getElementById('empNameDisplay').textContent = empName;
    const routeTemplate = "{{ route('accountant.pos.credit-sale.store', ['employee' => 'ID']) }}";
    document.getElementById('creditSaleForm').action = routeTemplate.replace('ID', empId);

    const modal = document.getElementById('creditSaleModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCreditSaleModal() {
    const modal = document.getElementById('creditSaleModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('creditSaleForm').reset();
}

document.getElementById('creditSaleForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = 'جاري الحفظ...';
});
</script>
@endsection