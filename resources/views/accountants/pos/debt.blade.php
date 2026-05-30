@extends('dashboard.app')
@section('title', 'تسجيل مديونية')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-4 py-3 sm:py-4">

    {{-- رسائل النظام المتجاوبة --}}
    @if(session('success'))
        <div class="mb-2 sm:mb-3 p-2 sm:p-3 bg-green-500/10 border border-green-500/50 rounded-lg text-green-400 flex items-center gap-1 sm:gap-2 text-xs sm:text-sm">
            <span>✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- الهيدر المتجاوب --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 mb-3 sm:mb-4">
        <div class="flex items-center gap-1.5 sm:gap-2">
            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-br from-pink-600 to-rose-700 flex items-center justify-center shadow">
                <span class="text-white text-sm sm:text-base">💰</span>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-bold text-white">إدارة المديونيات</h1>
                <p class="text-slate-400 text-[10px] sm:text-xs">تسجيل وتحصيل مديونيات الموظفين</p>
            </div>
        </div>
        <a href="{{ route('accountant.dashboard') }}"
           class="inline-flex items-center gap-1 bg-slate-800 hover:bg-slate-700 text-slate-200 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg text-xs transition-all border border-slate-700 w-fit">
            <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            الرجوع
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-4">

        {{-- قسم اختيار الموظف --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl sm:rounded-2xl p-3 sm:p-4 backdrop-blur-sm">

                {{-- شريط البحث والعنوان --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-3 mb-2 sm:mb-3">
                    <h2 class="text-sm sm:text-base font-bold text-white flex items-center gap-1">
                        <span class="w-1.5 h-4 sm:w-2 sm:h-5 bg-pink-500 rounded-full"></span>
                        الموظفين
                    </h2>
                    <div class="relative w-full sm:w-48">
                        <input type="text" id="employeeSearch"
                               placeholder="بحث..."
                               class="w-full bg-slate-900/50 border border-slate-700 text-slate-300 text-xs sm:text-sm rounded-lg py-1.5 px-3 focus:ring-1 focus:ring-pink-500 outline-none">
                    </div>
                </div>

                {{-- كروت الموظفين --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 sm:gap-2 max-h-[500px] overflow-y-auto custom-scrollbar pr-1" id="employeesList">
                    @foreach($people as $emp)
                    @php
                        $hasDebt = $emp->debts()->where('amount', '>', 0)->exists();
                        $totalDebt = $emp->debts()->where('amount', '>', 0)->sum('amount');
                    @endphp
                    <div class="employee-card bg-slate-900/40 border border-slate-700/50 rounded-lg sm:rounded-xl p-2 hover:border-pink-500/50 transition-all"
                         data-name="{{ $emp->name }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0 flex-1">
                                <div class="relative flex-shrink-0">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full {{ $emp->role === 'accountant' ? 'bg-blue-900/40 border border-blue-700/30' : 'bg-slate-800 border border-slate-700' }} flex items-center justify-center text-white font-bold text-xs sm:text-sm">
                                        {{ mb_substr($emp->name, 0, 1) }}
                                    </div>
                                    @if($hasDebt)
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-pink-600 rounded-full flex items-center justify-center border border-slate-900">
                                            <span class="text-[7px] text-white">$</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-white text-xs sm:text-sm font-medium truncate">{{ $emp->name }}</h3>
                                    <div class="flex items-center gap-1 mt-0.5">
                                        <span class="text-[8px] sm:text-[10px] px-1 py-0.5 rounded-md {{ $emp->role === 'accountant' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-800 text-slate-500' }}">
                                            {{ $emp->role === 'accountant' ? 'محاسب' : 'موظف' }}
                                        </span>
                                        @if($hasDebt)
                                            <span class="text-[8px] sm:text-[10px] px-1 py-0.5 rounded-md bg-pink-500/10 text-pink-400">
                                                {{ number_format($totalDebt, 0) }}ر.س
                                            </span>
                                        @else
                                            <span class="text-[8px] sm:text-[10px] px-1 py-0.5 rounded-md bg-green-500/10 text-green-400">
                                                سليم
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button onclick="openDebtModal({{ $emp->id }}, '{{ $emp->name }}', {{ $hasDebt ? 'true' : 'false' }})"
                                    class="bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg text-[10px] sm:text-xs font-medium transition-all shadow flex-shrink-0">
                                {{ $hasDebt ? 'إدارة' : 'إضافة' }}
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- قسم السجل الجانبي --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl sm:rounded-2xl p-3 sm:p-4 sticky top-3 shadow-xl">
                <h2 class="text-sm sm:text-base font-bold text-white mb-2 sm:mb-3 flex items-center gap-1">
                    <span class="text-yellow-500 text-base sm:text-lg">🕒</span>
                    آخر العمليات
                </h2>

                <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                    @forelse($lastDebts as $op)
                    <div class="group relative pr-2 sm:pr-3 border-r-2 {{ $op->amount > 0 ? 'border-pink-800 hover:border-pink-500' : 'border-blue-800 hover:border-blue-500' }} transition-colors">
                        <div class="flex justify-between items-start">
                            <h4 class="text-slate-200 text-xs sm:text-sm font-medium">{{ $op->person->name ?? '—' }}</h4>
                            <span class="{{ $op->amount > 0 ? 'text-pink-400' : 'text-blue-400' }} font-bold text-xs sm:text-sm">
                                {{ number_format(abs($op->amount), 0) }} ﷼
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-slate-500 text-[9px] sm:text-[10px]">{{ $op->date }}</p>
                            <span class="text-[8px] sm:text-[9px] px-1 py-0.5 rounded {{ $op->amount > 0 ? 'bg-pink-500/10 text-pink-400' : 'bg-blue-500/10 text-blue-400' }}">
                                {{ $op->amount > 0 ? 'إضافة' : 'تحصيل' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6 opacity-40">
                        <p class="text-slate-400 text-xs sm:text-sm">لا توجد عمليات</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- مودال المديونية الرئيسي المدمج --}}
{{-- ============================= --}}
<div id="debtModal"
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-3 transition-all duration-300">

    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         onclick="closeDebtModal()"></div>

    <div class="relative bg-slate-900 border border-slate-800 rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-full sm:max-w-md
                max-h-[90vh] overflow-y-auto custom-scrollbar transform transition-all mx-2 sm:mx-0">

        {{-- رأس المودال المدمج --}}
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-3 sm:px-4 py-2.5 sm:py-3 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="w-1.5 h-4 sm:w-2 sm:h-5 bg-pink-600 rounded-full"></span>
                <h3 class="text-sm sm:text-base font-bold text-white" id="modalTitle">إضافة مديونية</h3>
                <span class="text-slate-400 text-[10px] sm:text-xs mr-1 truncate max-w-[150px]" id="empNameDisplay"></span>
            </div>
            <button onclick="closeDebtModal()"
                    class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- محتوى الفورم --}}
        <form id="debtForm" method="POST" class="p-3 sm:p-4 space-y-2.5 sm:space-y-3">
            @csrf

            {{-- حقل المبلغ --}}
            <div class="space-y-1 sm:space-y-1.5">
                <label class="block text-xs sm:text-sm font-medium text-slate-300">المبلغ</label>
                <div class="relative">
                    <input type="number" name="amount" step="0.01" min="0.1" required
                           class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg sm:rounded-xl p-2.5 sm:p-3 pl-8 sm:pl-10
                                  focus:ring-1 focus:ring-pink-600 outline-none transition-all text-base sm:text-lg font-bold"
                           placeholder="0.00">
                    <span class="absolute left-3 sm:left-4 top-1/2 -translate-y-1/2 text-slate-500 text-xs sm:text-sm">﷼</span>
                </div>
            </div>

            {{-- حقل التاريخ --}}
            <div class="space-y-1 sm:space-y-1.5">
                <label class="block text-xs sm:text-sm font-medium text-slate-300">التاريخ</label>
                <input type="date" name="date"
                       value="{{ date('Y-m-d') }}"
                       required
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg sm:rounded-xl p-2.5 sm:p-3
                              focus:ring-1 focus:ring-pink-600 outline-none transition-all text-sm sm:text-base"
                       style="min-height: 44px; direction: ltr;">
            </div>

            {{-- حقل الوصف --}}
            <div class="space-y-1 sm:space-y-1.5">
                <label class="block text-xs sm:text-sm font-medium text-slate-300">الوصف (اختياري)</label>
                <textarea name="description" rows="2"
                          class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg sm:rounded-xl p-2.5 sm:p-3
                                 focus:ring-1 focus:ring-pink-600 outline-none transition-all resize-none text-sm sm:text-base"
                          placeholder="تفاصيل إضافية..."></textarea>
            </div>

            {{-- أزرار متعددة - تتغير حسب حالة الموظف --}}
            <div id="debtActions" class="hidden space-y-2">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white font-medium py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base transition-all flex items-center justify-center gap-1 sm:gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة مديونية
                </button>

                <button type="button" onclick="openCollectModal()"
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-medium py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base transition-all flex items-center justify-center gap-1 sm:gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    تحصيل
                </button>
            </div>

            {{-- أزرار الإضافة فقط --}}
            <div id="addOnly" class="hidden">
                <button type="submit"
                        class="w-full bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white font-medium py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base transition-all flex items-center justify-center gap-1 sm:gap-2">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ المديونية
                </button>
            </div>

            {{-- زر الإلغاء --}}
            <button type="button" onclick="closeDebtModal()"
                    class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base transition-all border border-slate-700/50">
                إلغاء
            </button>
        </form>
    </div>
</div>

{{-- ============================= --}}
{{-- مودال التحصيل المدمج --}}
{{-- ============================= --}}
<div id="collectModal"
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-2 sm:p-3 transition-all duration-300">

    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity"
         onclick="closeCollectModal()"></div>

    <div class="relative bg-slate-900 border border-slate-800 rounded-xl sm:rounded-2xl shadow-2xl w-full max-w-full sm:max-w-lg
                max-h-[90vh] overflow-y-auto custom-scrollbar transform transition-all mx-2 sm:mx-0">

        {{-- رأس المودال --}}
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-3 sm:px-4 py-2.5 sm:py-3 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-1.5 sm:gap-2">
                <span class="w-1.5 h-4 sm:w-2 sm:h-5 bg-blue-600 rounded-full"></span>
                <h3 class="text-sm sm:text-base font-bold text-white">تحصيل المديونيات</h3>
                <span class="text-slate-400 text-[10px] sm:text-xs mr-1 truncate max-w-[150px]" id="collectEmpName"></span>
            </div>
            <button onclick="closeCollectModal()"
                    class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center flex-shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- قائمة المديونيات --}}
        <div id="debtsList" class="p-3 sm:p-4 space-y-2 sm:space-y-3">
            <div class="flex items-center justify-center py-6">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
                <span class="mr-2 text-slate-400 text-xs sm:text-sm">جاري التحميل...</span>
            </div>
        </div>

        <div class="p-3 sm:p-4 pt-0">
            <button type="button" onclick="closeCollectModal()"
                    class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2.5 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base transition-all border border-slate-700/50">
                إغلاق
            </button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }

    /* تحسينات للجوال */
    @media (max-width: 640px) {
        input, select, textarea, button {
            font-size: 16px !important; /* منع التكبير التلقائي في iOS */
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 2px;
        }
    }
</style>

<script>
let currentEmpId = null;
let currentEmpName = '';

document.getElementById('employeeSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.employee-card');
    cards.forEach(card => {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(term) ? 'block' : 'none';
    });
});

