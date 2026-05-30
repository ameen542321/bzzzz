@extends('dashboard.app')
@section('title', 'متاجري')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-white">متاجري</h1>
        <p class="text-gray-400 text-sm mt-2">إدارة متاجرك ومتابعة أدائها</p>
    </div>

    {{-- التحقق من إمكانية الإضافة بناءً على الخطة --}}
    @php
        $user = auth()->user();
        $currentCount = $stores->count();
        $totalUsedFromPlan = $totalCountWithTrashed ?? $currentCount;
        $allowedStores = $user->plan->allowed_stores ?? $user->allowed_stores ?? 1;
        $canAdd = $totalUsedFromPlan < $allowedStores;
    @endphp

    @if($canAdd)
        <a href="{{ route('user.stores.create') }}"
           class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30 active:scale-[0.98]">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>متجر جديد</span>
        </a>
    @else
        <div class="relative group">
            <button disabled
                    class="flex items-center gap-2 bg-gray-800/50 text-gray-500 px-5 py-2.5 rounded-xl text-sm font-medium border border-gray-700 cursor-not-allowed">
                <i class="fa-solid fa-lock text-sm"></i>
                <span>متجر جديد</span>
            </button>
            {{-- التنبيه أسفل ويمين الزر --}}
            <div class="absolute top-full right-0 mt-2 px-3 py-2 bg-gray-900 text-gray-300 text-xs rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none border border-gray-800 shadow-xl z-50">
                <div class="absolute bottom-full right-4 -mb-1 border-4 border-transparent border-b-gray-900"></div>
                وصلت للحد الأقصى ({{ $allowedStores }})
                @if(($trashedCount ?? 0) > 0)
                <br><span class="text-blue-400">لديك متاجر في السلة تشغل مساحة</span>
                @endif
            </div>
        </div>
    @endif
</div>

{{-- بطاقة الإحصائيات السريعة --}}
@if($stores->count() > 0 || ($trashedCount ?? 0) > 0)
<div class="bg-gradient-to-br from-slate-900/70 via-gray-900/60 to-gray-900/40 border border-slate-700/60 rounded-2xl p-5 mb-6 shadow-xl shadow-black/20">
    @php
        $activeStores = $stores->where('status', 'active')->count();
        $suspendedStoresCount = $stores->where('status', 'suspended')->count();
        $trashedStoresCount = $trashedCount ?? 0;
        $usagePercent = $allowedStores > 0 ? min(100, round(($totalUsedFromPlan / $allowedStores) * 100)) : 0;
    @endphp

    <div class="mb-4">
        <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
            <span>استخدام الخطة</span>
            <span>{{ $usagePercent }}%</span>
        </div>
        <div class="h-2 bg-gray-800/80 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 rounded-full transition-all duration-500" style="width: {{ $usagePercent }}%"></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="group relative overflow-hidden bg-gray-900/60 backdrop-blur-sm border border-blue-500/20 rounded-xl p-4 hover:border-blue-400/50 transition-all">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-500/10 rounded-full blur-xl"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-gray-400 text-xs mb-1">المساحة المستخدمة</p>
                    <h3 class="text-2xl font-bold text-white">{{ $totalUsedFromPlan }} / {{ $allowedStores }}</h3>
                    <span class="text-xs text-blue-300">الحد المسموح بالخطة</span>
                </div>
                <div class="w-10 h-10 bg-blue-500/15 rounded-lg flex items-center justify-center border border-blue-500/30">
                    <i class="fa-solid fa-store text-blue-300"></i>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden bg-gray-900/60 backdrop-blur-sm border border-emerald-500/20 rounded-xl p-4 hover:border-emerald-400/50 transition-all">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-gray-400 text-xs mb-1">متاجر نشطة</p>
                    <h3 class="text-2xl font-bold text-white">{{ $activeStores }}</h3>
                    <span class="text-xs text-emerald-300">تعمل بشكل طبيعي</span>
                </div>
                <div class="w-10 h-10 bg-emerald-500/15 rounded-lg flex items-center justify-center border border-emerald-500/30">
                    <i class="fa-solid fa-check-circle text-emerald-300"></i>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden bg-gray-900/60 backdrop-blur-sm border border-yellow-500/20 rounded-xl p-4 hover:border-yellow-400/50 transition-all">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-yellow-500/10 rounded-full blur-xl"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-gray-400 text-xs mb-1">متاجر معطّلة</p>
                    <h3 class="text-2xl font-bold text-white">{{ $suspendedStoresCount }}</h3>
                    <span class="text-xs text-yellow-300">تحتاج مراجعة سريعة</span>
                </div>
                <div class="w-10 h-10 bg-yellow-500/15 rounded-lg flex items-center justify-center border border-yellow-500/30">
                    <i class="fa-solid fa-pause-circle text-yellow-300"></i>
                </div>
            </div>
        </div>

        <div class="group relative overflow-hidden bg-gray-900/60 backdrop-blur-sm border border-purple-500/20 rounded-xl p-4 hover:border-purple-400/50 transition-all">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-purple-500/10 rounded-full blur-xl"></div>
            <div class="relative flex items-start justify-between">
                <div>
                    <p class="text-gray-400 text-xs mb-1">المحذوفات</p>
                    <h3 class="text-2xl font-bold text-white">{{ $trashedStoresCount }}</h3>
                    <a href="{{ route('user.stores.trash') }}" class="text-xs text-purple-300 hover:text-purple-200 transition">
                        عرض سلة المحذوفات
                    </a>
                </div>
                <div class="w-10 h-10 bg-purple-500/15 rounded-lg flex items-center justify-center border border-purple-500/30">
                    <i class="fa-solid fa-trash text-purple-300"></i>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- إذا لا يوجد متاجر نشطة --}}
