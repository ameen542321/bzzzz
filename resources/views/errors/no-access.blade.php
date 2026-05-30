<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>لا تملك صلاحية الوصول</title>
    <style>
        body {
            font-family: "Tajawal", sans-serif;
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            width: 380px;
        }
        .icon {
            font-size: 60px;
            color: #e74c3c;
        }
        h1 {
            margin: 20px 0 10px;
            color: #333;
        }
        p {
            color: #666;
            margin-bottom: 25px;
        }
        a, button {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }
        a:hover, button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>

<div class="box">
    <div class="icon">🚫</div>
    <h1>لا تملك صلاحية الوصول</h1>
    <p>عذرًا، لا يمكنك الوصول لهذه الصفحة.</p>

    @guest
        <!-- المستخدم غير مسجّل دخول -->
        <a href="/">العودة للصفحة الرئيسية</a>
    @endguest

    @auth
        <!-- المستخدم مسجّل دخول -->
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">تسجيل خروج والعودة للصفحة الرئيسية</button>
        </form>
    @endauth
</div>

</body>
</html>
