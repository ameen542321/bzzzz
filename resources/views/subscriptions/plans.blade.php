@extends('dashboard.app')

@section('content')

<h1 class="text-3xl font-semibold mb-10 text-center">خطط الاشتراك</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    {{-- الخطة الأساسية --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 flex flex-col">
        <h2 class="text-xl font-semibold text-gray-200 mb-2">الخطة الأساسية</h2>
        <p class="text-gray-400 mb-4">مناسبة للمشاريع الصغيرة</p>

        <div class="text-4xl font-bold text-blue-400 mb-6">49 ريال</div>

        <ul class="text-gray-300 space-y-2 mb-6">
            <li>✔ متجر واحد</li>
            <li>✔ 2 مستخدمين</li>
            <li>✔ 500 منتج</li>
            <li>✔ فواتير غير محدودة</li>
            <li>✔ دعم عبر البريد</li>
        </ul>

        <a href="{{ route('subscriptions.show', 'basic') }}"
           class="mt-auto bg-blue-600 hover:bg-blue-700 text-center py-2 rounded text-white">
            اشترك الآن
        </a>
    </div>

    {{-- الخطة المتقدمة --}}
    <div class="bg-gray-900 border border-blue-600 rounded-xl p-6 flex flex-col shadow-lg shadow-blue-900/30">
        <h2 class="text-xl font-semibold text-gray-200 mb-2">الخطة المتقدمة</h2>
        <p class="text-gray-400 mb-4">الأكثر استخدامًا</p>

        <div class="text-4xl font-bold text-blue-400 mb-6">99 ريال</div>

        <ul class="text-gray-300 space-y-2 mb-6">
            <li>✔ 3 متاجر</li>
            <li>✔ 5 مستخدمين</li>
            <li>✔ منتجات غير محدودة</li>
            <li>✔ فواتير غير محدودة</li>
            <li>✔ دعم فني أسرع</li>
        </ul>

        <a href="{{ route('subscriptions.show', 'pro') }}"
           class="mt-auto bg-blue-600 hover:bg-blue-700 text-center py-2 rounded text-white">
            اشترك الآن
        </a>
    </div>

    {{-- الخطة الاحترافية --}}
    <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 flex flex-col">
        <h2 class="text-xl font-semibold text-gray-200 mb-2">الخطة الاحترافية</h2>
        <p class="text-gray-400 mb-4">للشركات والمتاجر الكبيرة</p>

        <div class="text-4xl font-bold text-blue-400 mb-6">199 ريال</div>

        <ul class="text-gray-300 space-y-2 mb-6">
            <li>✔ عدد متاجر غير محدود</li>
            <li>✔ عدد مستخدمين غير محدود</li>
            <li>✔ منتجات غير محدودة</li>
            <li>✔ دعم VIP</li>
            <li>✔ مميزات إضافية</li>
        </ul>

        <a href="{{ route('subscriptions.show', 'enterprise') }}"
           class="mt-auto bg-blue-600 hover:bg-blue-700 text-center py-2 rounded text-white">
            اشترك الآن
        </a>
    </div>

</div>

@endsection
