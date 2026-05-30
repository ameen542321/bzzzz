<div class="relative">

    {{-- أيقونة الجرس --}}
    <button id="notifBtn" class="relative focus:outline-none">

        {{-- الجرس --}}
        <svg class="w-7 h-7 text-gray-300 hover:text-white transition" fill="none"
             stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002
                     6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388
                     6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3
                     3 0 11-6 0v-1m6 0H9" />
        </svg>

        {{-- عدد الإشعارات غير المقروءة --}}
        <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs px-1.5 py-0.5 rounded-full">
            {{ $unreadCount ?? 0 }}
        </span>

    </button>

    {{-- القائمة المنسدلة --}}
    <div id="notifDropdown"
         class="hidden absolute left-0 mt-3 w-80 bg-gray-800 border border-gray-700 rounded-lg shadow-lg z-50">

        <div class="p-3 border-b border-gray-700 text-gray-300 font-semibold">
            الإشعارات
        </div>

        {{-- قائمة الإشعارات --}}
        <div class="max-h-64 overflow-y-auto">

            {{-- مثال (لاحقًا foreach) --}}
            <a href=""
               class="block px-4 py-3 border-b border-gray-700 hover:bg-gray-700 transition">

                <p class="text-gray-200 font-semibold">تم إغلاق راتب شهر فبراير</p>
                <p class="text-gray-400 text-sm">منذ 3 ساعات</p>

            </a>

            <a href="{{ route('notifications.show', 2) }}"
               class="block px-4 py-3 border-b border-gray-700 hover:bg-gray-700 transition">

                <p class="text-yellow-400 font-semibold">تم إضافة مصروف جديد</p>
                <p class="text-gray-400 text-sm">منذ 10 دقائق</p>

            </a>

        </div>

        {{-- زر عرض الكل --}}
        <div class="p-3 text-center">
            <a href="{{ route('notifications.index') }}"
               class="text-blue-400 hover:underline">
                عرض جميع الإشعارات
            </a>
        </div>

    </div>

</div>

{{-- سكربت بسيط لفتح/إغلاق القائمة --}}
<script>
    document.getElementById('notifBtn').addEventListener('click', function () {
        document.getElementById('notifDropdown').classList.toggle('hidden');
    });

    // إغلاق القائمة عند الضغط خارجها
    document.addEventListener('click', function (e) {
        const btn = document.getElementById('notifBtn');
        const dropdown = document.getElementById('notifDropdown');

        if (!btn.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
</script>
