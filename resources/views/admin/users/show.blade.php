@extends('dashboard.app')

@section('title', 'تفاصيل التاجر | ' . $user->name)

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-6 text-right" dir="rtl">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-900/40 p-6 rounded-3xl border border-gray-800 shadow-xl backdrop-blur-sm">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-blue-600/20 border border-blue-500/30 rounded-2xl flex items-center justify-center text-blue-500 text-2xl font-bold shadow-lg shadow-blue-500/10">
                {{ mb_substr($user->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-gray-400">
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-envelope text-blue-500/70"></i> {{ $user->email }}</span>
                    <span class="flex items-center gap-1.5"><i class="fa-solid fa-phone text-blue-500/70"></i> {{ $user->phone ?? 'لا يوجد هاتف' }}</span>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
             {{-- زر تبديل الحالة --}}
           
<form action="{{ route('admin.users.toggleStatus', $user->id) }}" method="POST">
    @csrf
    {{-- ✅ أضف هذا السطر هنا --}}
    @method('PATCH')

    <button type="submit" class="px-5 py-2.5 {{ $user->status == 'active' ? 'bg-amber-600/10 text-amber-500 border-amber-500/20' : 'bg-emerald-600/10 text-emerald-500 border-emerald-500/20' }} border text-sm font-bold rounded-xl transition-all flex items-center gap-2">
        <i class="fa-solid {{ $user->status == 'active' ? 'fa-pause' : 'fa-play' }}"></i>
        {{ $user->status == 'active' ? 'إيقاف مؤقت' : 'تفعيل الحساب' }}
    </button>
</form>

            <a href="{{ route('admin.users.edit', $user->id) }}"
               class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold rounded-xl transition-all flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square"></i> تعديل
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-bold rounded-xl border border-gray-700 transition-all">
                رجوع <i class="fa-solid fa-arrow-left mr-1"></i>
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- الحالة --}}
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-3xl relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-1 h-full {{ $user->status == 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">حالة الحساب</p>
            <div class="text-xl font-bold {{ $user->status == 'active' ? 'text-emerald-400' : 'text-red-400' }}">
                {{ $user->status == 'active' ? 'نشط' : 'موقوف' }}
            </div>
        </div>

        {{-- الخطة --}}
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-3xl">
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">باقة الاشتراك</p>
            <div class="text-xl font-bold text-blue-400">{{ $user->plan->name ?? 'بدون خطة' }}</div>
        </div>

        {{-- المتاجر --}}
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-3xl">
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">المتاجر</p>
            <div class="flex items-end gap-2 text-xl font-bold text-white">
                {{ $user->stores->count() }} <span class="text-sm text-gray-600 font-normal">من أصل {{ $user->allowed_stores }}</span>
            </div>
        </div>

        {{-- المحاسبين --}}
        <div class="bg-gray-900/50 border border-gray-800 p-5 rounded-3xl">
            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">المحاسبين</p>
            <div class="flex items-end gap-2 text-xl font-bold text-white">
                {{ $user->accountants->count() }} <span class="text-sm text-gray-600 font-normal">من أصل {{ $user->allowed_accountants }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- جدول المتاجر --}}
            <div class="bg-gray-900/50 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
                <div class="p-6 border-b border-gray-800">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="fa-solid fa-store text-blue-500"></i> المتاجر التابعة
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-800/30 text-gray-400">
                            <tr>
                                <th class="px-6 py-4">اسم المتجر</th>
                                <th class="px-6 py-4 text-center">الحالة</th>
                                <th class="px-6 py-4">تاريخ الإنشاء</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @forelse($user->stores as $store)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-6 py-4 text-gray-200">{{ $store->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $store->status == 'active' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }}">
                                        {{ $store->status == 'active' ? 'نشط' : 'موقف' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500">{{ $store->created_at->format('Y-m-d') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="px-6 py-10 text-center text-gray-600">لا توجد متاجر</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- المواعيد والاشتراك --}}
        <div class="space-y-6">
            <div class="bg-gray-900/50 border border-gray-800 p-6 rounded-3xl shadow-xl">
                <h3 class="text-gray-200 font-semibold mb-6 text-sm border-b border-gray-800 pb-4">مواعيد هامة</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">تاريخ التسجيل</span>
                        <span class="text-white text-sm">{{ $user->created_at->format('Y-m-d') }}</span>
                    </div>
                    @php
                        $subEnd = $user->subscription_end_at ? \Carbon\Carbon::parse($user->subscription_end_at) : null;
                        $expires = $user->expires_at ? \Carbon\Carbon::parse($user->expires_at) : null;
                    @endphp
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">انتهاء الاشتراك</span>
                        <span class="px-3 py-1 rounded-lg text-xs {{ $subEnd && $subEnd->isFuture() ? 'text-emerald-400 bg-emerald-400/10' : 'text-red-400 bg-red-400/10' }}">
                            {{ $subEnd ? $subEnd->format('Y-m-d') : 'غير محدد' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 text-sm">تاريخ الإغلاق (Expiry)</span>
                        <span class="text-gray-300 text-sm">{{ $expires ? $expires->format('Y-m-d') : 'غير محدد' }}</span>
                    </div>
                </div>
            </div>

            {{-- قسم الحذف النهائي --}}
            <div class="bg-red-500/5 border border-red-500/20 p-6 rounded-3xl">
                <h3 class="text-red-400 font-semibold mb-3 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> منطقة خطر
                </h3>
                <p class="text-[11px] text-gray-500 mb-5">
                    حذف هذا التاجر سيؤدي لمسح جميع بيانات متاجره ومنتجاته بشكل نهائي.
                </p>
                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('⚠️ هل أنت متأكد تماماً من حذف التاجر وكل توابعه؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-3 bg-red-600/10 hover:bg-red-600 text-red-500 hover:text-white border border-red-500/30 rounded-2xl text-xs font-bold transition-all">
                        حذف الحساب نهائياً
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
