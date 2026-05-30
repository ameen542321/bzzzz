@php
    use Illuminate\Support\Facades\Auth;
    use App\Models\Notification;

    $auth = Auth::guard('accountant')->user();

    // ⭐⭐ بنفس النمط الذي طلبته
    $latestNotifications = $auth->notificationsForAccountant()->take(5)->get();
    $unreadCount = $auth->unreadNotificationsCountForAccountant();
@endphp

<nav class="bg-gray-900 border-b border-gray-800 sticky top-0 z-50"
     x-data="{ openMenu: false, openUser: false, openNotif: false }">

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center gap-3 sm:gap-6">
                {{-- زر القائمة للجوال --}}
               <button @click="openMenu = !openMenu" type="button" aria-label="فتح القائمة" class="inline-flex items-center justify-center text-white hover:text-white transition focus:outline-none group relative p-1">
                    {{-- أيقونة SVG كحل ثابت حتى لو Font Awesome لم يُحمَّل --}}
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                    </svg>
                    {{-- توليب لزر القائمة --}}
                    <span class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                        القائمة الرئيسية
                    </span>
                </button>

                {{-- شعار CARLED: تم وضعه هنا ليكون أول ما تقع عليه العين في القراءة العربية --}}
                <a href="{{ route('accountant.dashboard') }}" class="flex items-center gap-2 group">
                    <div class="relative">
                        <div class="w-3 h-3 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6] group-hover:scale-125 transition-transform"></div>
                        <div class="absolute inset-0 w-3 h-3 rounded-full bg-blue-400 animate-ping opacity-20"></div>
                    </div>
                    <span class="text-white font-black text-xl tracking-wider uppercase">Car<span class="text-blue-500">led</span></span>
                </a>
            </div>
            {{-- زر تبديل النمط - يوضع بجانب الإشعارات --}}
            <button
                type="button"
                aria-label="تبديل النمط"
                @click="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')"
                class="inline-flex items-center justify-center p-2 text-white hover:text-white transition rounded-full hover:bg-gray-800 group relative shrink-0"
            >
                {{-- أيقونة SVG ثابتة لتبديل النمط بدون الاعتماد على Font Awesome --}}
                <svg class="w-5 h-5 dark:hidden" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <svg class="w-5 h-5 hidden dark:block" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 1 0 9.8 9.8Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
                {{-- توليب لزر تبديل النمط --}}
                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                    تبديل الوضع
                </span>
            </button>
            <div class="flex items-center gap-4">


                {{-- الإشعارات --}}
                <div class="relative">
                    <button
                        @click="openNotif = !openNotif; openUser = false"
                        class="p-2 text-gray-400 hover:text-white relative transition rounded-full hover:bg-gray-800 group"
                    >
                        <i class="fa-regular fa-bell text-xl"></i>
                        @if($unreadCount > 0)
                            <span class="absolute top-1.5 right-1.5 bg-red-600 text-white text-[10px] font-bold min-w-[18px] h-[18px] flex items-center justify-center rounded-full border-2 border-gray-900 animate-bounce">
                                {{ $unreadCount }}
                            </span>
                        @endif
                        {{-- توليب لزر الإشعارات --}}
                        <span class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                            الإشعارات
                        </span>
                    </button>

                    {{-- قائمة الإشعارات المنسدلة --}}
                    <div
                        x-show="openNotif"
                        @click.outside="openNotif = false"
                        x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="absolute left-0 mt-3 w-80 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden z-50"
                    >
                        <div class="p-4 border-b border-gray-800 flex justify-between items-center">
                            <h4 class="text-white font-bold">الإشعارات</h4>
                            <span class="text-xs bg-gray-800 text-gray-400 px-2 py-1 rounded-md">{{ $unreadCount }} جديدة</span>
                        </div>

                        <div class="max-h-[400px] overflow-y-auto custom-scroll">
                            @forelse($latestNotifications as $n)
                                <a href="{{ route('accountant.notifications.show', $n->id) }}"
                                   class="flex items-start gap-3 p-4 border-b border-gray-800/50 hover:bg-gray-800/40 transition {{ $n->isReadBy($auth->id) ? 'opacity-60' : 'border-r-4 border-r-blue-500 bg-blue-500/5' }}">
                                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex-shrink-0 flex items-center justify-center text-blue-500">
                                        <i class="fa-solid fa-circle-info"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-gray-200 font-medium leading-tight">{{ $n->title }}</p>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2">{{ $n->message }}</p>
                                        <span class="text-[10px] text-gray-600 mt-2 block">{{ $n->created_at->diffForHumans() }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="p-10 text-center">
                                    <i class="fa-solid fa-bell-slash text-gray-700 text-3xl mb-3 block"></i>
                                    <p class="text-gray-500 text-sm">لا توجد إشعارات حالياً</p>
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ route('accountant.notifications.index') }}" class="block p-3 text-center text-xs font-bold text-blue-500 hover:bg-gray-800 transition">
                            عرض الكل
                        </a>
                    </div>
                </div>

                {{-- بروفايل المستخدم --}}
                <div class="relative">
                    <button @click="openUser = !openUser; openNotif = false" class="flex items-center gap-2 p-1 pr-3 hover:bg-gray-800 rounded-full transition border border-transparent hover:border-gray-700 group relative">
                       <div class="text-right hidden sm:block">
    <p class="text-xs font-bold text-white leading-none">{{ $auth->name }}</p>
    {{-- إظهار اسم المتجر المرتبط --}}
    <p class="text-[10px] text-blue-400 mt-1 font-medium">
        <i class="fa-solid fa-store text-[9px] mr-1"></i>
        {{ $auth->store->name ?? 'محاسب النظام' }}
    </p>