@if($stores->count() === 0)
    <div class="bg-gradient-to-br from-gray-900/50 to-gray-900/30 border border-gray-800 rounded-2xl p-8 md:p-12 text-center">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500/10 to-blue-600/10 text-blue-400 flex items-center justify-center rounded-2xl mx-auto mb-6 border border-blue-500/20">
            <i class="fa-solid fa-store text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-white mb-3">ابدأ رحلتك التجارية</h2>
        <p class="text-gray-400 text-sm mb-8 max-w-md mx-auto leading-relaxed">
            لم تقم بإنشاء أي متجر حتى الآن. ابدأ بمتجرك الأول وأطلق العنان لإمكانيات عملك
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            @if($canAdd)
            <a href="{{ route('user.stores.create') }}"
               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 text-white px-8 py-3 rounded-xl text-sm font-medium transition-all shadow-lg shadow-blue-600/20 hover:shadow-blue-600/30">
                <i class="fa-solid fa-plus"></i>
                إنشاء متجري الأول
            </a>
            @endif
            <a href="{{ route('user.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 bg-gray-800/50 hover:bg-gray-800 text-gray-300 hover:text-white px-8 py-3 rounded-xl text-sm font-medium transition-all border border-gray-700 hover:border-gray-600">
                <i class="fa-solid fa-question-circle"></i>
                كيف أبدأ؟
            </a>
        </div>
    </div>
@else
    {{-- تنبيهات مهمة --}}
    @php
        $suspendedStores = $stores->where('status', 'suspended');
        $noProductsStores = [];
        $noEmployeesStores = [];
    @endphp

    @if($suspendedStores->count() > 0 || count($noProductsStores) > 0 || count($noEmployeesStores) > 0)
    <div class="mb-6">
        <div class="bg-gradient-to-r from-yellow-900/10 to-yellow-900/5 border border-yellow-800/50 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fa-solid fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-white font-medium mb-1">تحذيرات تحتاج لمراجعتك</h4>
                    <ul class="text-yellow-300 text-sm space-y-1">
                        @if($suspendedStores->count() > 0)
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-circle text-[6px]"></i>
                                {{ $suspendedStores->count() }} متجر معطل
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- عرض المتاجر --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($stores as $store)
            @include('user.stores.includes.store-card', ['store' => $store])
        @endforeach
    </div>

    {{-- تحسينات للأجهزة الصغيرة --}}
    <div class="block md:hidden mt-6">
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-mobile-screen text-blue-400 text-lg"></i>
                <div>
                    <p class="text-white text-sm font-medium">تصفح أسهل على الجوال</p>
                    <p class="text-gray-400 text-xs">اسحب لليمين لعرض المزيد من الخيارات</p>
                </div>
            </div>
        </div>
    </div>

    {{-- التوجيه السريع --}}
    @if($canAdd)
    <div class="mt-8 text-center">
        <div class="inline-flex items-center gap-4 px-6 py-4 bg-gradient-to-r from-gray-900/50 to-gray-900/30 border border-gray-800 rounded-xl">
            <div class="text-left">
                <p class="text-white text-sm font-medium mb-1">لا تزال لديك مساحة</p>
                <p class="text-gray-400 text-xs">
                    يمكنك إضافة {{ $allowedStores - $totalUsedFromPlan }} متاجر أخرى
                </p>
            </div>
            <a href="{{ route('user.stores.create') }}"
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">
                <i class="fa-solid fa-plus mr-2"></i>
                إضافة متجر
            </a>
        </div>
    </div>
    @endif
@endif

{{-- رابط سلة المحذوفات --}}
@if(($trashedCount ?? 0) > 0)
<div class="mt-8 pt-6 border-t border-gray-800">
    <a href="{{ route('user.stores.trash') }}"
       class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition group">
        <i class="fa-solid fa-trash-can text-sm"></i>
        <span class="text-sm">سلة المحذوفات ({{ $trashedCount }})</span>
        <i class="fa-solid fa-arrow-left text-xs opacity-0 group-hover:opacity-100 transition-opacity"></i>
    </a>
</div>
@endif

{{-- معلومات الخطة --}}
<div class="mt-8 bg-gradient-to-r from-gray-900/30 to-gray-900/10 border border-gray-800 rounded-xl p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500/10 to-blue-600/10 rounded-lg flex items-center justify-center">
                <i class="fa-solid fa-crown text-blue-400"></i>
            </div>
            <div>
                <h4 class="text-white font-medium">{{ $user->plan->name ?? 'الخطة الأساسية' }}</h4>
                <p class="text-gray-400 text-xs">
                    {{ $allowedStores }} متجر مسموح به
                    • {{ $user->plan->allowed_accountants ?? $user->allowed_accountants ?? 1 }} محاسب
                </p>
            </div>
        </div>
        @if(!$canAdd && $allowedStores > 0)
        <a href="{{ route('user.subscription.renew') }}"
           class="bg-gradient-to-r from-yellow-600 to-yellow-500 hover:from-yellow-700 hover:to-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition whitespace-nowrap">
            <i class="fa-solid fa-arrow-up mr-2"></i>
            ترقية الخطة
        </a>
        @endif
    </div>
</div>

@endsection
