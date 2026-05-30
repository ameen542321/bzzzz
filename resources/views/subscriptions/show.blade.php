@extends('dashboard.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تفاصيل الخطة</h1>

<div class="bg-gray-800 border border-gray-700 rounded-xl p-6 max-w-2xl mx-auto">

    {{-- اسم الخطة --}}
    <h2 class="text-3xl font-bold text-blue-400 mb-4">
        {{ $plan->name ?? 'اسم الخطة' }}
    </h2>

    {{-- السعر --}}
    <p class="text-gray-300 text-xl mb-6">
        السعر: <span class="text-blue-400 font-semibold">{{ $plan->price }} ريال</span>
    </p>

    {{-- المميزات --}}
    <h3 class="text-gray-200 text-lg font-semibold mb-3">المميزات:</h3>

    <ul class="text-gray-300 space-y-2 mb-6">
        <li>✔ عدد المتاجر: {{ $plan->stores_limit }}</li>
        <li>✔ عدد المستخدمين: {{ $plan->users_limit }}</li>
        <li>✔ عدد المنتجات: {{ $plan->products_limit }}</li>
        <li>✔ فواتير غير محدودة</li>
        <li>✔ دعم فني</li>
    </ul>

    {{-- ملاحظة مهمة --}}
    <div class="bg-gray-900 border border-gray-700 rounded p-4 text-gray-400 text-sm mb-6">
        ملاحظة: تفعيل الاشتراك يتم فقط من قبل <span class="text-blue-400">المدير العام</span>
        بعد اختيار المستخدم المناسب.
    </div>

    {{-- زر رجوع --}}
    <div class="flex justify-end">
        <a href="{{ route('subscriptions.plans') }}"
           class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white">
            رجوع إلى الخطط
        </a>
    </div>

</div>

@endsection
