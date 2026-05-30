@extends('layouts.app')

@section('content')
<div class="text-center py-20">
    <h1 class="text-3xl font-bold text-red-600 mb-4">عذراً، تم إيقاف هذا المتجر</h1>
    <p class="text-gray-600">لا يمكنك الوصول إلى هذا المتجر حالياً.</p>
</div>
@endsection
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>المتجر موقوف</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f172a; /* خلفية داكنة */
            font-family: "Tajawal", sans-serif;
            color: #e2e8f0;
            text-align: center;
        }

        /* الشعار */
        .logo {
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .logo img {
            width: 130px;
            opacity: 0.95;
        }

        .container {
            margin-top: 5vh;
            padding: 0 20px;
        }

        h1 {
            font-size: 36px;
            font-weight: 800;
            color: #ef4444; /* أحمر تحذيري */
            margin-bottom: 15px;
        }

        p {
            font-size: 20px;
            color: #cbd5e1;
        }

        .buttons {
            margin-top: 35px;
        }

        .btn {
            display: inline-block;
            padding: 12px 28px;
            margin: 8px;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            font-size: 18px;
            transition: 0.2s;
        }

        .home  { background: #3b82f6; }
        .login { background: #0ea5e9; }
        .back  { background: #475569; }

        .btn:hover {
            opacity: .85;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 600px) {
            h1 { font-size: 28px; }
            p { font-size: 17px; }
            .btn { font-size: 16px; padding: 10px 20px; }
            .logo img { width: 110px; }
        }
    </style>
</head>

<body>

<!-- شعار CARLED -->
<div class="logo">
    {{-- <img src="/carled-logo.png" alt="CARLED Logo"> --}}
</div>

<div class="container">
    <h1>عذراً، تم إيقاف هذا المتجر</h1>
    <p>لا يمكنك الوصول إلى هذا المتجر حالياً.</p>

    <div class="buttons">
        <a href="{{ url('/') }}" class="btn home">الصفحة الرئيسية</a>
        <a href="javascript:history.back()" class="btn back">رجوع للخلف</a>
        <a href="{{ route('login') }}" class="btn login">تسجيل الدخول</a>
    </div>
</div>

</body>
</html>
