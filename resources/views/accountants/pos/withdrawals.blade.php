@extends('dashboard.app')
@section('title', 'إضافة سحب نقدي')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-4 sm:py-6"> {{-- تقليل المسافات العلوية --}}
    
    {{-- رسائل النظام --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-500/10 border border-green-500/50 rounded-xl text-green-400 flex items-center gap-2 text-sm">
            <span>✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- الهيدر المصغر --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center shadow-lg shadow-blue-500/20">
                <span class="text-white text-lg font-bold">💰</span>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white">سحب نقدي</h1>
                <p class="text-slate-400 text-xs">إدارة سلفيات ومسحوبات الموظفين</p>
            </div>
        </div>
        <a href="{{ route('accountant.dashboard') }}"
           class="inline-flex items-center gap-1 bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-xl text-sm transition-all w-fit border border-slate-700">
            <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            الرجوع
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4"> {{-- تقليل المسافات بين الأعمدة --}}
        
        {{-- قسم اختيار الموظف --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-4 backdrop-blur-sm"> {{-- تقليل padding --}}
                
                {{-- شريط البحث والعنوان --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-3">
                    <h2 class="text-base font-bold text-white flex items-center gap-1">
                        <span class="w-1.5 h-4 bg-blue-500 rounded-full"></span>
                        قائمة الموظفين
                    </h2>
                    <div class="relative w-full sm:w-56">
                        <input type="text" id="employeeSearch" 
                               placeholder="بحث..." 
                               class="w-full bg-slate-900/50 border border-slate-700 text-slate-300 text-sm rounded-lg py-1.5 px-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="employeesList"> {{-- تقليل المسافات بين الكروت --}}
                    @foreach($people as $emp)
                    <div class="employee-card bg-slate-900/40 border border-slate-700/50 rounded-xl p-3 hover:border-blue-500/50 transition-all duration-300 group" 
                         data-name="{{ $emp->name }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-blue-400 font-bold text-sm shadow-inner flex-shrink-0">
                                    {{ mb_substr($emp->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-white text-sm font-medium truncate group-hover:text-blue-400 transition-colors">{{ $emp->name }}</h3>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded-md {{ $emp->role === 'accountant' ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'bg-slate-800 text-slate-500' }}">
                                        {{ $emp->role === 'accountant' ? 'محاسب' : 'موظف' }}
                                    </span>
                                </div>
                            </div>
                            <button onclick="openWithdrawalModal({{ $emp->id }}, '{{ $emp->name }}')"
                                    class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-all transform active:scale-95 shadow-lg shadow-blue-600/10 flex-shrink-0">
                                سحب
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- قسم السجل الجانبي --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 sticky top-4 shadow-xl"> {{-- تقليل padding --}}
                <h2 class="text-base font-bold text-white mb-3 flex items-center gap-1">
                    <span class="text-yellow-500 text-lg">🕒</span>
                    آخر العمليات
                </h2>

                <div class="space-y-3 max-h-[500px] overflow-y-auto custom-scrollbar pr-1"> {{-- تقليل المسافات --}}
                    @forelse($lastWithdrawals as $w)
                    <div class="group relative pr-3 border-r-2 border-slate-800 hover:border-blue-500 transition-colors">
                        <div class="flex justify-between items-start">
                            <h4 class="text-slate-200 text-xs font-medium group-hover:text-white transition-colors">{{ $w->person->name ?? '—' }}</h4>
                            <span class="text-green-400 font-bold text-xs">{{ number_format($w->amount) }} ﷼</span>
                        </div>
                        <p class="text-slate-500 text-[10px] mt-0.5">{{ $w->date }}</p>
                        @if(!empty($w->description))
                            <p class="text-slate-400 text-[10px] bg-slate-800/40 p-1.5 rounded-lg mt-1.5 italic border border-slate-700/30">
                                {{ Str::limit($w->description, 40) }}
                            </p>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-6 opacity-40">
                        <p class="text-slate-400 text-xs italic">لا توجد سجلات</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- المودال المصغر والمضبوط --}}
<div id="withdrawalModal" 
     class="fixed inset-0 z-[100] hidden items-center justify-center p-3 transition-all duration-300"
     style="display: none;">
    
    {{-- الخلفية --}}
    <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm transition-opacity" 
         onclick="closeWithdrawalModal()"></div>
    
    {{-- جسم المودال --}}
    <div class="relative bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl w-full max-w-md 
                max-h-[85vh] overflow-y-auto custom-scrollbar transform transition-all">
        
        {{-- رأس المودال المصغر --}}
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-4 py-3 border-b border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-white flex items-center gap-1">
                    <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                    تسجيل سحب
                </h3>
                <p class="text-slate-400 text-[10px] mt-0.5" id="empNameDisplay"></p>
            </div>
            <button onclick="closeWithdrawalModal()" 
                    class="w-7 h-7 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- محتوى الفورم المصغر --}}
        <form id="withdrawalForm" method="POST" class="p-4 space-y-3"> {{-- تقليل المسافات بشكل كبير --}}
            @csrf
            <input type="hidden" name="employee_id" id="employeeId">
            
            {{-- حقل المبلغ --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">المبلغ</label>
                <div class="relative">
                    <input type="number" name="amount" step="0.01" min="0.1" required
                           class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl p-2.5 pl-8 
                                  focus:ring-1 focus:ring-blue-600 focus:border-transparent outline-none 
                                  transition-all text-base font-bold"
                           placeholder="0.00">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs font-bold">﷼</span>
                </div>
            </div>

            {{-- حقل التاريخ المحسّن --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">التاريخ</label>
                <input type="date" name="date" 
                       value="{{ date('Y-m-d') }}" 
                       max="{{ date('Y-m-d') }}"
                       required
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl p-2.5
                              focus:ring-1 focus:ring-blue-600 focus:border-transparent outline-none 
                              transition-all text-sm"
                       style="direction: ltr;">
            </div>

            {{-- حقل الملاحظات --}}
            <div class="space-y-1">
                <label class="block text-xs font-medium text-slate-300">ملاحظات (اختياري)</label>
                <textarea name="description" rows="2"
                          class="w-full bg-slate-950 border border-slate-800 text-white rounded-xl p-2.5 
                                 focus:ring-1 focus:ring-blue-600 outline-none transition-all resize-none text-sm"
                          placeholder="تفاصيل إضافية..."></textarea>
            </div>

            {{-- الأزرار المصغرة --}}
            <div class="flex gap-2 pt-2">
                <button type="submit" 
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 rounded-xl 
                               text-sm transition-all transform active:scale-[0.98] 
                               flex items-center justify-center gap-1 disabled:opacity-50">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ
                </button>
                <button type="button" onclick="closeWithdrawalModal()" 
                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium py-2.5 rounded-xl 
                               text-sm transition-all border border-slate-700/50">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* تحسين شكل السكرول بار */
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
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

function openWithdrawalModal(empId, empName) {
    document.getElementById('empNameDisplay').textContent = empName;
    document.getElementById('employeeId').value = empId;
    const routeTemplate = "{{ route('accountant.pos.withdrawal.store', ['employee' => 'ID']) }}";
    document.getElementById('withdrawalForm').action = routeTemplate.replace('ID', empId);
    
    const modal = document.getElementById('withdrawalModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeWithdrawalModal() {
    const modal = document.getElementById('withdrawalModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    document.getElementById('withdrawalForm').reset();
}

// منع الإرسال المتكرر
document.getElementById('withdrawalForm').addEventListener('submit', function(e) {
    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = 'جاري الحفظ...';
});
</script>
@endsection