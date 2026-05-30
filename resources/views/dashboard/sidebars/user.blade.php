@php
    $user = auth()->user();
    $plan = $user->plan;

    // المتاجر
    $currentStores = \App\Models\Store::where('user_id', $user->id)->count();
    $remainingStores = $plan->allowed_stores - $currentStores;

    // المحاسبين
    $currentAccountants = \App\Models\Accountant::where('user_id', $user->id)->count();
    $allowedAccountants = $plan->allowed_accountants;
    $remainingAccountants = $allowedAccountants - $currentAccountants;
@endphp

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
        لوحة التحكم
    </h2>

    {{-- العناصر --}}
    <ul class="space-y-2 px-3">

        {{-- الرئيسية --}}
        <li>
            <a href="{{ route('user.dashboard.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.dashboard.index') ? 'bg-gray-800 text-blue-400' : '' }}">
                <i class="fa-solid fa-house w-6 text-lg"></i>
                <span x-show="open" x-cloak>الرئيسية</span>
            </a>
        </li>

        {{-- الملف الشخصي --}}
        <li>
            <a href="#"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300">
                <i class="fa-solid fa-user w-6 text-lg"></i>
                <span x-show="open" x-cloak>الملف الشخصي</span>
            </a>
        </li>

        {{-- المتاجر --}}
        <li x-data="{ dropStores: {{ request()->routeIs('user.stores.*') ? 'true' : 'false' }} }">

            <button
                @click="dropStores = !dropStores"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.stores.*') ? 'bg-gray-800 text-blue-400' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-store w-6 text-lg"></i>
                    <span x-show="open" x-cloak>المتاجر</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropStores ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul
                x-show="dropStores"
                x-cloak
                class="mt-2 space-y-2 pr-3 text-sm text-gray-400"
                x-transition.opacity
            >
                <li>
                    <a href="{{ route('user.stores.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('user.stores.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض المتاجر</span>
                    </a>
                </li>

                @if ($remainingStores > 0)
                    <li>
                        <a href="{{ route('user.stores.create') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                                   {{ request()->routeIs('user.stores.create') ? 'text-blue-400' : '' }}">
                            <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                            <span x-show="open" x-cloak>إضافة متجر</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="#"
                            class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-yellow-400">
                            <i class="fa-solid fa-circle-up w-5 text-xs"></i>
                            <span x-show="open" x-cloak>ترقية الاشتراك</span>
                        </a>
                    </li>
                @endif

                <li>
                    <a href="{{ route('user.stores.trash') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('user.stores.trash') ? 'text-blue-400' : 'text-gray-400' }}">
                        <i class="fa-solid fa-trash w-5 text-xs"></i>
                        <span x-show="open" x-cloak>سلة المحذوفات</span>
                    </a>
                </li>

            </ul>

        </li>

        {{-- الموظفين (المحاسبين) --}}
        <li x-data="{ dropEmployees: {{ request()->routeIs('user.accountants.*') ? 'true' : 'false' }} }">

            <button
                @click="dropEmployees = !dropEmployees"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.accountants.*') ? 'bg-gray-800 text-blue-400' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-users-gear w-6 text-lg"></i>
                    <span x-show="open" x-cloak>الموظفين</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropEmployees ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul
                x-show="dropEmployees"
                x-cloak
                class="mt-2 space-y-2 pr-3 text-sm text-gray-400"
                x-transition.opacity
            >
                {{-- عرض المحاسبين --}}
                <li>
                    <a href="{{ route('user.accountants.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('user.accountants.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-user-tie w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض المحاسبين</span>
                    </a>
                </li>

                {{-- إضافة محاسب --}}
                @if ($remainingAccountants > 0)
                    <li>
                        <a href="{{ route('user.accountants.create') }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                                   {{ request()->routeIs('user.accountants.create') ? 'text-blue-400' : '' }}">
                            <i class="fa-solid fa-circle-plus w-5 text-xs"></i>
                            <span x-show="open" x-cloak>إضافة محاسب</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="#"
                            class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-yellow-400">
                            <i class="fa-solid fa-circle-up w-5 text-xs"></i>
                            <span x-show="open" x-cloak>ترقية الاشتراك</span>
                        </a>
                    </li>
                @endif

                {{-- سلة المحاسبين --}}
                <li>
                    <a href="{{ route('user.accountants.trash') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('user.accountants.trash') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-trash w-5 text-xs"></i>
                        <span x-show="open" x-cloak>سلة المحاسبين</span>
                    </a>
                </li>

            </ul>

        </li>

        {{-- العمال --}}
        <li x-data="{ dropWorkers: {{ request()->routeIs('user.employees.*') ? 'true' : 'false' }} }">

            <button
                @click="dropWorkers = !dropWorkers"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.employees.*') ? 'bg-gray-800 text-blue-400' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-people-group w-6 text-lg"></i>
                    <span x-show="open" x-cloak>العمال</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropWorkers ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul
                x-show="dropWorkers"
                x-cloak
                class="mt-2 space-y-2 pr-3 text-sm text-gray-400"
                x-transition.opacity
            >
                {{-- عرض العمال --}}
                <li>
                    <a href="{{ route('user.employees.index') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('employees.index') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-dot w-5 text-xs"></i>
                        <span x-show="open" x-cloak>عرض العمال</span>
                    </a>
                </li>

                {{-- إضافة عامل --}}
                <li>
                    <a href="{{ route('user.employees.create') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('employees.create') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-circle-plus w-5 text-xs"></i>
                        <span x-show="open" x-cloak>إضافة عامل</span>
                    </a>
                </li>

                {{-- سلة العمال --}}
                <li>
                    <a href="{{ route('user.employees.trash') }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition
                               {{ request()->routeIs('employees.trash') ? 'text-blue-400' : '' }}">
                        <i class="fa-solid fa-trash w-5 text-xs"></i>
                        <span x-show="open" x-cloak>سلة العمال</span>
                    </a>
                </li>

            </ul>

        </li>

        {{-- التقارير --}}
        <li x-data="{ dropReports: {{ request()->routeIs('user.reports.*') ? 'true' : 'false' }} }">

            <button
                @click="dropReports = !dropReports"
                class="flex items-center justify-between w-full px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.reports.*') ? 'bg-gray-800 text-blue-400' : '' }}"
            >
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-chart-line w-6 text-lg"></i>
                    <span x-show="open" x-cloak>التقارير</span>
                </div>

                <i class="fa-solid fa-chevron-down transition-transform"
                   :class="dropReports ? 'rotate-180' : ''"
                   x-show="open" x-cloak></i>
            </button>

            <ul
                x-show="dropReports"
                x-cloak
                class="mt-2 space-y-2 pr-3 text-sm text-gray-400"
                x-transition.opacity
            >
                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-receipt w-5 text-xs"></i>
                        <span x-show="open" x-cloak>تقارير المبيعات</span>
                    </a>
                </li>

                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-cart-shopping w-5 text-xs"></i>
                        <span x-show="open" x-cloak>تقارير المشتريات</span>
                    </a>
                </li>

                <li>
                    <a href="#"
                        class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition">
                        <i class="fa-solid fa-money-bill-transfer w-5 text-xs"></i>
                        <span x-show="open" x-cloak>العمليات المالية</span>
                    </a>
                </li>
            </ul>

        </li>

        {{-- سجل العمليات --}}
        <li>
            <a href="{{ route('user.logs.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-gray-800 transition text-gray-300
                       {{ request()->routeIs('user.logs.index') ? 'bg-gray-800 text-blue-400' : '' }}">
                <i class="fa-solid fa-clock-rotate-left w-6 text-lg"></i>
                <span x-show="open" x-cloak>سجل العمليات</span>
            </a>
        </li>

    </ul>

</div>
