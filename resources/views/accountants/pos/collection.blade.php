@extends('dashboard.app')
@section('title', 'تحصيل البيع الآجل')
@section('content')

<div class="max-w-7xl mx-auto px-3 py-3"> {{-- تصغير المسافات الخارجية --}}

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
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-green-600 to-emerald-700 flex items-center justify-center shadow">
                <span class="text-white text-sm">💳</span>
            </div>
            <div>
                <h1 class="text-base font-bold text-white">تحصيل البيع الآجل</h1>
                <p class="text-slate-400 text-[10px]">اختر الموظف لعرض العمليات</p>
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

        {{-- قسم اختيار الموظف --}}
        <div class="lg:col-span-2">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-3 backdrop-blur-sm"> {{-- تصغير padding --}}

                {{-- شريط البحث والعنوان المدمج --}}
                <div class="flex items-center justify-between gap-2 mb-2">
                    <h2 class="text-sm font-bold text-white flex items-center gap-1">
                        <span class="w-1.5 h-4 bg-green-500 rounded-full"></span>
                        الموظفين
                    </h2>
                    <div class="relative w-48">
                        <input type="text" id="employeeSearch"
                               placeholder="بحث..."
                               class="w-full bg-slate-900/50 border border-slate-700 text-slate-300 text-xs rounded-lg py-1.5 px-3 focus:ring-1 focus:ring-green-500 outline-none">
                    </div>
                </div>

                {{-- كروت الموظفين المدمجة --}}
                <div class="space-y-1.5 max-h-[500px] overflow-y-auto custom-scrollbar pr-1">
                    @foreach($people as $emp)
                    @php
                        $pendingCount = $emp->pending_credit_sales->count();
                        $totalPending = $emp->pending_credit_sales->sum('remaining_amount');
                    @endphp
                    <div class="employee-card bg-slate-900/40 border border-slate-700/50 rounded-lg p-2 hover:border-green-500/50 transition-all"
                         data-name="{{ $emp->name }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 min-w-0 flex-1">
                                <div class="relative flex-shrink-0">
                                    <div class="w-7 h-7 rounded-full {{ $emp->role === 'accountant' ? 'bg-blue-900/40 border border-blue-700/30' : 'bg-slate-800 border border-slate-700' }} flex items-center justify-center text-white font-bold text-xs">
                                        {{ mb_substr($emp->name, 0, 1) }}
                                    </div>
                                    @if($pendingCount > 0)
                                        <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-600 rounded-full flex items-center justify-center border border-slate-900">
                                            <span class="text-[8px] text-white">{{ $pendingCount }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1">
                                        <h3 class="text-white text-xs font-medium truncate">{{ $emp->name }}</h3>
                                        <span class="text-[8px] px-1 py-0.5 rounded-md {{ $emp->role === 'accountant' ? 'bg-blue-500/10 text-blue-400' : 'bg-slate-800 text-slate-500' }}">
                                            {{ $emp->role === 'accountant' ? 'محاسب' : '' }}
                                        </span>
                                    </div>
                                    @if($pendingCount > 0)
                                        <span class="text-[8px] px-1 py-0.5 rounded-md bg-red-500/10 text-red-400">
                                            {{ number_format($totalPending, 0) }} ر.س
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <button onclick="openCollectionModal({{ $emp->id }}, '{{ $emp->name }}')"
                                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 text-white px-2 py-1 rounded-lg text-[10px] font-medium transition-all shadow flex-shrink-0">
                                تحصيل
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- قسم السجل الجانبي المدمج --}}
        <div class="lg:col-span-1">
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-3 sticky top-3 shadow">
                <h2 class="text-sm font-bold text-white mb-2 flex items-center gap-1">
                    <span class="text-yellow-500">🕒</span>
                    آخر التحصيلات
                </h2>

                <div class="space-y-2 max-h-[400px] overflow-y-auto custom-scrollbar pr-1">
                    @foreach ($lastCollections as $log)
                    <div class="group relative pr-2 border-r-2 {{ $log->action_name === 'credit_sale_deducted' ? 'border-green-800 hover:border-green-500' : 'border-blue-800 hover:border-blue-500' }} transition-colors">
                        <div class="flex justify-between items-start">
                            <h4 class="text-slate-200 text-xs font-medium">{{ $log->person->name ?? '—' }}</h4>
                            <span class="{{ $log->action_name === 'credit_sale_deducted' ? 'text-green-400' : 'text-blue-400' }} font-bold text-xs">
                                {{ $log->amount ?? 0 }} ﷼
                            </span>
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-slate-500 text-[9px]">{{ $log->created_at->format('Y-m-d') }}</p>
                            <span class="text-[8px] px-1 py-0.5 rounded {{ $log->action_name === 'credit_sale_deducted' ? 'bg-green-500/10 text-green-400' : 'bg-blue-500/10 text-blue-400' }}">
                                {{ $log->action_name === 'credit_sale_deducted' ? 'كامل' : 'جزئي' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- مودال التحصيل المدمج --}}
{{-- ============================= --}}
<div id="collectionModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50 p-3">

    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-2xl w-full max-w-md
                max-h-[80vh] overflow-y-auto custom-scrollbar mx-2">

        {{-- رأس المودال المدمج --}}
        <div class="sticky top-0 bg-slate-900/95 backdrop-blur-sm z-10 px-3 py-2.5 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-4 bg-green-600 rounded-full"></span>
                <h3 class="text-sm font-bold text-white">تحصيل</h3>
                <span class="text-slate-400 text-[10px] mr-1" id="empName"></span>
            </div>
            <button onclick="closeCollectionModal()"
                    class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- قائمة العمليات --}}
        <div id="creditSalesList" class="p-3 space-y-2"></div>

        {{-- زر الإغلاق --}}
        <div class="p-3 pt-0">
            <button type="button" onclick="closeCollectionModal()"
                    class="w-full bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm py-2 rounded-lg transition-all">
                إغلاق
            </button>
        </div>
    </div>
