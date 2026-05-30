@extends('dashboard.app')
@section('title', 'تسجيل غياب')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6 sm:py-8" dir="rtl">
    
    {{-- رسائل النظام --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/50 rounded-xl text-green-400 flex items-center gap-3 animate-bounce">
            <span>✅</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- الهيدر --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-red-600 to-rose-700 flex items-center justify-center shadow-lg shadow-red-500/20">
                <span class="text-white text-xl font-bold">📅</span>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">تسجيل غياب</h1>
                <p class="text-slate-400 text-sm mt-1">إدارة سجلات  غياب طاقم العمل</p>
            </div>
        </div>
        <a href="{{ route('accountant.dashboard') }}"
           class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2.5 rounded-xl transition-all group w-fit border border-slate-700">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            الرجوع للرئيسية
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- قسم اختيار الموظف --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-3xl p-5 sm:p-7 backdrop-blur-sm">
                
                {{-- شريط البحث --}}
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-5 bg-red-500 rounded-full"></span>
                        قائمة الموظفين
                    </h2>
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="employeeSearch" 
                               placeholder="بحث عن موظف..." 
                               class="w-full bg-slate-900/50 border border-slate-700 text-slate-300 text-sm rounded-xl py-2 px-4 focus:ring-2 focus:ring-red-500 focus:border-transparent outline-none transition-all">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="employeesList">
                    @foreach($people as $emp)
                    <div class="employee-card bg-slate-900/40 border border-slate-700/50 rounded-2xl p-4 hover:border-red-500/50 transition-all duration-300 group" 
                         data-name="{{ $emp->name }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-red-400 font-bold shadow-inner">
                                    {{ mb_substr($emp->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-white font-semibold truncate group-hover:text-red-400 transition-colors">{{ $emp->name }}</h3>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md {{ $emp->role === 'accountant' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-800 text-slate-500' }}">
                                        {{ $emp->role === 'accountant' ? 'محاسب' : 'موظف' }}
                                    </span>
                                </div>
                            </div>
                            <button onclick="openAbsenceModal({{ $emp->id }}, '{{ $emp->name }}')"
                                    class="bg-red-600 hover:bg-red-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all transform active:scale-95 shadow-lg shadow-red-600/10">
                                تسجيل غياب
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- قسم السجل الجانبي --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 border border-slate-800 rounded-3xl p-6 sticky top-6 shadow-xl">
                <h2 class="text-lg font-bold text-white mb-5 flex items-center gap-2">
                    <span class="text-red-500 text-xl">🕒</span>
                    آخر تسجيلات الغياب
                </h2>

                <div class="space-y-4 max-h-[600px] overflow-y-auto custom-scrollbar">
                    @forelse($lastAbsences as $a)
                    <div class="group relative pr-4 border-r-2 border-slate-800 hover:border-red-500 transition-colors">
                        <div class="flex justify-between items-start">
                            <h4 class="text-slate-200 font-medium text-sm group-hover:text-white transition-colors">{{ $a->person->name ?? '—' }}</h4>
                            <span class="text-red-400 font-bold text-[10px] bg-red-400/10 px-2 py-0.5 rounded">غائب</span>
                        </div>
                        <p class="text-slate-500 text-[11px] mt-1">{{ $a->date }}</p>
                        @if(!empty($a->description))
                            <p class="text-slate-400 text-[11px] bg-slate-800/40 p-2 rounded-lg mt-2 italic border border-slate-700/30">
                                {{ Str::limit($a->description, 50) }}
                            </p>
                        @endif
                    </div>
                    @empty
                    <div class="text-center py-10 opacity-40">
                        <p class="text-slate-400 text-sm italic">لا توجد سجلات غياب</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- المودال المحسّن للغياب --}}
<div id="absenceModal" 
     class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 transition-all duration-300">
    
    <div class="fixed inset-0 bg-slate-950/90 backdrop-blur-md transition-opacity" 
         onclick="closeAbsenceModal()"></div>
    
    <div class="relative bg-slate-900 border border-slate-800 rounded-[2rem] shadow-2xl w-full max-w-lg 
                max-h-[90vh] overflow-y-auto custom-scrollbar transform transition-all 
                animate-in fade-in zoom-in slide-in-from-bottom-8 duration-300">
        
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-6 py-5 border-b border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <span class="w-2 h-6 bg-red-600 rounded-full"></span>
                    تأكيد غياب موظف
                </h3>
                <p class="text-slate-400 text-[11px] mt-0.5 uppercase tracking-widest font-medium" id="empNameDisplay"></p>
            </div>
            <button onclick="closeAbsenceModal()" 
                    class="w-10 h-10 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="absenceForm" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf
            
           
           {{-- حقل التاريخ المطور والمتجاوب --}}
<div class="space-y-2 text-right">
    <label class="block text-sm font-semibold text-slate-300 mr-1">تاريخ الغياب</label>
    <div class="relative group">
        {{-- الحقل الفعلي --}}
        <input type="date" name="date" 
               value="{{ date('Y-m-d') }}" 
               max="{{ date('Y-m-d') }}"
               required
               class="w-full bg-slate-950 border border-slate-800 text-white rounded-2xl p-4 pl-12
                      focus:ring-2 focus:ring-red-600 focus:border-transparent outline-none 
                      transition-all font-bold group-hover:border-slate-700 cursor-pointer
                      relative z-10 appearance-none shadow-inner">
        
        {{-- الأيقونة المخصصة (توضع في جهة اليسار) --}}
        <div class="absolute left-4 top-1/2 -translate-y-1/2 flex items-center gap-2 pointer-events-none z-20">
            <span class="h-6 w-[1px] bg-slate-800 mr-2"></span>
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
</div>
            {{-- حقل الملاحظات --}}
            <div class="space-y-2">
                <label class="block text-sm font-semibold text-slate-300 mr-1">سبب الغياب (اختياري)</label>
                <textarea name="description" rows="3"
                          class="w-full bg-slate-950 border border-slate-800 text-white rounded-2xl p-4 
                                 focus:ring-2 focus:ring-red-600 outline-none transition-all resize-none shadow-inner"
                          placeholder="مثال: ظرف طارئ، مرض، بدون إذن..."></textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <button type="submit" id="submitBtn"
                        class="flex-[2] bg-red-600 hover:bg-red-500 text-white font-bold py-4 rounded-2xl 
                               shadow-lg shadow-red-600/20 transition-all transform active:scale-[0.98] 
                               flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    تأكيد تسجيل الغياب
                </button>
                <button type="button" onclick="closeAbsenceModal()" 
                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-4 rounded-2xl 
                               transition-all border border-slate-700/50">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
</style>

<script>
// ميزة البحث في الموظفين بالاسم
document.getElementById('employeeSearch').addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.employee-card');
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(term) ? 'block' : 'none';
    });
});

function openAbsenceModal(empId, empName) {
    document.getElementById('empNameDisplay').textContent = 'للموظف: ' + empName;
    const routeTemplate = "{{ route('accountant.pos.absence.store', ['employee' => 'ID']) }}";
    document.getElementById('absenceForm').action = routeTemplate.replace('ID', empId);
    
    const modal = document.getElementById('absenceModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAbsenceModal() {
    const modal = document.getElementById('absenceModal');
    modal.classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('absenceForm').reset();
}

// منع الإرسال المتكرر
document.getElementById('absenceForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = 'جاري الحفظ...';
});
</script>
@endsection