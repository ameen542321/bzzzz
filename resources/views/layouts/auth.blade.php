<!DOCTYPE html>
<html lang="ar" dir="rtl" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>{{ $title ?? 'CARLED - تسجيل الدخول' }}</title>
</head>

<body class="auth-surface min-h-screen px-4 py-10 text-white">
    <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-24 right-1/2 h-72 w-72 translate-x-1/2 rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
    </div>

    <main class="relative z-10 flex min-h-[calc(100vh-5rem)] items-center justify-center">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>
</body>
</html>
