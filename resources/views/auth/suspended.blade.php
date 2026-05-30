<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تم إيقاف حسابك</title>

    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Tajawal", sans-serif;
            background: #0f1115;
            color: #fff;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .box {
            background: #1b1d21;
            border: 1px solid #2a2d31;
            padding: 40px;
            border-radius: 16px;
            width: 380px;
            text-align: center;
            box-shadow: 0 0 25px rgba(0,0,0,0.25);
        }

        .icon {
            font-size: 70px;
            color: #ff4d4f;
            margin-bottom: 15px;
        }

        h1 {
            font-size: 26px;
            margin-bottom: 10px;
            color: #fff;
        }

        p {
            color: #b5b5b5;
            line-height: 1.8;
            margin-bottom: 25px;
        }

        a, button {
            display: inline-block;
            padding: 12px 22px;
            background: #ff4d4f;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        a:hover, button:hover {
            background: #e04345;
        }

        form {
            margin: 0;
        }
    </style>
</head>
<body>

<div class="box">

    <div class="icon">⛔</div>

    <h1>تم إيقاف حسابك</h1>

    <p>
        تم إيقاف حسابك من قبل الإدارة.<br>
        إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع الدعم.
    </p>

    @guest
        <a href="/">العودة للصفحة الرئيسية</a>
    @endguest

    @auth
        <form id="logout-form" action="{{ route('accountant.logout') }}" method="POST">
            @csrf
            <button type="submit">تسجيل الخروج</button>
        </form>
    @endauth

</div>

</body>
</html>
