@extends('layouts.auth')

@section('content')

<div class="max-w-md mx-auto bg-gray-800 border border-gray-700 rounded-xl p-8 mt-10">

    <h1 class="text-2xl font-semibold text-center mb-6">نسيت كلمة المرور</h1>

    <p class="text-gray-400 text-center mb-6">
        أدخل بريدك الإلكتروني وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- البريد الإلكتروني --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">البريد الإلكتروني</label>
            <input type="email"
                   name="email"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                   placeholder="example@email.com"
                   required>
        </div>

        {{-- زر الإرسال --}}
        <button class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-white mb-4">
            إرسال رابط إعادة التعيين
        </button>

        {{-- زر الرجوع --}}
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-blue-400 hover:underline">
                العودة لتسجيل الدخول
            </a>
        </div>

    </form>

</div>

@endsection
