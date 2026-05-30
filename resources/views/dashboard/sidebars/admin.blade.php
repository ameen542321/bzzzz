<!-- resources/views/dashboard/sidebars/admin.blade.php -->
<div
    x-data="{ open: false }"
    class="bg-gray-900 border-l border-gray-800 text-gray-200 min-h-screen transition-all duration-300 flex flex-col"
    :class="open ? 'w-64' : 'w-20'"
>

    {{-- زر الطي --}}
    <div class="flex justify-end p-3">
        <button
            @click="open = !open"
            class="text-gray-400 hover:text-white transition-transform duration-300"
            :class="open ? 'rotate-180' : ''"
        >
            <i class="fa-solid fa-angles-right"></i>
        </button>
    </div>

    {{-- عنوان --}}
    <h2 class="text-center text-xl font-bold mb-6 transition-opacity duration-300"
        x-show="open" x-cloak>
        المدير العام
    </h2>

    {{-- العناصر --}}
    <ul class="space-y-2 px-3">

        {{-- الرئيسية --}}
        <li>
            <a href="{{ route('admin.dashboard.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                       {{ request()->routeIs('admin.dashboard.index') ? 'bg-gray-800 text-blue-400' : 'text-gray-300' }}">
                <i class="fa-solid fa-house w-6 text-lg"></i>
                <span x-show="open" x-cloak>الرئيسية</span>
            </a>
        </li>

        {{-- إدارة المستخدمين --}}
        <li x-data="{ dropUsers: {{ request()->routeIs('admin.users.*') ? 'true' : 'false' }} }">

            <button
                @click="dropUsers = !dropUsers"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition
                       {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-blue-400' : 'text-gray-300' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-users w-6 text-lg"></i>
                    <span x-show="open" x-cloak>المستخدمين</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropUsers ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul x-show="dropUsers" x-cloak class="mt-2 space-y-2 pr-3 text-sm text-gray-400" x-transition.opacity>
                <li>
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.users.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض المستخدمين</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.users.create') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.users.create') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-plus w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إضافة مستخدم</span>
                    </a>
                </li>
            </ul>

        </li>

        {{-- إدارة المتاجر --}}
        <li x-data="{ dropStores: {{ request()->routeIs('admin.stores.*') ? 'true' : 'false' }} }">

            <button
                @click="dropStores = !dropStores"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition
                       {{ request()->routeIs('admin.stores.*') ? 'bg-gray-800 text-blue-400' : 'text-gray-300' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-store w-6 text-lg"></i>
                    <span x-show="open" x-cloak>المتاجر</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropStores ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul x-show="dropStores" x-cloak class="mt-2 space-y-2 pr-3 text-sm text-gray-400" x-transition.opacity>
                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.stores.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض المتاجر</span>
                    </a>
                </li>

                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.stores.create') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-plus w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إضافة متجر</span>
                    </a>
                </li>
            </ul>

        </li>

        {{-- إدارة المحاسبين --}}
        <li x-data="{ dropAccountants: {{ request()->routeIs('admin.accountants.*') ? 'true' : 'false' }} }">

            <button
                @click="dropAccountants = !dropAccountants"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition
                       {{ request()->routeIs('admin.accountants.*') ? 'bg-gray-800 text-blue-400' : 'text-gray-300' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-user-tie w-6 text-lg"></i>
                    <span x-show="open" x-cloak>المحاسبين</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropAccountants ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul x-show="dropAccountants" x-cloak class="mt-2 space-y-2 pr-3 text-sm text-gray-400" x-transition.opacity>
                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.accountants.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض المحاسبين</span>
                    </a>
                </li>

                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('admin.accountants.create') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-plus w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إضافة محاسب</span>
                    </a>
                </li>
            </ul>

        </li>

        {{-- الإشعارات --}}
      <li x-data="{ dropNotifs: {{ request()->routeIs('admin.notifications.*') ? 'true' : 'false' }} }">


            <button
                @click="dropNotifs = !dropNotifs"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition
                       {{ request()->routeIs('admin.notifications.*') ? 'bg-gray-800 text-blue-400' : 'text-gray-300' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-bell w-6 text-lg"></i>
                    <span x-show="open" x-cloak>الإشعارات</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropNotifs ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul x-show="dropNotifs" x-cloak class="mt-2 space-y-2 pr-3 text-sm text-gray-400" x-transition.opacity>

                <li>
                    <a href="{{ route('admin.notifications.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>مركز الإشعارات</span>
                    </a>
                </li>

                <li>
                   <a href="{{ route('notifications.internal.send') }}"


                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-paper-plane w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إرسال إشعار داخلي</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.onesignal.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-satellite-dish w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إعدادات OneSignal</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.notifications.push') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-broadcast-tower w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إرسال إشعار OneSignal</span>
                    </a>
                </li>

            </ul>

        </li>

        {{-- التقارير العامة --}}
        <li>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300">
                <i class="fa-solid fa-chart-line w-6 text-lg"></i>
                <span x-show="open" x-cloak>التقارير العامة</span>
            </a>
        </li>

        {{-- سجل العمليات --}}
        <li>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300">
                <i class="fa-solid fa-clock-rotate-left w-6 text-lg"></i>
                <span x-show="open" x-cloak>سجل العمليات</span>
            </a>
        </li>

    </ul>

</div>
