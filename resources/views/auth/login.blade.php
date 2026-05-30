@extends('layouts.auth')

@section('content')
<div class="max-w-md mx-auto mt-10">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center">
            <div class="relative">
                <div class="text-5xl font-black tracking-tighter text-white">
                    <span class="text-blue-500">CAR</span><span class="relative">LED<span class="absolute -bottom-1 left-0 w-full h-1 bg-blue-500 shadow-[0_0_15px_rgba(59,130,246,0.8)] rounded-full"></span></span>
                </div>
                <div class="absolute -inset-2 bg-blue-500/20 blur-xl rounded-full -z-10"></div>
            </div>
        </div>
        <p class="text-gray-500 text-xs mt-3 tracking-[0.2em] uppercase font-bold">Smart Management System</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-50"></div>

        <h1 class="text-xl font-bold text-center mb-8 text-gray-200">الدخول إلى النظام</h1>

        {{-- رسائل الأخطاء والحالات --}}
        @if ($errors->any() || session('auth') || session('status'))
            <div class="mb-6">
                @if ($errors->any())
                    <div class="bg-red-900/20 border-r-4 border-red-600 text-red-400 p-3 rounded text-sm">
                        @foreach ($errors->all() as $error) <div>{{ $error }}</div> @endforeach
                    </div>
                @endif
                @if (session('auth'))
                    <div class="bg-amber-900/20 border-r-4 border-amber-600 text-amber-400 p-3 rounded text-sm">{{ session('auth') }}</div>
                @endif
                @if (session('status'))
                    <div class="bg-green-900/20 border-r-4 border-green-600 text-green-400 p-3 rounded text-sm">{{ session('status') }}</div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="space-y-5">
                {{-- البريد الإلكتروني --}}
                <div>
                    <label class="text-gray-400 text-xs font-bold mb-2 block mr-1">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all placeholder:text-gray-600"
                           placeholder="name@company.com" required autofocus>
                </div>

                {{-- كلمة المرور --}}
                <div>
                    <label class="text-gray-400 text-xs font-bold mb-2 block mr-1">كلمة المرور</label>
                    <input type="password" name="password"
                           class="w-full bg-gray-900/50 border border-gray-700 rounded-xl px-4 py-3 text-gray-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                           required>
                </div>

                {{-- تذكرني + نسيت كلمة المرور --}}
                <div class="flex items-center justify-between text-sm mt-2">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="hidden peer">
                        <div class="w-5 h-5 border border-gray-600 rounded bg-gray-900 peer-checked:bg-blue-600 peer-checked:border-blue-600 transition-all flex items-center justify-center">
                            <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <span class="mr-2 text-gray-400 group-hover:text-gray-300 transition-colors">تذكرني</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-blue-500 hover:text-blue-400 font-medium">نسيت كلمة المرور؟</a>
                </div>

                {{-- زر الدخول --}}
                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-500 py-3.5 rounded-xl text-white font-bold transition-all shadow-lg shadow-blue-600/20 active:scale-[0.98] mt-4">
                    تسجيل الدخول
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