function openDebtModal(empId, empName, hasDebt) {
    currentEmpId = empId;
    currentEmpName = empName;

    document.getElementById('empNameDisplay').textContent = empName;
    const routeTemplate = "{{ route('accountant.pos.debt.store', ['employee' => 'ID']) }}";
    document.getElementById('debtForm').action = routeTemplate.replace('ID', empId);

    if (hasDebt) {
        document.getElementById('debtActions').classList.remove('hidden');
        document.getElementById('addOnly').classList.add('hidden');
        document.getElementById('modalTitle').textContent = 'إدارة المديونية';
    } else {
        document.getElementById('addOnly').classList.remove('hidden');
        document.getElementById('debtActions').classList.add('hidden');
        document.getElementById('modalTitle').textContent = 'إضافة مديونية';
    }

    const modal = document.getElementById('debtModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDebtModal() {
    const modal = document.getElementById('debtModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('debtForm').reset();
}

function openCollectModal() {
    closeDebtModal();

    document.getElementById('collectModal').classList.remove('hidden');
    document.getElementById('collectEmpName').textContent = currentEmpName;
    document.body.style.overflow = 'hidden';

    const url = "{{ route('accountant.debts.list', ['id' => 'EMP_ID']) }}".replace('EMP_ID', currentEmpId);

    fetch(url)
        .then(res => res.json())
        .then(data => {
            let html = '';

            if (data.length === 0) {
                html = `
                    <div class="text-center py-6">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-slate-800 flex items-center justify-center">
                            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="text-slate-400 text-xs sm:text-sm">لا توجد مديونيات</p>
                    </div>
                `;
            } else {
                data.forEach(d => {
                    const amount = parseFloat(d.amount).toFixed(2);
                    html += `
                        <div class="bg-slate-800/40 border border-slate-700/50 rounded-lg sm:rounded-xl p-2 sm:p-3">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-blue-900/20 border border-blue-700/30 flex items-center justify-center flex-shrink-0">
                                            <span class="text-blue-400 font-bold text-xs sm:text-sm">${amount.split('.')[0]}</span>
                                        </div>
                                        <div>
                                            <div class="text-white font-bold text-sm sm:text-base">${amount} ﷼</div>
                                            <div class="text-slate-400 text-[10px] sm:text-xs">${d.date}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex gap-1 sm:gap-2">
                                    <button onclick="collectFull(${d.id})"
                                            class="flex-1 sm:flex-none bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white px-2 sm:px-3 py-1.5 rounded-lg text-[10px] sm:text-xs font-medium">
                                        كامل
                                    </button>
                                    <button onclick="togglePartial(${d.id})"
                                            class="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white px-2 sm:px-3 py-1.5 rounded-lg text-[10px] sm:text-xs font-medium">
                                        جزئي
                                    </button>
                                </div>
                            </div>

                            <div id="partial-${d.id}" class="hidden mt-2 bg-slate-800/60 rounded-lg p-2">
                                <div class="mb-2">
                                    <input type="number" id="partialAmount-${d.id}"
                                           placeholder="المبلغ"
                                           class="w-full bg-slate-900 border border-slate-700 text-white rounded-lg p-2 text-sm">
                                </div>
                                <button onclick="collectPartial(${d.id})"
                                        class="w-full bg-gradient-to-r from-yellow-600 to-amber-600 hover:from-yellow-500 hover:to-amber-500 text-white py-1.5 rounded-lg text-xs font-medium">
                                    تأكيد
                                </button>
                            </div>
                        </div>
                    `;
                });
            }

            document.getElementById('debtsList').innerHTML = html;
        })
        .catch(() => {
            document.getElementById('debtsList').innerHTML = `
                <div class="bg-red-900/20 border border-red-700/50 rounded-lg p-3 text-center">
                    <p class="text-red-400 text-xs sm:text-sm">حدث خطأ في التحميل</p>
                </div>
            `;
        });
}

function togglePartial(id) {
    const element = document.getElementById(`partial-${id}`);
    element.classList.toggle('hidden');
}

function collectFull(id) {
    if (confirm('تأكيد تحصيل المديونية كاملة؟')) {
        window.location.href = "{{ url('accountant/debt/collect/full') }}/" + id;
    }
}

function collectPartial(id) {
    const amount = document.getElementById(`partialAmount-${id}`).value;
    if (!amount || amount <= 0) {
        alert('أدخل مبلغ صحيح');
        return;
    }
    if (confirm(`تأكيد تحصيل ${amount} ريال؟`)) {
        window.location.href = "{{ url('accountant/debt/collect/partial') }}/" + id + "/" + amount;
    }
}

function closeCollectModal() {
    const modal = document.getElementById('collectModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.getElementById('debtForm').addEventListener('submit', function() {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = 'جاري الحفظ...';
});
</script>
@endsection