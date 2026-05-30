@extends('dashboard.app')
@section('title', 'الإشعارات')
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

    $unreadCount = $notifications->filter(fn ($notification) => !$notification->isReadBy($currentUser->id))->count();
@endphp

<div class="max-w-6xl mx-auto px-4 py-6 space-y-5">

    <div class="bg-gradient-to-r from-gray-900 to-gray-900/80 border border-gray-800 rounded-2xl p-5">
        <div class="flex flex-col md:flex-row gap-4 md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-black text-white">مركز الإشعارات</h1>
                <p class="text-gray-400 text-sm mt-1">تابع كل التنبيهات الداخلية وقم بإدارة حالتها بسهولة.</p>
            </div>

            <div class="flex gap-2 flex-wrap">
                <span class="px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/30 text-blue-300 text-xs font-bold">
                    الإجمالي: {{ $notifications->count() }}
                </span>
                <span class="px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-bold">
                    غير المقروء: {{ $unreadCount }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
        <div class="flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                    <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-500 bg-gray-800">
                    تحديد الكل
                </label>

                <form method="POST" action="{{ $notifRoute('markAll') }}">
                    @csrf
                    <button class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold transition">
                        تعليم الكل كمقروء
                    </button>
                </form>
            </div>

            <form id="bulk-form" method="POST" action="{{ $notifRoute('markSelected') }}">
                @csrf
                <button class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold transition">
                    تحديد المحدد كمقروء
                </button>
            </form>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $n)
            @php $isRead = $n->isReadBy($currentUser->id); @endphp
            <div class="bg-gray-900/90 border {{ $isRead ? 'border-gray-800' : 'border-blue-500/40' }} rounded-2xl p-4 shadow-sm">
                <div class="flex flex-col lg:flex-row gap-4 lg:items-start lg:justify-between">
                    <div class="flex items-start gap-3 min-w-0">
                        <input type="checkbox"
                               form="bulk-form"
                               name="selected[]"
                               value="{{ $n->id }}"
                               class="item-checkbox mt-1 w-4 h-4 rounded border-gray-500 bg-gray-800">

                        <div class="min-w-0">
                            <a href="{{ $notifRoute('show', $n->id) }}"
                               class="text-base font-bold {{ $isRead ? 'text-gray-300' : 'text-blue-300' }} hover:text-blue-200 transition">
                                {{ $n->title }}
                            </a>
                            <p class="text-sm text-gray-300 mt-1 break-words">{{ $n->message }}</p>
                            <div class="mt-2 flex items-center gap-2 text-xs text-gray-500">
                                <i class="fa-regular fa-clock"></i>
                                {{ $n->created_at->format('Y-m-d H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-row lg:flex-col gap-2 shrink-0">
                        <form method="POST" action="{{ $notifRoute('toggle', $n->id) }}">
                            @csrf
                            <button class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold transition w-full">
                                {{ $isRead ? 'وضع كغير مقروء' : 'وضع كمقروء' }}
                            </button>
                        </form>

                        <form method="POST" action="{{ $notifRoute('delete', $n->id) }}" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="{{ url()->current() }}">
                            <button class="px-3 py-2 rounded-lg bg-red-600/90 hover:bg-red-600 text-white text-xs font-bold transition w-full">
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-gray-400 py-12 bg-gray-900 border border-gray-800 rounded-2xl">
                لا توجد إشعارات حالياً.
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.item-checkbox');

        if (!selectAll) return;

        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        });
    });
</script>

@endsection
