@extends('dashboard.app')

@section('title', 'إدارة الموظفين — ' . $store->name)

@section('content')

{{-- تعديل ستايل الهيدر ليتماشى مع صفحة الأشخاص --}}

    <x-store-section
        :title="'موظفين : ' . $store->name"
        :back="route('user.stores.show', $store->id)"
        :add="route('user.employees.create', ['store' => $store->id])"
        addLabel="+ إضافة موظف"
        :returnTo="url()->current()"
        class="bg-[#1a1c23]/80 backdrop-blur-md border border-gray-800 rounded-[2.5rem] shadow-2xl p-6 transition-all group-hover:border-blue-500/30"
    >
   


    <div x-data="{ openRow: null, activeModal: null }">
        
        {{-- 1. عرض الكمبيوتر (Desktop) --}}
        <div class="hidden md:block overflow-x-auto rounded-2xl border border-gray-800 bg-gray-900/40 backdrop-blur-md shadow-2xl">
            <table class="w-full text-right text-sm text-gray-300">
                <thead class="bg-gray-800/50 text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-4 px-6 text-center w-16">#</th>
                        <th class="py-4 px-6">الاسم</th>
                        <th class="py-4 px-6">الراتب</th>
                        <th class="py-4 px-6 text-center">العمليات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($employees as $emp)
                    <tr class="border-b border-gray-800 transition-colors hover:bg-gray-800/30">
                        <td class="py-4 px-6 text-center text-gray-500 font-mono">{{ $loop->iteration }}</td>
                        <td class="py-4 px-6 font-bold text-white">{{ $emp->name }}</td>
                        <td class="py-4 px-6 text-green-400 font-bold">{{ number_format($emp->salary, 2) }} ر.س</td>
                        <td class="py-4 px-6 text-center">
                            <button @click="openRow === {{ $emp->id }} ? openRow = null : openRow = {{ $emp->id }}" 
                                    class="bg-blue-600 px-5 py-2 rounded-xl text-xs font-bold shadow-lg hover:bg-blue-500 transition-all active:scale-95">
                                خيارات العمليات <i class="fa-solid fa-chevron-down mr-2 text-[9px] transition-transform" :class="openRow === {{ $emp->id }} ? 'rotate-180' : ''"></i>
                            </button>
                        </td>
                    </tr>
                    {{-- منطقة العمليات للكمبيوتر --}}
                    <tr x-show="openRow === {{ $emp->id }}" x-cloak x-transition>
                        <td colspan="4" class="p-8 bg-black/40 shadow-inner">
                            <div class="grid grid-cols-8 gap-4">
                                <button @click="activeModal = 'details-{{ $emp->id }}'" class="action-btn border-gray-700 bg-gray-800 hover:text-blue-400"><i class="fa-solid fa-info-circle"></i><span>تفاصيل</span></button>
                                <button @click="activeModal = 'withdrawal-{{ $emp->id }}'" class="action-btn border-emerald-900/30 bg-emerald-900/10 text-emerald-500 hover:bg-emerald-600 hover:text-white"><i class="fa-solid fa-money-bill-transfer"></i><span>سحب</span></button>
                                <button @click="activeModal = 'absence-{{ $emp->id }}'" class="action-btn border-amber-900/30 bg-amber-900/10 text-amber-500 hover:bg-amber-600 hover:text-white"><i class="fa-solid fa-user-clock"></i><span>غياب</span></button>
                                <button @click="activeModal = 'debt-{{ $emp->id }}'" class="action-btn border-rose-900/30 bg-rose-900/10 text-rose-500 hover:bg-rose-600 hover:text-white"><i class="fa-solid fa-receipt"></i><span>دين</span></button>
                                <button @click="activeModal = 'credit-{{ $emp->id }}'" class="action-btn border-purple-900/30 bg-purple-900/10 text-purple-500 hover:bg-purple-600 hover:text-white"><i class="fa-solid fa-tags"></i><span>آجل</span></button>
                                <button @click="activeModal = 'collect-{{ $emp->id }}'" class="action-btn border-indigo-900/30 bg-indigo-900/10 text-indigo-500 hover:bg-indigo-600 hover:text-white"><i class="fa-solid fa-hand-holding-dollar"></i><span>تحصيل</span></button>
                                
                                {{-- زر التعديل --}}
                                <a href="{{ route('user.employees.edit', $emp->id) }}?return_to={{ urlencode(url()->current()) }}" 
                                   class="action-btn border-amber-900/30 bg-amber-900/20 text-amber-500 hover:bg-amber-700 hover:text-white w-full transition-all">
                                    <i class="fa-solid fa-pen-to-square"></i><span>تعديل</span>
                                </a>
                                
                                {{-- زر الحذف مع تأكيد --}}
                                <form action="{{ route('user.employees.destroy', $emp->id) }}?return_to={{ urlencode(url()->current()) }}" method="POST" 
                                      x-data="{ confirmDelete: false }" @submit.prevent="if(confirm('هل أنت متأكد تماماً من حذف الموظف ({{ $emp->name }})؟ لا يمكن التراجع عن هذه العملية.')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn border-red-900/30 bg-red-900/20 text-red-500 hover:bg-red-700 hover:text-white w-full transition-all">
                                        <i class="fa-solid fa-trash-can"></i><span>حذف</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- 2. عرض الجوال (Modern App Interface) --}}
        <div class="md:hidden space-y-4 px-1">
            @foreach ($employees as $emp)
            <div class="bg-[#1a1c23] border border-gray-800 rounded-3xl overflow-hidden shadow-2xl">
                {{-- الهيدر --}}
                <div class="p-4 flex justify-between items-center bg-gradient-to-l from-gray-800/10 to-transparent" 
                     @click="openRow === {{ $emp->id }} ? openRow = null : openRow = {{ $emp->id }}">
                    <div class="flex items-center gap-4 text-right">
                        <div class="relative">
                            <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                                <span class="text-xl font-black">{{ mb_substr($emp->name, 0, 1) }}</span>
                            </div>
                            <span class="absolute -top-2 -right-2 w-6 h-6 bg-gray-900 border border-gray-700 text-gray-400 text-[10px] font-bold rounded-full flex items-center justify-center shadow-md">
                                {{ $loop->iteration }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-white font-bold text-base">{{ $emp->name }}</h3>
                            <span class="text-gray-500 text-[10px] font-mono tracking-tight">{{ $emp->phone ?? 'بدون رقم مسجل' }}</span>
                        </div>
                    </div>
                    <div class="text-left flex items-center gap-3">
                        <span class="text-emerald-400 font-black text-base">{{ number_format($emp->salary, 0) }}</span>
                        <i class="fa-solid fa-chevron-down text-gray-600 text-xs transition-transform" :class="openRow === {{ $emp->id }} ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- منطقة الخيارات للجوال --}}
                <div x-show="openRow === {{ $emp->id }}" x-collapse x-cloak class="bg-[#111318]/40 px-3 pb-5 pt-1 border-t border-gray-800/50">
                    <div class="grid grid-cols-1 gap-2 mt-2">
                        <button @click="activeModal = 'details-{{ $emp->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-blue-500/10 text-blue-500"><i class="fa-solid fa-id-badge"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-4">ملف الموظف الكامل</span>
                            <i class="fa-solid fa-chevron-left text-gray-800 text-[9px] ml-1"></i>
                        </button>
                        <button @click="activeModal = 'withdrawal-{{ $emp->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-emerald-500/10 text-emerald-500"><i class="fa-solid fa-money-bill-transfer"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-4">تسجيل سلفة أو سحب</span>
                            <i class="fa-solid fa-chevron-left text-gray-800 text-[9px] ml-1"></i>
                        </button>
                        <button @click="activeModal = 'absence-{{ $emp->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-amber-500/10 text-amber-500"><i class="fa-solid fa-user-clock"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-4">إضافة غياب / تأخير</span>
                            <i class="fa-solid fa-chevron-left text-gray-800 text-[9px] ml-1"></i>
                        </button>
                        <button @click="activeModal = 'debt-{{ $emp->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-rose-500/10 text-rose-500"><i class="fa-solid fa-file-invoice-dollar"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-4">تعديل مديونية الموظف</span>
                            <i class="fa-solid fa-chevron-left text-gray-800 text-[9px] ml-1"></i>
                        </button>
                        <button @click="activeModal = 'collect-{{ $emp->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-indigo-500/10 text-indigo-500"><i class="fa-solid fa-vault"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-4">تحصيل مبالغ آجلة</span>
                            <i class="fa-solid fa-chevron-left text-gray-800 text-[9px] ml-1"></i>
                        </button>

                        {{-- زر التعديل للجوال --}}
                        <a href="{{ route('user.employees.edit', $emp->id) }}?return_to={{ urlencode(url()->current()) }}" 
                           class="w-full flex items-center p-3 bg-amber-950/20 border border-amber-900/20 rounded-2xl text-amber-500 active:bg-amber-600 active:text-white transition-all mt-2">
                            <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-amber-500/10 ml-4">
                                <i class="fa-solid fa-pen-to-square text-sm"></i>
                            </div>
                            <span class="flex-1 text-right font-bold text-xs uppercase tracking-tighter">تعديل بيانات الموظف</span>
                        </a>

                        {{-- زر الحذف في الجوال مع تأكيد --}}
                        <form action="{{ route('user.employees.destroy', $emp->id) }}?return_to={{ urlencode(url()->current()) }}" method="POST" 
                              class="mt-3" @submit.prevent="if(confirm('سيتم حذف جميع بيانات الموظف المختار بشكل نهائي، هل أنت متأكد؟')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full flex items-center p-3 bg-red-950/20 border border-red-900/20 rounded-2xl text-red-500 active:bg-red-600 active:text-white transition-all">
                                <div class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 ml-4"><i class="fa-solid fa-trash-can text-sm"></i></div>
                                <span class="flex-1 text-right font-bold text-xs uppercase tracking-tighter">حذف سجل الموظف نهائياً</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- 3. المهارة: المودالات مدمجة ومحمية بـ Teleport --}}
        @foreach ($employees as $emp)
        <template x-teleport="body">
            <div x-show="activeModal && activeModal.includes('-{{ $emp->id }}')" x-cloak>
                <template x-if="activeModal === 'details-{{ $emp->id }}'">
                    @include('components.employee.details-modal', ['employee' => $emp, 'modalId' => 'employeeDetailsModal-'.$emp->id, 'type' => 'الموظف'])
                </template>
                <template x-if="activeModal === 'withdrawal-{{ $emp->id }}'">
                    @include('components.employee.withdrawal-form', ['employee' => $emp, 'modalId' => 'withdrawalModal-'.$emp->id])
                </template>
                <template x-if="activeModal === 'absence-{{ $emp->id }}'">
                    @include('components.employee.absence-form', ['employee' => $emp, 'modalId' => 'absenceModal-'.$emp->id])
                </template>
                <template x-if="activeModal === 'debt-{{ $emp->id }}'">
                    @include('components.employee.debt-form', ['employee' => $emp, 'modalId' => 'debtModal-'.$emp->id])
                </template>
                <template x-if="activeModal === 'credit-{{ $emp->id }}'">
                    @include('components.employee.credit-sale-form', ['employee' => $emp, 'modalId' => 'creditSaleModal-'.$emp->id])
                </template>
                <template x-if="activeModal === 'collect-{{ $emp->id }}'">
                    @include('components.employee.credit-sale-collection', ['employee' => $emp, 'modalId' => 'creditSaleCollectionModal-'.$emp->id])
                </template>

                <div x-init="$watch('activeModal', v => { 
                    if(v && v.includes('-{{ $emp->id }}')) {
                        setTimeout(() => {
                            const mapping = {
                                'details-{{ $emp->id }}': 'employeeDetailsModal-{{ $emp->id }}',
                                'withdrawal-{{ $emp->id }}': 'withdrawalModal-{{ $emp->id }}',
                                'absence-{{ $emp->id }}': 'absenceModal-{{ $emp->id }}',
                                'debt-{{ $emp->id }}': 'debtModal-{{ $emp->id }}',
                                'credit-{{ $emp->id }}': 'creditSaleModal-{{ $emp->id }}',
                                'collect-{{ $emp->id }}': 'creditSaleCollectionModal-{{ $emp->id }}'
                            };
                            const targetId = mapping[v];
                            if(targetId) document.getElementById(targetId)?.classList.remove('hidden');
                        }, 50);
                    }
                })"></div>
            </div>
        </template>
        @endforeach
    </div>

