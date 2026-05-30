{{-- resources/views/layouts/partials/navbar.blade.php --}}
@php
    // 1. جلب بيانات المستخدم والخطة
    $auth = $global_auth ?? auth()->user();
    $plan = $global_plan ?? ($auth ? $auth->plan : null);

    // 2. الحساب الصحيح (النشط + المحذوف) لضبط استهلاك الخطة
    $currentStores = $auth->stores()->withTrashed()->count();
    $currentAccountants = $auth->accountants()->count();
    $allowedStores = $plan->allowed_stores ?? 0;

    // 3. المتبقي الفعلي
    $remainingStores = max(0, $allowedStores - $currentStores);
    $remainingAccountants = max(0, ($plan->allowed_accountants ?? 0) - $currentAccountants);

    // 4. الإشعارات
    $latestNotifications = \App\Models\Notification::forUser($auth->id)->latest()->take(5)->get();
    $unreadCount = \App\Models\Notification::unreadCountFor($auth->id);

    // 5. التحقق مما إذا كنا داخل متجر
    $routeName = request()->route() ? request()->route()->getName() : '';
    $currentStore = null;
    $storeId = null;
    $storeName = null;
    $isInStore = false;
    $storeSwitchRoute = 'user.stores.show';

    if (request()->route('store')) {
        $currentStore = request()->route('store');
        $storeId = is_object($currentStore) ? $currentStore->id : $currentStore;
        $storeName = is_object($currentStore) ? $currentStore->name : 'المتجر';
        $isInStore = true;

        // عند تغيير المتجر: ابقَ على نفس القسم الحالي بدل الرجوع للوحة المتجر دائمًا
        if (request()->routeIs('user.stores.daily')) {
            $storeSwitchRoute = 'user.stores.daily';
        } elseif (request()->routeIs('user.stores.products.*')) {
            $storeSwitchRoute = 'user.stores.products.index';
        } elseif (request()->routeIs('user.stores.supply.*')) {
            $storeSwitchRoute = 'user.stores.supply.index';
        } elseif (request()->routeIs('user.stores.internal-use.*')) {
            $storeSwitchRoute = 'user.stores.internal-use.report.view';
        } elseif (request()->routeIs('user.stores.employees.*')) {
            $storeSwitchRoute = 'user.stores.employees.index';
        } elseif (request()->routeIs('user.stores.accountants.*')) {
            $storeSwitchRoute = 'user.stores.accountants.index';
        } elseif (request()->routeIs('user.stores.invoices.*')) {
            $storeSwitchRoute = 'user.stores.invoices.index';
        }
    }

    // 6. قائمة عناصر قائمة المتجر
    $storeMenuItems = [
        [
            'route' => 'user.stores.show',
            'icon' => 'gauge',
            'label' => 'لوحة المتجر',
            'color' => 'blue',
            'active' => request()->routeIs('user.stores.show')
        ],
        [
            'route' => 'user.stores.daily',
            'icon' => 'chart-line',
            'label' => 'مبيعات اليوم',
            'color' => 'green',
            'active' => request()->routeIs('user.stores.daily')
        ],
        [
            'route' => 'user.stores.invoices.index',
            'icon' => 'file-invoice',
            'label' => 'الفواتير',
            'color' => 'indigo',
            'active' => request()->routeIs('user.stores.invoices.*')
        ],
        [
            'route' => 'user.stores.products.index',
            'icon' => 'boxes',
            'label' => 'المنتجات',
            'color' => 'blue',
            'active' => request()->routeIs('user.stores.products.*')
        ],
        [
            'route' => 'user.stores.supply.index',
            'icon' => 'truck-fast',
            'label' => 'التوريد',
            'color' => 'green',
            'active' => request()->routeIs('user.stores.supply.*')
        ],
        [
            'route' => 'user.stores.internal-use.report.view',
            'icon' => 'chart-line',
            'label' => 'الاستهلاك',
            'color' => 'yellow',
            'active' => request()->routeIs('user.stores.internal-use.report.view')
        ],
        [
            'route' => 'user.stores.employees.index',
            'icon' => 'users',
            'label' => 'الموظفين',
            'color' => 'purple',
            'active' => request()->routeIs('user.stores.employees.*')
        ],
        [
            'route' => 'user.stores.accountants.index',
            'icon' => 'user-tie',
            'label' => 'المحاسبين',
            'color' => 'green',
            'active' => request()->routeIs('user.stores.accountants.*')
        ]
    ];
@endphp

