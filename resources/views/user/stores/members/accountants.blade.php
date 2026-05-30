@extends('dashboard.app')

@section('title', 'محاسبين — ' . $store->name)

@section('content')

<x-store-section
    :title="'محاسبو المتجر: ' . $store->name"
    :back="route('user.stores.show', $store->id)"
    :add="route('user.accountants.create', ['store' => $store->id, 'from' => 'store'])"
     addLabel="+ إضافة محاسب"
    :returnTo="url()->current()"
>

    <div x-data="{ openRow: null, activeModal: null }">
        
        {{-- 1. عرض الكمبيوتر (Desktop) --}}
        <div class="hidden md:block overflow-x-auto rounded-3xl border border-gray-800 bg-gray-900/40 backdrop-blur-md shadow-2xl">
            <table class="w-full text-right text-sm text-gray-300">
                <thead class="bg-gray-800/50 text-gray-400 font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-4 px-6 text-center w-16 tracking-tighter">#</th>
                        <th class="py-4 px-6">الاسم الكامل</th>
                        <th class="py-4 px-6">البريد الإلكتروني</th>
                        <th class="py-4 px-6 text-center">الحالة</th>
                        <th class="py-4 px-6 text-center">الإدارة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accountants as $acc)
                    <tr class="border-b border-gray-800 transition-colors hover:bg-blue-600/5 group">
                        <td class="py-4 px-6 text-center text-gray-500 font-mono text-xs">{{ $loop->iteration }}</td>
                        <td class="py-4 px-6 font-bold text-white group-hover:text-blue-400 transition-colors">{{ $acc->employee->name ?? '-' }}</td>
                        <td class="py-4 px-6 text-gray-400 font-mono text-xs">{{ $acc->email }}</td>
                        <td class="py-4 px-6 text-center">
                            @if($acc->status === 'active')
                                <span class="bg-emerald-500/10 text-emerald-500 px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-500/20">نشط</span>
                            @else
                                <span class="bg-red-500/10 text-red-500 px-3 py-1 rounded-full text-[10px] font-bold border border-red-500/20">موقوف</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-center">
                            <button @click="openRow === {{ $acc->id }} ? openRow = null : openRow = {{ $acc->id }}" 
                                    class="bg-blue-600/10 text-blue-500 border border-blue-600/20 px-5 py-2 rounded-xl text-xs font-bold hover:bg-blue-600 hover:text-white transition-all active:scale-95">
                                خيارات العمليات <i class="fa-solid fa-sliders mr-2 text-[9px] transition-transform" :class="openRow === {{ $acc->id }} ? 'rotate-90' : ''"></i>
                            </button>
                        </td>
                    </tr>

                    {{-- منطقة العمليات للكمبيوتر (كاملة 100%) --}}
                    <tr x-show="openRow === {{ $acc->id }}" x-cloak x-transition>
                        <td colspan="5" class="p-8 bg-black/40 shadow-inner">
                            <div class="grid grid-cols-8 gap-4">
                                {{-- تفاصيل --}}
                                <button @click="activeModal = 'details-{{ $acc->id }}'" class="action-btn border-gray-700 bg-gray-800 hover:text-blue-400">
                                    <i class="fa-solid fa-circle-info"></i><span>تفاصيل</span>
                                </button>
                                {{-- سحب --}}
                                <button @click="activeModal = 'withdrawal-{{ $acc->id }}'" class="action-btn border-emerald-900/30 bg-emerald-900/10 text-emerald-500 hover:bg-emerald-600 hover:text-white">
                                    <i class="fa-solid fa-money-bill-transfer"></i><span>سحب</span>
                                </button>
                                {{-- غياب --}}
                                <button @click="activeModal = 'absence-{{ $acc->id }}'" class="action-btn border-amber-900/30 bg-amber-900/10 text-amber-500 hover:bg-amber-600 hover:text-white">
                                    <i class="fa-solid fa-user-clock"></i><span>غياب</span>
                                </button>
                                {{-- مديونية --}}
                                <button @click="activeModal = 'debt-{{ $acc->id }}'" class="action-btn border-rose-900/30 bg-rose-900/10 text-rose-500 hover:bg-rose-600 hover:text-white">
                                    <i class="fa-solid fa-receipt"></i><span>مديونية</span>
                                </button>
                                {{-- بيع آجل --}}
                                <button @click="activeModal = 'credit-{{ $acc->id }}'" class="action-btn border-purple-900/30 bg-purple-900/10 text-purple-500 hover:bg-purple-600 hover:text-white">
                                    <i class="fa-solid fa-tags"></i><span>آجل</span>
                                </button>
                                {{-- تحصيل --}}
                                <button @click="activeModal = 'collect-{{ $acc->id }}'" class="action-btn border-indigo-900/30 bg-indigo-900/10 text-indigo-500 hover:bg-indigo-600 hover:text-white">
                                    <i class="fa-solid fa-hand-holding-dollar"></i><span>تحصيل</span>
                                </button>
                                
                                {{-- زر التعديل --}}
                                <a href="{{ route('user.accountants.edit', $acc->id) }}?return_to={{ urlencode(url()->current()) }}" 
                                   class="action-btn border-amber-900/30 bg-amber-900/20 text-amber-500 hover:bg-amber-700 hover:text-white w-full transition-all">
                                    <i class="fa-solid fa-pen-to-square"></i><span>تعديل</span>
                                </a>
                                
                                {{-- حذف --}}
                                <form action="{{ route('user.accountants.delete', $acc->id) }}" method="POST" 
                                      @submit.prevent="if(confirm('هل أنت متأكد من حذف حساب المحاسب ({{ $acc->employee->name ?? '' }})؟')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn border-red-900/30 bg-red-900/20 text-red-500 hover:bg-red-700 hover:text-white w-full">
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

        {{-- 2. عرض الجوال (Mobile) - كامل بجميع العمليات --}}
        <div class="md:hidden space-y-4 px-1">
            @foreach ($accountants as $acc)
            <div class="bg-[#1a1c23] border border-gray-800 rounded-[2rem] overflow-hidden shadow-2xl transition-transform active:scale-[0.99]">
                {{-- هيدر البطاقة --}}
                <div class="p-5 flex justify-between items-center bg-gradient-to-br from-gray-800/20 to-transparent" 
                     @click="openRow === {{ $acc->id }} ? openRow = null : openRow = {{ $acc->id }}">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-500 rounded-2xl flex items-center justify-center text-white shadow-lg border border-white/5">
                                <span class="text-xl font-black">{{ mb_substr($acc->employee->name ?? 'م', 0, 1) }}</span>
                            </div>
                            <span class="absolute -top-1 -right-1 w-6 h-6 bg-white text-blue-600 text-[10px] font-black rounded-full flex items-center justify-center shadow-lg border-2 border-[#1a1c23]">
                                {{ $loop->iteration }}
                            </span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="text-white font-bold text-base">{{ $acc->employee->name ?? '-' }}</h3>
                            <span class="text-gray-500 text-[10px] font-mono truncate w-32 uppercase tracking-tighter">{{ $acc->email }}</span>
                        </div>
                    </div>
                    <div class="text-left flex flex-col items-end gap-2">
                        @if($acc->status === 'active')
                            <span class="w-2 h-2 bg-emerald-500 rounded-full shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                        @else
                            <span class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                        @endif
                        <i class="fa-solid fa-chevron-down text-gray-700 text-xs transition-transform duration-300" :class="openRow === {{ $acc->id }} ? 'rotate-180 text-blue-500' : ''"></i>
                    </div>
                </div>

                {{-- قائمة العمليات كاملة للجوال --}}
                <div x-show="openRow === {{ $acc->id }}" x-collapse x-cloak class="bg-[#111318]/60 px-4 pb-6 pt-2 border-t border-gray-800/50">
                    <div class="grid grid-cols-1 gap-2.5 mt-2">
                        {{-- تفاصيل --}}
                        <button @click="activeModal = 'details-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-blue-500/10 text-blue-400 group-active:bg-blue-500 group-active:text-white"><i class="fa-solid fa-user-gear"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">عرض ملف المحاسب</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>
                        {{-- سحب --}}
                        <button @click="activeModal = 'withdrawal-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-emerald-500/10 text-emerald-400 group-active:bg-emerald-500 group-active:text-white"><i class="fa-solid fa-money-bill-transfer"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">تسجيل سحوبات</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>
                        {{-- غياب --}}
                        <button @click="activeModal = 'absence-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-amber-500/10 text-amber-400 group-active:bg-amber-500 group-active:text-white"><i class="fa-solid fa-clock-rotate-left"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">إضافة غياب / تأخير</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>
                        {{-- مديونية --}}
                        <button @click="activeModal = 'debt-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-rose-500/10 text-rose-400 group-active:bg-rose-500 group-active:text-white"><i class="fa-solid fa-receipt"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">مديونية المحاسب</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>
                        {{-- بيع آجل --}}
                        <button @click="activeModal = 'credit-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-purple-500/10 text-purple-400 group-active:bg-purple-500 group-active:text-white"><i class="fa-solid fa-tags"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">تسجيل بيع آجل</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>
                        {{-- تحصيل --}}
                        <button @click="activeModal = 'collect-{{ $acc->id }}'" class="mod-list-item group">
                            <div class="mod-icon-box bg-indigo-500/10 text-indigo-400 group-active:bg-indigo-500 group-active:text-white"><i class="fa-solid fa-vault"></i></div>
                            <span class="flex-1 text-right text-gray-300 font-bold text-xs uppercase mr-5">تحصيل الأقساط</span>
                            <i class="fa-solid fa-angle-left text-gray-800 text-[10px]"></i>
                        </button>

                        {{-- زر التعديل للجوال --}}
                        <a href="{{ route('user.accountants.edit', $acc->id) }}?return_to={{ urlencode(url()->current()) }}" 
                           class="w-full flex items-center p-3 bg-amber-950/20 border border-amber-900/20 rounded-[1.25rem] text-amber-500 active:bg-amber-600 active:text-white transition-all mt-2">
                            <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-500/10 ml-5">
                                <i class="fa-solid fa-pen-to-square text-base"></i>
                            </div>
                            <span class="flex-1 text-right font-black text-xs uppercase">تعديل بيانات المحاسب</span>
                        </a>

                        {{-- حذف المحاسب --}}
                        <form action="{{ route('user.accountants.delete', $acc->id) }}" method="POST" class="mt-4" 
                              @submit.prevent="if(confirm('تنبيه: هل تريد حذف المحاسب ({{ $acc->employee->name ?? '' }}) نهائياً؟')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full flex items-center p-3 bg-red-600/5 border border-red-600/20 rounded-[1.25rem] text-red-500 active:bg-red-600 active:text-white transition-all shadow-sm">
                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-600/10 ml-5"><i class="fa-solid fa-trash-can text-base"></i></div>
                                <span class="flex-1 text-right font-black text-xs uppercase">إلغاء المحاسب وحذف حسابه</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- 3. المودالات بنظام Teleport لضمان سرعة الاستجابة --}}
        @foreach ($accountants as $acc)
        <template x-teleport="body">
            <div x-show="activeModal && activeModal.includes('-{{ $acc->id }}')" x-cloak>
                <template x-if="activeModal === 'details-{{ $acc->id }}'">
                    @include('components.employee.details-modal', ['employee' => $acc->employee, 'modalId' => 'employeeDetailsModal-'.$acc->id, 'type' => 'المحاسب'])
                </template>
                <template x-if="activeModal === 'withdrawal-{{ $acc->id }}'">
                    @include('components.employee.withdrawal-form', ['employee' => $acc->employee, 'modalId' => 'withdrawalModal-'.$acc->id])
                </template>
                <template x-if="activeModal === 'absence-{{ $acc->id }}'">
                    @include('components.employee.absence-form', ['employee' => $acc->employee, 'modalId' => 'absenceModal-'.$acc->id])
                </template>
                <template x-if="activeModal === 'debt-{{ $acc->id }}'">
                    @include('components.employee.debt-form', ['employee' => $acc->employee, 'modalId' => 'debtModal-'.$acc->id])
                </template>
                <template x-if="activeModal === 'credit-{{ $acc->id }}'">
                    @include('components.employee.credit-sale-form', ['employee' => $acc->employee, 'modalId' => 'creditSaleModal-'.$acc->id])
                </template>
                <template x-if="activeModal === 'collect-{{ $acc->id }}'">
                    @include('components.employee.credit-sale-collection', ['employee' => $acc->employee, 'modalId' => 'creditSaleCollectionModal-'.$acc->id])
                </template>

                <div x-init="$watch('activeModal', v => { 
                    if(v && v.includes('-{{ $acc->id }}')) {
                        setTimeout(() => {
                            const mapping = {
                                'details-{{ $acc->id }}': 'employeeDetailsModal-{{ $acc->id }}',
                                'withdrawal-{{ $acc->id }}': 'withdrawalModal-{{ $acc->id }}',
                                'absence-{{ $acc->id }}': 'absenceModal-{{ $acc->id }}',
                                'debt-{{ $acc->id }}': 'debtModal-{{ $acc->id }}',
                                'credit-{{ $acc->id }}': 'creditSaleModal-{{ $acc->id }}',
                                'collect-{{ $acc->id }}': 'creditSaleCollectionModal-{{ $acc->id }}'
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
@php
    // جلب عدد المحاسبين المحذوفين فقط التابعين للمحل الحالي أو المستخدم الحالي
    $trashedAccountantsCount = \App\Models\Accountant::onlyTrashed()
        ->where('user_id', auth()->id()) // لضمان جلب محذوفات المستخدم الحالي فقط
        ->count();
@endphp

{{-- يظهر الرابط فقط إذا كان هناك محاسبون في السلة --}}
@if($trashedAccountantsCount > 0)
    <div class="mt-4">
        <a href="{{ route('user.accountants.trash', ['from' => 'store', 'store_id' => $store->id]) }}" 
           class="flex items-center justify-center gap-2 px-4 py-2 bg-amber-600/10 border border-amber-600/20 text-amber-600 rounded-xl hover:bg-amber-600 hover:text-white transition-all duration-300">
            
            <i class="fa-solid fa-user-slash"></i>
            <span class="font-bold text-sm">سلة المحاسبين المحذوفين</span>
            
            <span class="bg-amber-600 text-white text-[10px] px-2 py-0.5 rounded-full group-hover:bg-white transition">
                {{ $trashedAccountantsCount }}
            </span>
        </a>
    </div>
@endif
<style>
    /* تنسيق كروية أزرار العمليات (الكمبيوتر) */
    .action-btn { @apply flex flex-col items-center justify-center gap-3 p-5 rounded-[1.5rem] border border-gray-800 transition-all text-[11px] font-black text-gray-400 hover:shadow-xl active:scale-95; }
    .action-btn i { @apply text-2xl; }

    /* تنسيق قائمة العمليات (الجوال) */
    .mod-list-item { @apply flex items-center p-3 bg-gray-900/40 border border-gray-800/40 rounded-[1.25rem] transition-all active:bg-blue-600/10 active:scale-[0.98] shadow-sm; }
    .mod-icon-box { @apply w-12 h-12 flex items-center justify-center rounded-2xl text-xl shadow-inner bg-black/20 transition-all duration-300; }
    
    [x-cloak] { display: none !important; }
</style>

@endsection