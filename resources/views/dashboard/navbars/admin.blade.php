@php
    use Illuminate\Support\Facades\Auth;

    // المدير العام
    $auth = Auth::guard('web')->user();

    // عداد الإشعارات
    $unreadCount = \App\Models\Notification::unreadCountFor($auth->id);

    // آخر الإشعارات
    $latestNotifications = \App\Models\Notification::orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->filter(function ($n) use ($auth) {
            if ($n->target_type === 'all') return true;
            if (in_array($auth->id, $n->target_ids ?? [])) return true;
            return false;
        });
@endphp

<nav class="bg-gray-900 border-b border-gray-800"
     x-data="{ openMenu: false, openUser: false, openNotif: false }">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- يسار --}}
            <div class="flex items-center gap-4">

                {{-- زر القائمة للجوال --}}
                <button @click="openMenu = !openMenu" class="lg:hidden text-gray-300 hover:text-white">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>

                {{-- الشعار --}}
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                    <span class="text-white font-bold text-lg">Carled</span>
                </div>

            </div>

            {{-- يمين --}}
            <div class="flex items-center gap-6">

                {{-- الإشعارات --}}
                <div class="relative">
    {{-- زر الجرس --}}
    <button
        @click="openNotif = !openNotif; openUser = false"
        class="text-gray-300 hover:text-white relative transition"
    >
        <i class="fa-regular fa-bell text-xl"></i>

        {{-- البادج --}}
        <span data-notif-badge
              class="absolute -top-1 -right-2 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full
                     {{ $unreadCount > 0 ? '' : 'hidden' }}">
            {{ $unreadCount }}
        </span>
    </button>

    {{-- القائمة --}}
    <div
        x-show="openNotif"
        @click.outside="openNotif = false"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute left-0 mt-3 w-80 bg-gray-900/95 backdrop-blur-md border border-gray-700/70
               rounded-xl shadow-2xl py-3 z-50"
    >
        {{-- العنوان --}}
        <div class="px-4 pb-2 border-b border-gray-700/60">
            <h4 class="text-white font-semibold text-lg">الإشعارات</h4>
        </div>

        {{-- القائمة --}}
        <div data-notif-list class="max-h-72 overflow-y-auto custom-scroll px-1">
            @forelse($latestNotifications as $n)
                <a href="{{ route('admin.notifications.show', $n->id) }}"
                   class="block px-4 py-3 rounded-lg transition
                          hover:bg-gray-800/70 cursor-pointer
                          {{ $n->isReadBy($auth->id) ? 'text-gray-400' : 'text-gray-200 font-semibold' }}">
                    <div class="flex items-start gap-3">
                        {{-- أيقونة --}}
                        <div class="w-9 h-9 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm">
                            <i class="fa-solid fa-bell"></i>
                        </div>

                        <div class="flex-1">
                            <div class="text-sm">{{ $n->title }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $n->message }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-4 py-6 text-gray-400 text-center text-sm">
                    لا توجد إشعارات
                </div>
            @endforelse
        </div>

        {{-- زر عرض الكل --}}
        <div class="border-t border-gray-700/60 mt-2 pt-2">
            <a href="{{ route('admin.notifications.index') }}"
               class="block px-4 py-2 text-blue-400 hover:text-blue-300 text-center text-sm">
                عرض جميع الإشعارات
            </a>
        </div>
    </div>
</div>

                {{-- المستخدم --}}
                <div class="relative">
                    <button
                        @click="openUser = !openUser; openNotif = false"
                        class="flex items-center gap-2"
                    >
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($auth->name) }}"
                            class="w-9 h-9 rounded-full border border-gray-700"
                        >
                        <i class="fa-solid fa-chevron-down text-gray-400 text-sm"></i>
                    </button>

                    <div
                        x-show="openUser"
                        @click.outside="openUser = false"
                        x-cloak
                        class="absolute left-0 mt-3 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-lg py-2 z-50"
                    >
                        {{-- <a href="#"
                           class="px-4 py-2 flex items-center gap-3 text-gray-300 hover:bg-gray-700 cursor-pointer">
                            <i class="fa-solid fa-user text-gray-400"></i>
                            <span>الملف الشخصي</span>
                        </a> --}}

                        <a href="#"
                           class="px-4 py-2 flex items-center gap-3 text-gray-300 hover:bg-gray-700 cursor-pointer">
                            <i class="fa-solid fa-gear text-gray-400"></i>
                            <span>الإعدادات</span>
                        </a>

                        <div class="border-t border-gray-700 my-2"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-gray-700">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- قائمة الجوال الخاصة بالمدير العام --}}
    <div
        x-show="openMenu"
        x-cloak
        class="lg:hidden bg-gray-900 border-t border-gray-800 px-4 py-4 space-y-3"
    >
        <a href="{{ route('admin.users.create') }}"
           class="w-full flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition">
            <i class="fa-solid fa-user-plus text-blue-400 text-lg"></i>
            <span>إضافة مستخدم</span>
        </a>
    </div>

</nav>

<script>
    window.Echo.private('user.{{ $auth->id }}')
        .listen('.new-notification', (e) => {
            const badge = document.querySelector('[data-notif-badge]');
            if (badge) {
                let current = parseInt(badge.innerText || '0');
                badge.innerText = current + 1;
                badge.classList.remove('hidden');
            }

            const list = document.querySelector('[data-notif-list]');
            if (list) {
                const item = document.createElement('div');
                item.className = 'px-4 py-2 hover:bg-gray-700 cursor-pointer text-gray-200 font-semibold';
                item.innerHTML = `<div>${e.title}</div><div class="text-xs text-gray-400">${e.message}</div>`;
                list.prepend(item);
            }
        });
</script>