</x-store-section>

<style>
    .action-btn { @apply flex flex-col items-center justify-center gap-3 p-4 rounded-2xl border transition-all text-[11px] font-bold text-gray-400 hover:text-white hover:bg-gray-800/50 active:scale-95; }
    .action-btn i { @apply text-xl; }
    .mod-list-item { @apply flex items-center p-3 bg-gray-800/30 border border-gray-700/20 rounded-2xl transition-all active:bg-gray-700 active:scale-[0.98] shadow-sm; }
    .mod-icon-box { @apply w-11 h-11 flex items-center justify-center rounded-xl text-lg shadow-inner bg-gray-900/50 transition-colors duration-200; }
    [x-cloak] { display: none !important; }
</style>
@php
    // جلب عدد الموظفين المحذوفين حذفاً مؤقتاً لهذا المتجر
    // نستخدم onlyTrashed() للتأكد من الموظفين الموجودين في السلة
    $trashedEmployeesCount = $store->employees()->onlyTrashed()->count();
@endphp

@if($trashedEmployeesCount > 0)
    <div class="mt-4">
        <a href="{{ route('user.employees.trash',['from' => 'store', 'store_id' => $store->id]) }}" 
           class="flex items-center justify-between px-4 py-3 bg-red-600/5 border border-red-600/20 rounded-xl group hover:bg-red-600 transition-all duration-300">
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-600/10 rounded-lg flex items-center justify-center group-hover:bg-white/20 transition">
                    <i class="fa-solid fa-user-slash text-red-500 group-hover:text-white"></i>
                </div>
                <div>
                    <span class="block text-red-500 group-hover:text-white font-bold text-sm">سلة محذوفات الموظفين</span>
                    <span class="block text-red-400/80 group-hover:text-white/80 text-[10px]">يوجد {{ $trashedEmployeesCount }} سجل يمكن استعادته</span>
                </div>
            </div>

            <i class="fa-solid fa-chevron-left text-red-400 group-hover:text-white transition"></i>
        </a>
    </div>
@endif
@endsection