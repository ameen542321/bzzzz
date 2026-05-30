@extends('dashboard.app')

@section('title', 'التقارير - ' . $store->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">مركز التقارير</h1>
        <p class="text-gray-400 text-sm mt-1">{{ $store->name }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('user.stores.reports.last-ten-days', $store->id) }}"
           class="block bg-gradient-to-br from-blue-900/40 to-blue-800/20 border border-blue-700/40 rounded-2xl p-5 hover:border-blue-500 transition">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-chart-line text-blue-300 text-xl"></i>
                <h2 class="text-white font-bold">تقارير مبيعات آخر 10 أيام</h2>
            </div>
            <p class="text-gray-300 text-sm">عرض ملفات تقارير الإقفال المولدة خلال آخر 10 أيام.</p>
        </a>

        <a href="{{ route('user.stores.reports.monthly', $store->id) }}"
           class="block bg-gradient-to-br from-emerald-900/40 to-emerald-800/20 border border-emerald-700/40 rounded-2xl p-5 hover:border-emerald-500 transition">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-calendar-alt text-emerald-300 text-xl"></i>
                <h2 class="text-white font-bold">التقرير الشهري للمتجر</h2>
            </div>
            <p class="text-gray-300 text-sm">ملخص شهري شامل: مبيعات، استهلاك، رواتب، مصروفات وصافي النتيجة.</p>
        </a>

        <a href="{{ route('user.stores.reports.search', $store->id) }}"
           class="block bg-gradient-to-br from-cyan-900/40 to-cyan-800/20 border border-cyan-700/40 rounded-2xl p-5 hover:border-cyan-500 transition">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-magnifying-glass-chart text-cyan-300 text-xl"></i>
                <h2 class="text-white font-bold">تقرير بحث شامل للمتجر</h2>
            </div>
            <p class="text-gray-300 text-sm">ابحث بكلمة واحدة داخل المبيعات، الاستهلاك الداخلي، ومشتريات المالك خلال فترة محددة.</p>
        </a>

        <div class="bg-gray-900/40 border border-gray-700 rounded-2xl p-5 opacity-70">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-users text-purple-300 text-xl"></i>
                <h2 class="text-white font-bold">تقرير الموظفين</h2>
            </div>
            <p class="text-gray-400 text-sm">قريباً</p>
        </div>
    </div>
</div>
@endsection
