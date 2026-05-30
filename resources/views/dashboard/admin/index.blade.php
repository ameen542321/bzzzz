@extends('dashboard.app')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">لوحة الأدمن</h1>
    <p class="text-gray-400 text-sm mt-1">نظرة عامة على النظام (بيانات حقيقية)</p>
</div>

{{-- البطاقات الديناميكية --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

    {{-- عدد المستخدمين (أصحاب المتاجر) --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المستخدمين</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['users_count']) }}</h3>
            </div>
            <div class="bg-blue-600/20 text-blue-400 p-3 rounded-lg">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
        </div>
    </div>

    {{-- عدد المحاسبين --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المحاسبين</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['accountants_count']) }}</h3>
            </div>
            <div class="bg-green-600/20 text-green-400 p-3 rounded-lg">
                <i class="fa-solid fa-user-tie text-xl"></i>
            </div>
        </div>
    </div>

    {{-- عدد المتاجر --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">عدد المتاجر</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['stores_count']) }}</h3>
            </div>
            <div class="bg-yellow-600/20 text-yellow-400 p-3 rounded-lg">
                <i class="fa-solid fa-store text-xl"></i>
            </div>
        </div>
    </div>

    {{-- الإشعارات --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">الإشعارات الجديدة</p>
                <h3 class="text-2xl font-bold text-white mt-1">{{ number_format($stats['notifications_count']) }}</h3>
            </div>
            <div class="bg-purple-600/20 text-purple-400 p-3 rounded-lg">
                <i class="fa-solid fa-bell text-xl"></i>
            </div>
        </div>
    </div>

</div>

{{-- آخر الأنشطة الديناميكية --}}
<div class="mt-10 bg-gray-800 border border-gray-700 rounded-xl p-6">
    <h2 class="text-xl font-semibold text-white mb-4">آخر الأنشطة</h2>

    <ul class="space-y-3">
        @forelse($recent_activities as $log)
            <li class="flex items-center justify-between border-b border-gray-700 pb-3">
                <span class="text-gray-300">{{ $log->description }}</span>
                <span class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
            </li>
        @empty
            <li class="text-gray-500 text-center py-4">لا توجد أنشطة مسجلة حالياً</li>
        @endforelse
    </ul>
</div>

@endsection
