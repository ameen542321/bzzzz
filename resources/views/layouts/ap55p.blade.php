<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Alpine --}}
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    [x-cloak] { display: none !important; }
</style>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>{{ $title ?? 'Carled Dashboard' }}</title>


    {{-- ✅ تحميل Tailwind + app.js عبر Vite فقط --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
</head>

<body class="bg-gray-900 text-gray-100">

    {{-- Navbar --}}
    @include('layouts.partials.navbar')

    <div class="flex">

       

        {{-- Main Content --}}
        <main id="mainContent" class="flex-1 p-6 transition-all duration-300 mt-16">
    @auth
    @if(auth()->user()->status === 'active' && auth()->user()->subscription_end_at)
        @php
            // حساب الأيام بشكل صحيح بدون كسور
            $daysLeft = now()->startOfDay()->diffInDays(
                \Carbon\Carbon::parse(auth()->user()->subscription_end_at)->startOfDay(),
                false
            );

            // دالة تصريف كلمة "يوم" بالعربي
            function arabicDays($days) {
                if ($days == 1) return "يوم واحد";
                if ($days == 2) return "يومان";
                if ($days >= 3 && $days <= 10) return "$days أيام";
                return "$days يوم";
            }
        @endphp

        @if($daysLeft <= 7 && $daysLeft >= 0)
            <div class="w-full bg-amber-100 border-b border-amber-300 text-amber-800 text-sm py-2 text-center">
                تنبيه: متبقي {{ arabicDays($daysLeft) }} على انتهاء اشتراكك.
            </div>
        @endif
    @endif
@endauth



            @yield('content')
            <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
        تسجيل الخروج
    </button>
</form>
        </main>


    </div>



</body>
</html>
