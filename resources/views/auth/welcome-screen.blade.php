<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مرحباً بك</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #0f1115;
            font-family: "Tajawal", sans-serif;
            color: #e5e7eb;
        }

        .welcome-wrapper {
            max-width: 600px;
            margin: 70px auto;
            background: #1a1d23;
            border-radius: 20px;
            padding: 45px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.4);
            animation: fadeIn 0.6s ease;
        }

        h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
        }

        p.subtitle {
            text-align: center;
            color: #9ca3af;
            margin-top: 10px;
            font-size: 15px;
        }

        .progress-container {
            margin-top: 40px;
        }

        .progress-bg {
            width: 100%;
            height: 10px;
            background: #2a2d33;
            border-radius: 10px;
        }

        .progress-bar {
            width: 0%;
            height: 10px;
            background: linear-gradient(90deg, #3b82f6, #2563eb);
            border-radius: 10px;
            transition: width 0.2s ease;
        }

        .loading-text {
            text-align: center;
            color: #9ca3af;
            margin-top: 12px;
            font-size: 14px;
        }

        .skip-btn {
            margin-top: 35px;
            display: none; /* مخفي في البداية */
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 2px solid #3b82f6;
            color: #3b82f6;
            border-radius: 10px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.2s;
        }

        .skip-btn:hover {
            background: #3b82f6;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

<div class="welcome-wrapper">

    <h2>مرحباً {{ auth()->user()->name }} 👋</h2>

    <p class="subtitle">يتم تجهيز حسابك الآن…</p>

    {{-- Progress Bar --}}
    <div class="progress-container">
        <div class="progress-bg">
            <div id="welcomeProgress" class="progress-bar"></div>
        </div>
        <p class="loading-text">جاري تجهيز حسابك…</p>
    </div>

    {{-- زر تخطي --}}
    <form id="welcomeContinueForm" method="POST" action="{{ route('welcome.continue') }}">
        @csrf
        <button id="skipBtn" class="skip-btn">تخطي الآن</button>
    </form>

</div>

<script>
    let progress = 0;
    const bar = document.getElementById('welcomeProgress');
    const skipBtn = document.getElementById('skipBtn');

    const interval = setInterval(() => {
        progress += 5;
        bar.style.width = progress + '%';

        if (progress >= 100) {
            clearInterval(interval);

            // إظهار زر التخطي بعد اكتمال الشريط
            skipBtn.style.display = 'block';

            // إرسال تلقائي
            document.getElementById('welcomeContinueForm').submit();
        }
    }, 100);
</script>

</body>
</html>