<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50 shadow-xl"
     x-data="{
        openMenu: false,
        openUser: false,
        openNotif: false,
        openStoreMenu: false,

        init() {
            // إغلاق جميع القوائم عند التحميل
            this.openStoreMenu = false;
            this.openMenu = false;
            this.openUser = false;
            this.openNotif = false;
        },

        toggleStoreMenu() {
            this.openMenu = false;
            this.openUser = false;
            this.openNotif = false;
            this.openStoreMenu = !this.openStoreMenu;
        },

        closeAllMenus() {
            this.openStoreMenu = false;
            this.openMenu = false;
            this.openUser = false;
            this.openNotif = false;
        }
     }"
     @keydown.escape.window="closeAllMenus()">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- ===== الجهة اليسرى: الهمبرجر الرئيسي + الشعار + قائمة المتجر ===== --}}
            <div class="flex items-center gap-2">
                {{-- زر الهمبرجر الرئيسي --}}
                <button @click="openMenu = !openMenu; openStoreMenu = false; openUser = false; openNotif = false"
                        class="flex items-center gap-2 text-gray-300 hover:text-white transition p-2 rounded-lg hover:bg-gray-800">
                    <i class="fa-solid fa-bars text-xl"></i>
                    <span class="hidden sm:inline text-sm font-medium">القائمة</span>
                </button>

                {{-- شعار CARLED --}}
                <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 group ml-2">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6] group-hover:scale-125 transition-transform"></div>
                        <div class="absolute inset-0 w-3 h-3 rounded-full bg-blue-400 animate-ping opacity-20"></div>
                    </div>
                    <span class="text-white font-black text-xl tracking-wider uppercase">Car<span class="text-blue-500">led</span></span>
                </a>

                {{-- 🏬 قائمة المتجر - تظهر فقط داخل المتجر --}}
                @if($isInStore && $storeId)
                    <div class="relative mr-2 pr-2 border-r border-gray-800">
                        <button @click="toggleStoreMenu()"
                                class="flex items-center gap-2 text-blue-400 hover:text-white transition p-2 rounded-lg hover:bg-gray-800"
                                :class="{ 'bg-gray-800': openStoreMenu }">
                            <i class="fa-solid fa-bars-staggered text-xl"></i>
                            <span class="hidden sm:inline text-sm font-medium">المتجر</span>
                            <span class="bg-blue-500/20 text-blue-400 text-[10px] px-2 py-0.5 rounded-full font-bold hidden sm:block">{{ $storeName }}</span>
                        </button>

                        {{-- قائمة المتجر المنسدلة --}}
                        <div x-show="openStoreMenu"
                             @click.outside="openStoreMenu = false"
                             x-cloak
                             x-transition
                             class="absolute right-0 mt-2 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-3 z-[60] overflow-hidden">

                            {{-- رأس القائمة --}}
                            <div class="px-4 pb-2 border-b border-gray-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center">
                                    <i class="fa-solid fa-store text-blue-400 text-xl"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-sm">{{ $storeName }}</h4>
                                    <p class="text-[10px] text-gray-500">قائمة التنقل السريع</p>
                                </div>
                            </div>

                            {{-- روابط المتجر --}}
                            <div class="p-2">
                                @foreach($storeMenuItems as $item)
                                    @php
                                        $storeRouteParams = array_merge(['store' => $storeId], $item['params'] ?? []);
                                    @endphp
                                    <a href="{{ route($item['route'], $storeRouteParams) }}"
                                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                                              {{ $item['active'] ? "bg-{$item['color']}-600/20 text-{$item['color']}-400 border-r-4 border-{$item['color']}-500" : 'text-gray-300 hover:bg-gray-800' }}">
                                        <i class="fa-solid fa-{{ $item['icon'] }} w-5 text-center"></i>
                                        <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                        @if($item['active'])
                                            <span class="mr-auto text-[10px] bg-{{ $item['color'] }}-500/30 px-2 py-0.5 rounded-full">الحالي</span>
                                        @endif
                                    </a>
                                @endforeach
                                <a href="{{ route('user.stores.reports.index', ['store' => $storeId]) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition
                                          {{ request()->routeIs('user.stores.reports.*') ? 'bg-cyan-600/20 text-cyan-400 border-r-4 border-cyan-500' : 'text-gray-300 hover:bg-gray-800' }}">
                                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                                    <span class="text-sm font-medium">التقارير</span>
                                </a>

                                <div class="border-t border-gray-800 my-2"></div>

                                {{-- إعدادات المتجر --}}
                                <a href="{{ route('user.stores.edit', $storeId) }}"
                                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition text-gray-300 hover:bg-gray-800">
                                    <i class="fa-solid fa-gear w-5 text-center"></i>
                                    <span class="text-sm font-medium">إعدادات المتجر</span>
                                </a>

                                {{-- مبدل المتاجر السريع (الذي طلبته أنت) --}}
                                <div class="mt-3 pt-3 border-t border-gray-800">
                                    <label class="text-[10px] text-gray-500 block px-3 mb-2">تغيير المتجر</label>
                                    <div class="px-2">
                                        <select
                                            onchange="if(this.value) window.location.href = this.value"
                                            class="w-full bg-gray-800 text-white text-sm rounded-lg p-2 border border-gray-700 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                            @foreach($auth->stores as $store)
                                                <option value="{{ route(request()->routeIs('user.stores.reports.*') ? 'user.stores.reports.index' : $storeSwitchRoute, $store->id) }}"
                                                        {{ $store->id == $storeId ? 'selected' : '' }}>
                                                    {{ $store->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ===== الجهة اليمنى: الإشعارات والبروفايل ===== --}}
            <div class="flex items-center gap-4 md:gap-6">
                {{-- زر تبديل النمط --}}
                <button
                    @click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                    class="p-2 text-gray-400 hover:text-white transition rounded-full hover:bg-gray-800">
                    <i class="fa-solid fa-sun dark:hidden"></i>
                    <i class="fa-solid fa-moon hidden dark:block"></i>
                </button>

                {{-- الإشعارات --}}
                <div class="relative">
                    <button @click="openNotif = !openNotif; openUser = false; openMenu = false; openStoreMenu = false"
                            class="text-gray-400 hover:text-white relative transition p-2 hover:bg-gray-800 rounded-lg">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span data-notif-badge
                              class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-gray-900 {{ $unreadCount > 0 ? '' : 'hidden' }}">
                            {{ $unreadCount }}
                        </span>
                    </button>

                    <div x-show="openNotif"
                         @click.outside="openNotif = false"
                         x-cloak
                         x-transition
                         class="absolute left-0 mt-3 w-80 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-3 z-50 overflow-hidden">

                        <div class="px-4 pb-2 border-b border-gray-800 flex justify-between items-center bg-gray-800/20">
                            <h4 class="text-white font-bold text-sm">التنبيهات</h4>
                            <span class="text-[10px] bg-blue-500/20 text-blue-400 px-2 py-0.5 rounded-full font-bold uppercase">جديد</span>
                        </div>

                        <div data-notif-list class="max-h-72 overflow-y-auto custom-scroll">
                            @forelse($latestNotifications as $n)
                                <a href="{{ route('user.notifications.show', $n->id) }}"
                                   class="block px-4 py-3 transition border-b border-gray-800/40 last:border-0 hover:bg-gray-800/70 {{ $n->isReadBy($auth->id) ? 'text-gray-500' : 'text-gray-200 font-semibold bg-blue-500/5' }}">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-lg {{ $n->isReadBy($auth->id) ? 'bg-gray-800' : 'bg-blue-600' }} flex items-center justify-center text-white text-xs shrink-0">
                                            <i class="fa-solid fa-bell"></i>
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-xs line-clamp-1">{{ $n->title }}</div>
                                            <div class="text-[10px] text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">{{ $n->message }}</div>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="px-4 py-8 text-gray-500 text-center text-xs italic">لا توجد إشعارات حالياً</div>
                            @endforelse
                        </div>

                        <div class="mt-2 pt-2 px-3">
                            <a href="{{ route('user.notifications.index') }}" class="block w-full py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg text-center text-[11px] font-bold transition">عرض كل الإشعارات</a>
                        </div>
                    </div>
                </div>

                {{-- البروفايل --}}
                <div class="relative">
                    <button @click="openUser = !openUser; openNotif = false; openMenu = false; openStoreMenu = false"
                            class="flex items-center gap-3 p-1 pr-3 hover:bg-gray-800 rounded-xl transition border border-transparent hover:border-gray-800 group">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-bold text-white group-hover:text-blue-400 transition">{{ $auth->name }}</p>
                            <div class="flex items-center justify-end gap-1.5 mt-0.5">
                                <span class="text-[8px] px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-500 font-black uppercase tracking-tighter">{{ $plan->name ?? 'Basic' }}</span>
                                <span class="text-[9px] text-gray-500 font-medium italic">{{ $auth->subscription_end_at ? \Carbon\Carbon::parse($auth->subscription_end_at)->format('Y-m-d') : '∞' }}</span>
                            </div>
                        </div>
                        <div class="relative">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($auth->name) }}&background=1e293b&color=3b82f6&bold=true"
                                 class="w-9 h-9 rounded-lg border border-gray-700 group-hover:border-blue-500/50 transition shadow-lg"
                                 alt="{{ $auth->name }}"
                                 loading="lazy">
                            <div class="absolute -bottom-1 -left-1 w-2.5 h-2.5 bg-green-500 border-2 border-gray-900 rounded-full"></div>
                        </div>
                    </button>

                    <div x-show="openUser"
                         @click.outside="openUser = false"
                         x-cloak
                         x-transition
                         class="absolute left-0 mt-3 w-56 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-2 z-50 overflow-hidden">

                        <div class="px-4 py-3 bg-gray-800/30 mb-2 border-b border-gray-800">
                            {{-- شريط استهلاك المتاجر --}}
                            <p class="text-[10px] text-gray-500 uppercase font-bold mb-1 text-right">استهلاك المتاجر</p>
                            <div class="w-full bg-gray-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full transition-all duration-700"
                                     style="width: {{ ($currentStores / max(1, $plan->allowed_stores ?? 1)) * 100 }}%"></div>
                            </div>
                            <p class="text-[9px] text-gray-400 mt-1.5 text-right">{{ $currentStores }} من أصل {{ $plan->allowed_stores ?? 0 }}</p>

                            {{-- تاريخ انتهاء الاشتراك --}}
                            <div class="mt-3 pt-3 border-t border-gray-800/50">
                                <p class="text-[10px] text-gray-500 uppercase font-bold mb-1 text-right">صلاحية الاشتراك</p>
                                <div class="flex items-center justify-end gap-2 text-gray-300">
                                    @if($auth->subscription_end_at)
                                        <span class="text-[11px] font-bold {{ \Carbon\Carbon::parse($auth->subscription_end_at)->isFuture() ? 'text-blue-400' : 'text-red-500' }}">
                                            {{ \Carbon\Carbon::parse($auth->subscription_end_at)->translatedFormat('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-[11px] font-bold text-green-500">مفتوح (دائم)</span>
                                    @endif
                                    <i class="fa-solid fa-calendar-day text-[10px] text-gray-600"></i>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-800 my-2"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2 text-red-500 hover:bg-red-500/10 transition text-sm font-bold">
                                <i class="fa-solid fa-power-off w-4 text-center"></i><span>تسجيل الخروج</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== القائمة الرئيسية المنسدلة (تظهر عند الضغط على زر الهمبرجر) ===== --}}
    <div x-show="openMenu"
         @click.outside="openMenu = false"
         x-cloak
         x-transition
         class="border-t border-gray-800 bg-gradient-to-b from-gray-900 via-gray-900 to-gray-950 px-4 py-5 shadow-inner">

        <div class="mx-auto max-w-7xl space-y-5">
        {{-- سطر البطاقات --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            {{-- بطاقة المتاجر --}}
            <div class="p-4 rounded-2xl bg-gray-900/70 border border-gray-800/90 shadow-lg shadow-blue-950/10">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-blue-500/15 flex items-center justify-center ring-1 ring-blue-500/20">
                            <i class="fa-solid fa-store text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">المتاجر</p>
                            <p class="text-sm font-bold text-white">{{ $remainingStores }} متاح</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $currentStores }}/{{ $allowedStores }}</span>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-800/90 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-blue-500 h-full"
                             style="width: {{ ($currentStores / max(1, $allowedStores)) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            {{-- بطاقة المحاسبين --}}
            <div class="p-4 rounded-2xl bg-gray-900/70 border border-gray-800/90 shadow-lg shadow-green-950/10">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-green-500/15 flex items-center justify-center ring-1 ring-green-500/20">
                            <i class="fa-solid fa-user-tie text-green-400"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-bold">المحاسبين</p>
                            <p class="text-sm font-bold text-white">{{ $remainingAccountants }} متاح</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $currentAccountants }}/{{ $plan->allowed_accountants ?? 0 }}</span>
                </div>
                <div class="mt-2">
                    <div class="w-full bg-gray-800/90 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-green-500 h-full"
                             style="width: {{ ($currentAccountants / max(1, $plan->allowed_accountants ?? 1)) * 100 }}%"></div>
                    </div>
                </div>
            </div>

            {{-- بطاقة الموظفين --}}
            <div class="p-4 rounded-2xl bg-gray-900/70 border border-gray-800/90 shadow-lg shadow-purple-950/10">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-purple-500/15 flex items-center justify-center ring-1 ring-purple-500/20">
                        <i class="fa-solid fa-users text-purple-400"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 uppercase font-bold">الموظفين</p>
                        <p class="text-sm font-bold text-white">{{ $auth->employees()->count() }}</p>
                    </div>
                </div>
                <div class="mt-2 text-[10px] text-gray-500">بدون حدود</div>
            </div>

            {{-- بطاقة الإضافة السريعة --}}
            <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-600/15 via-indigo-600/10 to-purple-700/15 border border-blue-700/30 shadow-lg shadow-indigo-950/20">
                <div class="text-center">
                    <p class="text-[10px] text-blue-400 uppercase font-bold mb-2">إضافة سريعة</p>
                    <div class="flex flex-col sm:flex-row gap-2">
                        @if($remainingStores > 0)
                            <a href="{{ route('user.stores.create') }}"
                               class="flex-1 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition text-center">
                                + متجر
                            </a>
                        @endif
                        @if($remainingAccountants > 0)
                            <a href="{{ route('user.accountants.create') }}"
                               class="flex-1 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-xs font-bold transition text-center">
                                + محاسب
                            </a>
                        @endif
                        <a href="{{ route('user.employees.create') }}"
                           class="flex-1 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg text-xs font-bold transition text-center">
                            + موظف
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- روابط التنقل أسفل البطاقات --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('user.stores.index') }}"
               class="group flex flex-col items-center p-3 rounded-xl bg-gray-900/70 border border-gray-800 text-gray-300 transition-all hover:-translate-y-0.5 hover:bg-gray-800/90 hover:border-blue-500/40">
                <i class="fa-solid fa-store text-blue-400 mb-1"></i>
                <span class="text-[11px] font-bold">المتاجر</span>
            </a>

            <a href="{{ route('user.accountants.index') }}"
               class="group flex flex-col items-center p-3 rounded-xl bg-gray-900/70 border border-gray-800 text-gray-300 transition-all hover:-translate-y-0.5 hover:bg-gray-800/90 hover:border-green-500/40">
                <i class="fa-solid fa-user-tie text-green-400 mb-1"></i>
                <span class="text-[11px] font-bold">المحاسبين</span>
            </a>

            <a href="{{ route('user.employees.index') }}"
               class="group flex flex-col items-center p-3 rounded-xl bg-gray-900/70 border border-gray-800 text-gray-300 transition-all hover:-translate-y-0.5 hover:bg-gray-800/90 hover:border-purple-500/40">
                <i class="fa-solid fa-users text-purple-400 mb-1"></i>
                <span class="text-[11px] font-bold">الموظفين</span>
            </a>

            <a href="{{ route('user.notifications.send') }}"
               class="group flex flex-col items-center p-3 rounded-xl bg-gray-900/70 border border-gray-800 text-gray-300 transition-all hover:-translate-y-0.5 hover:bg-gray-800/90 hover:border-cyan-500/40">
                <i class="fa-solid fa-paper-plane text-cyan-400 mb-1"></i>
                <span class="text-[11px] font-bold">إرسال إشعار</span>
            </a>
        </div>

        {{-- إذا كنا داخل متجر، نضيف رابط سريع للمتجر --}}
        @if($isInStore)
        <div class="pt-4 border-t border-gray-800">
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">أنت الآن في:</span>
                <a href="{{ route('user.stores.show', $storeId) }}" class="flex items-center gap-2 text-blue-400 hover:text-blue-300 transition">
                    <i class="fa-solid fa-store"></i>
                    <span class="text-sm font-bold">{{ $storeName ?? 'المتجر' }}</span>
                </a>
            </div>
        </div>
        @endif
        </div>
    </div>
</nav>

<style>
    .custom-scroll::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.5);
        border-radius: 2px;
    }
</style>


<script>
    // إشعارات الوقت الفعلي
    if (window.Echo) {
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
                    item.className = 'px-4 py-3 hover:bg-gray-800/70 border-b border-gray-800/40 cursor-pointer text-gray-200';
                    item.innerHTML = `<div class="text-xs font-bold">${e.title}</div><div class="text-[10px] text-gray-500 mt-1">${e.message}</div>`;
                    list.prepend(item);
                }
            });
    }

    // استرجاع الثيم المحفوظ
    document.addEventListener('alpine:init', () => {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        }
    });
</script>
