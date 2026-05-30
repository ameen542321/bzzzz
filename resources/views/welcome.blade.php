<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carled </title>
{{-- @vite('resources/css/app.css') --}}
<script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet" href="/css/app.css">
</head>

<body class="bg-gray-900 text-gray-200">

    {{-- القسم الرئيسي --}}
    <section class="min-h-screen flex flex-col items-center justify-center text-center px-6">

        <h1 class="text-6xl font-extrabold text-blue-400 mb-6 tracking-wide">
            CARLED
        </h1>

        <p class="text-gray-300 text-xl max-w-2xl leading-relaxed mb-10">
            منصة متكاملة لإدارة المتاجر، المحاسبة، الرواتب، المصاريف، الاشتراكات،
            وإدارة الموظفين — كل ذلك في نظام واحد بسيط، سريع، واحترافي.
        </p>

       <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit"
        class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-3 rounded-lg text-lg shadow-lg transition">
        تسجيل الدخول
    </button>
</form>


        <div class="mt-16 animate-bounce text-gray-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

    </section>

    {{-- الخدمات --}}
    <section class="py-20 px-6 bg-gray-800 border-t border-gray-700">

        <h2 class="text-4xl font-bold text-center text-blue-400 mb-12">
            خدمات Carled
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 max-w-7xl mx-auto">

            {{-- خدمة 1 --}}
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center hover:border-blue-500 transition">
                <div class="mb-4">
                    <svg class="w-12 h-12 mx-auto text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 7h18M3 12h18M3 17h18"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold mb-4">إدارة المتاجر</h3>
                <p class="text-gray-400 leading-relaxed">
                    إدارة كاملة للمتاجر والفروع مع صلاحيات دقيقة لكل مستخدم.
                </p>
            </div>

            {{-- خدمة 2 --}}
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center hover:border-blue-500 transition">
                <div class="mb-4">
                    <svg class="w-12 h-12 mx-auto text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2v6h6v-6c0-1.105-1.343-2-3-2z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold mb-4">المحاسبة والمصاريف</h3>
                <p class="text-gray-400 leading-relaxed">
                    تتبع المصاريف، الرواتب، السحوبات، والتقارير المالية بشكل احترافي.
                </p>
            </div>

            {{-- خدمة 3 --}}
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center hover:border-blue-500 transition">
                <div class="mb-4">
                    <svg class="w-12 h-12 mx-auto text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5.121 17.804A4 4 0 0112 15a4 4 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold mb-4">إدارة الموظفين</h3>
                <p class="text-gray-400 leading-relaxed">
                    إدارة العمال، الرواتب، السجلات، السحوبات، والمهام اليومية بسهولة.
                </p>
            </div>

            {{-- خدمة 4 --}}
            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center hover:border-blue-500 transition">
                <div class="mb-4">
                    <svg class="w-12 h-12 mx-auto text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold mb-4">الاشتراكات</h3>
                <p class="text-gray-400 leading-relaxed">
                    خطط اشتراك مرنة تناسب حجم متجرك مع إمكانية الترقية بسهولة.
                </p>
            </div>

        </div>

    </section>

    {{-- لماذا Carled --}}
    <section class="py-20 px-6">

        <h2 class="text-4xl font-bold text-center text-blue-400 mb-12">
            لماذا Carled؟
        </h2>

        <div class="max-w-4xl mx-auto text-center text-gray-300 text-lg leading-relaxed">
            Carled يجمع كل ما يحتاجه صاحب المتجر في منصة واحدة:
            إدارة، محاسبة، رواتب، مصاريف، اشتراكات، موظفين، تقارير، وإشعارات —
            بدون تعقيد، بدون إضافات غير ضرورية، وبدون تكلفة عالية.
        </div>

    </section>

    {{-- مميزات النظام --}}
    <section class="py-20 px-6 bg-gray-800 border-t border-gray-700">

        <h2 class="text-4xl font-bold text-center text-blue-400 mb-12">
            مميزات النظام
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center">
                <h3 class="text-xl font-semibold mb-3">سهولة الاستخدام</h3>
                <p class="text-gray-400">واجهة بسيطة وسريعة بدون أي تعقيد.</p>
            </div>

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center">
                <h3 class="text-xl font-semibold mb-3">سرعة عالية</h3>
                <p class="text-gray-400">أداء ممتاز حتى مع البيانات الكبيرة.</p>
            </div>

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center">
                <h3 class="text-xl font-semibold mb-3">تقارير دقيقة</h3>
                <p class="text-gray-400">تحليل شامل لكل عملياتك المالية والإدارية.</p>
            </div>

        </div>

    </section>

    {{-- آراء العملاء --}}
    <section class="py-20 px-6">

        <h2 class="text-4xl font-bold text-center text-blue-400 mb-12">
            آراء العملاء
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">
                <p class="text-gray-300 mb-4">
                    "بفضل استخدامي للبرنامج لم يعد  يكفيني عصبة من الرجال لحمل مفاتيح خزائني  ."
                </p>
                <p class="text-blue-400 font-semibold">—   قارون</p>
            </div>

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">
                <p class="text-gray-300 mb-4">
                    " لا باس به فلقد ساعدنا في جمع الثروات والغنائم لنقاتل محمدا ومن صبا من قومة  ."
                </p>
                <p class="text-blue-400 font-semibold">— ابو جهل </p>
            </div>

            <div class="bg-gray-900 border border-gray-700 rounded-xl p-6">
                <p class="text-gray-300 mb-4">
                    "لقد استطعت ادارة قوافلي شتاء وصيفا الا القافلة التي اخذها محمدا وصحبة"
                </p>
                <p class="text-blue-400 font-semibold">—  الوليد ابن المغيرة</p>
            </div>

        </div>

    </section>

    {{-- الفوتر --}}
    <footer class="py-10 text-center text-gray-400 border-t border-gray-800">

        <div class="flex flex-col md:flex-row justify-center gap-8 mb-6 text-lg">

            <a href="#" class="hover:text-blue-400 transition">من نحن</a>
            <a href="#" class="hover:text-blue-400 transition">الشروط والأحكام</a>
            <a href="#" class="hover:text-blue-400 transition">سياسة الخصوصية</a>
            <a href="#" class="hover:text-blue-400 transition">تواصل معنا</a>

        </div>

        <p class="text-gray-500 text-sm">
            © {{ date('Y') }} Carled — جميع الحقوق محفوظة
        </p>

    </footer>

</body>
</html>
