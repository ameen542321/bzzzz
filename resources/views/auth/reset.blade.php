@extends('layouts.auth')

@section('content')

<div class="max-w-md mx-auto bg-gray-800 border border-gray-700 rounded-xl p-8 mt-10">

    <h1 class="text-2xl font-semibold text-center mb-6">إعادة تعيين كلمة المرور</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        {{-- التوكن --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- البريد --}}
        <input type="hidden" name="email" value="{{ $email }}">

        {{-- كلمة المرور الجديدة --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">كلمة المرور الجديدة</label>
            <input type="password"
                   name="password"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                   required>
        </div>

        {{-- تأكيد كلمة المرور --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">تأكيد كلمة المرور</label>
            <input type="password"
                   name="password_confirmation"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                   required>
        </div>

        {{-- زر التحديث --}}
        <button class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded text-white mb-4">
            تحديث كلمة المرور
        </button>

        {{-- العودة --}}
        <div class="text-center">
            <a href="{{ route('login') }}" class="text-blue-400 hover:underline">
                العودة لتسجيل الدخول
            </a>
        </div>

    </form>

</div>

@endsection
