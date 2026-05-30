@extends('dashboard.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 px-4">

    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-xl p-8 max-w-md w-full text-center">

        <div class="mb-6">
            <div class="mx-auto w-16 h-16 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center">
                <span class="text-red-600 dark:text-red-300 text-3xl">!</span>
            </div>
        </div>

        <h1 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-3">
            انتهت مدة اشتراكك
        </h1>

        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed mb-6">
            لا يمكنك استخدام النظام في الوقت الحالي لأن اشتراكك قد انتهى.
            يرجى تجديد الاشتراك للمتابعة في استخدام خدمات المنصة.
        </p>

        <a href="{{ route('subscription.renew') }}"
           class="inline-block w-full py-2.5 bg-gray-800 dark:bg-gray-700 text-white rounded-lg hover:bg-gray-900 dark:hover:bg-gray-600 transition">
            تجديد الاشتراك الآن
        </a>

        <div class="mt-4">


             @auth
        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button  type="submit">تسجيل خروج والعودة للصفحة الرئيسية</button>
        </form>
    @endauth
        </div>

    </div>

</div>
@endsection