</div>
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($auth->name) }}&background=0D8ABC&color=fff" class="w-8 h-8 rounded-full shadow-inner">
                        {{-- توليب لزر البروفايل --}}
                        <span class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                            حسابي
                        </span>
                    </button>

                    <div x-show="openUser" @click.outside="openUser = false" x-cloak x-transition class="absolute left-0 mt-3 w-52 bg-gray-900 border border-gray-800 rounded-xl shadow-2xl py-2 z-50">

                        <div class="border-t border-gray-800 my-2"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition">
                                <i class="fa-solid fa-power-off"></i> تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- قائمة الجوال --}}
   <div x-show="openMenu" x-cloak x-transition class="bg-gray-900 border-t border-gray-800 px-4 py-6 space-y-4 shadow-2xl fixed top-16 inset-x-0 z-[70]">
    {{-- معلومات المحاسب --}}


    {{-- القائمة الرئيسية للمحاسب --}}
   <div class="grid grid-cols-2 gap-2">

    {{-- ⭐ البيع السريع --}}
    <a href="{{ route('accountant.quick-sale.index') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-emerald-900/40 to-emerald-900/20 border border-emerald-800/50 text-white transition-all hover:from-emerald-900/60 hover:to-emerald-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-bolt text-emerald-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">بيع سريع</span>
        {{-- توليب للبيع السريع --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            بيع منتج بسرعة
        </span>
    </a>

    {{-- ⭐ تسجيل استهلاك (المضافة) --}}
    <a href="{{ route('accountant.internal-use.create') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-purple-900/40 to-purple-900/20 border border-purple-800/50 text-white transition-all hover:from-purple-900/60 hover:to-purple-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-box-open text-purple-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">تسجيل استهلاك</span>
        {{-- توليب لتسجيل استهلاك --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            استهلاك داخلي للمواد
        </span>
    </a>

    {{-- ⭐ إضافة مصروف --}}
    <a href="{{ route('accountant.pos.expense.page') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-rose-900/40 to-rose-900/20 border border-rose-800/50 text-white transition-all hover:from-rose-900/60 hover:to-rose-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-money-bill-transfer text-rose-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">إضافة مصروف</span>
        {{-- توليب لإضافة مصروف --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            تسجيل مصروف جديد
        </span>
    </a>

    {{-- ⭐ بيع آجل --}}
    <a href="{{ route('accountant.pos.credit-sale.page') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-amber-900/40 to-amber-900/20 border border-amber-800/50 text-white transition-all hover:from-amber-900/60 hover:to-amber-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-credit-card text-amber-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">بيع آجل</span>
        {{-- توليب للبيع الآجل --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            مبيعات آجلة
        </span>
    </a>

    {{-- ⭐ سحب نقدي --}}
    <a href="{{ route('accountant.pos.withdrawal.page') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-violet-900/40 to-violet-900/20 border border-violet-800/50 text-white transition-all hover:from-violet-900/60 hover:to-violet-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-hand-holding-dollar text-violet-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">سحب نقدي</span>
        {{-- توليب للسحب النقدي --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            سحب من الصندوق
        </span>
    </a>

    {{-- ⭐ تسجيل غياب --}}
    <a href="{{ route('accountant.pos.absence.page') }}" class="p-4 bg-gray-800 border border-gray-700 rounded-xl text-center hover:bg-gray-750 hover:border-orange-500 transition-all group relative">
        <div class="bg-orange-500/15 text-orange-300 p-3 rounded-lg inline-block group-hover:scale-110 transition">
            <i class="fa-solid fa-user-xmark text-lg"></i>
        </div>
        <p class="text-xs text-gray-300 mt-2">غياب</p>
        {{-- توليب لتسجيل الغياب --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            تسجيل غياب الموظفين
        </span>
    </a>

    {{-- ⭐ المبيعات اليومية (إضافة مديونية) --}}
    <a href="{{ route('accountant.pos.debt.page') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-blue-900/40 to-blue-900/20 border border-blue-800/50 text-white transition-all hover:from-blue-900/60 hover:to-blue-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-money-bill-transfer text-blue-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">إضافة مديونية</span>
        {{-- توليب لإضافة مديونية --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            إضافة دين على موظف
        </span>
    </a>

    {{-- ⭐ المخزون --}}
    <a href="{{ route('accountant.pos.searchProduct') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-cyan-900/40 to-cyan-900/20 border border-cyan-800/50 text-white transition-all hover:from-cyan-900/60 hover:to-cyan-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-boxes-stacked text-cyan-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">المنتجات</span>
        {{-- توليب للمخزون --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            البحث في المخزون
        </span>
    </a>

    {{-- ⭐ تحصيل --}}
    <a href="{{ route('accountant.pos.collection.page') }}" class="p-4 bg-gray-800 border border-gray-700 rounded-xl text-center hover:bg-gray-750 hover:border-green-500 transition-all group relative">
        <div class="bg-green-500/15 text-green-300 p-3 rounded-lg inline-block group-hover:scale-110 transition">
            <i class="fa-solid fa-money-check-dollar text-lg"></i>
        </div>
        <p class="text-xs text-gray-300 mt-2">تحصيل</p>
        {{-- توليب للتحصيل --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            تحصيل دفعات من العملاء
        </span>
    </a>

    {{-- ⭐ الفواتير --}}
    <a href="{{ route('accountant.invoices.index') }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-gradient-to-br from-indigo-900/40 to-indigo-900/20 border border-indigo-800/50 text-white transition-all hover:from-indigo-900/60 hover:to-indigo-900/40 active:scale-95 shadow-md group relative">
        <i class="fa-solid fa-file-invoice text-indigo-400 text-xl"></i>
        <span class="text-[11px] font-bold uppercase">الفواتير</span>
        {{-- توليب للفواتير --}}
        <span class="absolute -top-8 left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
            عرض كل الفواتير
        </span>
    </a>
</div>

</div>

</nav>
