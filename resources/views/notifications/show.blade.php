@extends('dashboard.app')
@section('title', 'تفاصيل الإشعار')

@section('content')
@php
    $currentUser = auth('accountant')->check() ? auth('accountant')->user() : auth('web')->user();

    if (auth('accountant')->check()) {
        $routePrefix = 'accountant.notifications.';
    } elseif (auth('web')->check() && auth('web')->user()->role === 'admin') {
        $routePrefix = 'admin.notifications.';
    } else {
        $routePrefix = 'user.notifications.';
    }

    $notifRoute = fn ($name, $id = null) => $id
        ? route($routePrefix . $name, $id)
        : route($routePrefix . $name);

    $isRead = $notification->isReadBy($currentUser->id);
@endphp

<div class="max-w-4xl mx-auto px-4 py-6 space-y-5">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black text-white">تفاصيل الإشعار</h1>
            <p class="text-gray-400 text-sm mt-1">عرض كامل لمحتوى الإشعار وإدارته.</p>
        </div>

        <div class="flex gap-2 flex-wrap">
            <a href="{{ $notifRoute('index') }}"
               class="px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-sm font-bold transition">
                <i class="fa-solid fa-arrow-right ml-1"></i> رجوع
            </a>

            <form method="POST" action="{{ $notifRoute('toggle', $notification->id) }}">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition">
                    {{ $isRead ? 'وضع كغير مقروء' : 'وضع كمقروء' }}
                </button>
            </form>

            <form method="POST" action="{{ $notifRoute('delete', $notification->id) }}" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="redirect_to" value="{{ $notifRoute('index') }}">
                <button class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-bold transition">
                    حذف
                </button>
            </form>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isRead ? 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-300' : 'bg-amber-500/10 border border-amber-500/30 text-amber-300' }}">
                {{ $isRead ? 'مقروء' : 'غير مقروء' }}
            </span>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-gray-800 text-gray-300 border border-gray-700">
                {{ $notification->created_at->format('Y-m-d H:i') }}
            </span>
        </div>

        <h2 class="text-xl font-black text-blue-300 mb-3">{{ $notification->title }}</h2>

        <div class="mb-4 text-sm text-gray-300 flex items-center gap-2">
            <i class="fa-solid fa-paper-plane text-gray-500"></i>
            <span class="text-gray-400">المرسل:</span>
            @switch($notification->sender_type)
                @case('admin')
                    <span>المدير العام</span>
                    @break
                @case('user')
                    <span>المالك</span>
                    @break
                @case('accountant')
                    <span>محاسب</span>
                    @break
                @case('CARLED')
                    <span class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-xs">CARLED</span>
                    @break
                @default
                    <span>غير معروف</span>
            @endswitch
        </div>

        <div class="bg-gray-800/70 border border-gray-700 rounded-xl p-4 text-gray-200 leading-7 whitespace-pre-line">
            {{ $notification->message }}
        </div>

        @if(isset($notification->data['url']) && $notification->data['url'])
            <div class="mt-4">
                <a href="{{ $notification->data['url'] }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition">
                    <i class="fa-solid fa-link"></i>
                    فتح الرابط المرتبط
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
