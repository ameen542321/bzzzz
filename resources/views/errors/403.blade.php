<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>403 - غير مسموح بالوصول</title>

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

        /* الرقم 403 مع أنيميشن */
        h1 {
            font-size: 120px;
            font-weight: 900;
            color: #f59e0b; /* أصفر تحذيري */
            margin: 0;
            animation: pulse 2.2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%   { transform: scale(1); }
            50%  { transform: scale(1.07); }
            100% { transform: scale(1); }
        }

        p {
            font-size: 22px;
            margin-top: 10px;
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
        .back  { background: #475569; }
        .login { background: #0ea5e9; }

        .btn:hover {
            opacity: .85;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 600px) {
            h1 { font-size: 80px; }
            p { font-size: 18px; }
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
    <h1>403</h1>
    <p>عذراً، لا تملك صلاحية الوصول إلى هذه الصفحة.</p>

    <div class="buttons">
        <a href="{{ url('/') }}" class="btn home">الصفحة الرئيسية</a>
        <a href="javascript:history.back()" class="btn back">رجوع للخلف</a>
        <a href="{{ route('login') }}" class="btn login">تسجيل الدخول</a>
    </div>
</div>

</body>
</html>