</div>

{{-- ============================= --}}
{{-- مودال التحصيل الجزئي المدمج --}}
{{-- ============================= --}}
<div id="partialModal"
     class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center backdrop-blur-sm z-50 p-3">

    <div class="bg-slate-900 border border-slate-800 rounded-xl shadow-2xl w-full max-w-sm mx-2">

        {{-- رأس المودال المدمج --}}
        <div class="px-3 py-2.5 border-b border-slate-800 flex justify-between items-center">
            <div class="flex items-center gap-1.5">
                <span class="w-1.5 h-4 bg-blue-600 rounded-full"></span>
                <h3 class="text-sm font-bold text-white">تحصيل جزئي</h3>
            </div>
            <button onclick="closePartialModal()"
                    class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 transition-all flex items-center justify-center">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- محتوى المودال المدمج --}}
        <form id="partialForm" class="p-3 space-y-2">
            <div class="space-y-1">
                <label class="block text-xs text-slate-300">المبلغ</label>
                <input id="partialAmount" type="number" min="1"
                       class="w-full bg-slate-950 border border-slate-800 text-white rounded-lg p-2
                              focus:ring-1 focus:ring-blue-600 outline-none text-sm">
            </div>

            <div class="flex gap-2 pt-1">
                <button type="submit"
                        class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500
                               text-white text-sm font-medium py-2 rounded-lg transition-all">
                    تأكيد
                </button>
                <button type="button" onclick="closePartialModal()"
                        class="flex-1 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium py-2 rounded-lg">
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
</style>

<script>
    const allSales = @json($people->mapWithKeys(function($emp){
        return [$emp->id => $emp->pending_credit_sales];
    }));

function openCollectionModal(empId, empName) {

    if (empId == {{ auth('accountant')->user()->employee_id }}) {
        document.getElementById('empName').innerText = empName;
        document.getElementById('creditSalesList').innerHTML = `
            <div class="text-center py-6">
                <div class="w-12 h-12 mx-auto mb-2 rounded-full bg-red-900/20 border border-red-700/30 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <p class="text-slate-400 text-xs">عفوًا لا تملك الإذن بذلك، راجع مالك المتجر أو المدير </p>
            </div>
        `;
        document.getElementById('collectionModal').classList.remove('hidden');
        return;
    }

    let sales = allSales[empId];
    let html = '';

    sales.forEach(sale => {
        const fullRoute = "{{ route('accountant.pos.collection.store', ['sale' => 'SALE']) }}"
            .replace('SALE', sale.id);

        html += `
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-lg p-2.5">
                <div class="flex justify-between mb-1.5">
                    <span class="text-white text-xs">المبلغ: ${sale.amount} ريال</span>
                    <span class="text-yellow-400 text-xs font-bold">المتبقي: ${sale.remaining_amount}</span>
                </div>
                <div class="text-slate-400 text-[10px] mb-2">${sale.date}</div>
                <div class="flex gap-1.5">
                    <form action="${fullRoute}" method="POST" class="flex-1">
                        @csrf
                        <button class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500
                                       text-white text-xs py-1.5 rounded-lg">
                            كامل
                        </button>
                    </form>
                    <button onclick="openPartialModal(${sale.id}, ${sale.remaining_amount})"
                            class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500
                                   text-white text-xs py-1.5 rounded-lg">
                        جزئي
                    </button>
                </div>
            </div>
        `;
    });

    document.getElementById('empName').innerText = empName;
    document.getElementById('creditSalesList').innerHTML = html;
    document.getElementById('collectionModal').classList.remove('hidden');
}

    function closeCollectionModal() {
        document.getElementById('collectionModal').classList.add('hidden');
    }

    function openPartialModal(saleId, maxAmount) {
        const form = document.getElementById('partialForm');
        const amountInput = document.getElementById('partialAmount');
        amountInput.max = maxAmount;

        const route = "{{ route('accountant.pos.collection.store', ['sale' => 'SALE']) }}"
            .replace('SALE', saleId);

        form.onsubmit = function(e) {
            e.preventDefault();
            const val = amountInput.value;

            if (val < 1 || val > maxAmount) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'المبلغ غير صالح',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('amount', val);

            fetch(route, {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                if (!response.ok) {
                    const data = await response.json();
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.error ?? 'غير مصرح',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'تم التحصيل',
                    showConfirmButton: false,
                    timer: 2000
                });

                setTimeout(() => location.reload(), 1500);
            });
        };

        document.getElementById('partialModal').classList.remove('hidden');
    }

    function closePartialModal() {
        document.getElementById('partialModal').classList.add('hidden');
    }

    document.getElementById('employeeSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.employee-card');

        cards.forEach(card => {
            const name = card.getAttribute('data-name').toLowerCase();
            card.style.display = name.includes(term) ? 'flex' : 'none';
        });
    });
</script>

@endsection