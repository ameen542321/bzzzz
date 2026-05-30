<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js + Collapse --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

    @vite('resources/css/app.css')

    <title>CARLED - تسجيل الدخول</title>

    <style>
        body {
            background: #0f172a; /* dark navy */
            font-family: 'Tajawal', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center px-4">




{{-- ========================= --}}
{{-- PAGE CONTENT --}}
{{-- ========================= --}}
<div class="w-full max-w-md bg-[#1e293b] rounded-xl shadow-xl p-8 text-white">
    @yield('content')
</div>

</body>
</html>
