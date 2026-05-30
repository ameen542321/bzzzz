@extends('dashboard.app')

@section('title', 'تعديل بيانات المستخدم')

@section('content')
<div class="p-6 max-w-4xl mx-auto text-right" dir="rtl">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}"
               class="w-10 h-10 flex items-center justify-center rounded-xl bg-gray-800 border border-gray-700 text-gray-400 hover:text-white hover:border-blue-500 transition-all">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">تعديل الملف الشخصي</h1>
                <p class="text-sm text-gray-400">تحديث صلاحيات وبيانات: <span class="text-blue-400 font-medium">{{ $user->name }}</span></p>
            </div>
        </div>
        <div class="hidden md:block">
            <span class="px-3 py-1 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-lg text-xs font-mono uppercase">
                User ID: #{{ $user->id }}
            </span>
        </div>
    </div>

    {{-- Error Alerts --}}
    @if($errors->any())
    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl">
        <ul class="text-sm text-red-400 space-y-1">
            @foreach($errors->all() as $error)
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-exclamation text-xs"></i>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- الجانب الأيمن: البيانات الأساسية --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-900/50 border border-gray-800 p-6 rounded-3xl shadow-xl backdrop-blur-sm">
                    <h3 class="text-gray-200 font-semibold mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-id-card text-blue-500"></i> المعلومات الشخصية
                    </h3>

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs text-gray-500 mb-2 mr-1">الاسم الكامل</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                       class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-2 mr-1">رقم الهاتف</label>
                                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                       class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition outline-none text-right">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">البريد الإلكتروني</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition outline-none">
                        </div>
                    </div>
                </div>

                {{-- ✅ إضافة قسم اختيار الخطة --}}
                <div class="bg-gray-900/50 border border-gray-800 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-gray-200 font-semibold mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-box-open text-blue-500"></i> باقة الاشتراك
                    </h3>
                    <select name="plan_id" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none appearance-none color-scheme-dark">
                        <option value="">بدون باقة (حساب يدوي)</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" {{ $user->plan_id == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} ({{ $plan->allowed_stores }} متجر / {{ $plan->allowed_accountants }} محاسب)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-gray-200 font-semibold mb-6 flex items-center gap-2 text-sm uppercase tracking-wider">
                        <i class="fa-solid fa-sliders text-blue-500"></i> حدود النظام (Limits)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">المتاجر المسموحة</label>
                            <input type="number" name="allowed_stores" value="{{ old('allowed_stores', $user->allowed_stores) }}"
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">المحاسبين المسموح بهم</label>
                            <input type="number" name="allowed_accountants" value="{{ old('allowed_accountants', $user->allowed_accountants) }}"
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            {{-- الجانب الأيسر: الحالة والتواريخ --}}
            <div class="space-y-6">
                <div class="bg-gray-900/50 border border-gray-800 p-6 rounded-3xl shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-1 h-full {{ $user->status == 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                    <h3 class="text-gray-200 font-semibold mb-6 text-sm uppercase tracking-wider">الحالة والتواريخ</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">حالة الحساب</label>
                            <select name="status" class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none appearance-none">
                                <option value="active" {{ $user->status == 'active' ? 'selected' : '' }}>نشط (Active)</option>
                                <option value="suspended" {{ $user->status == 'suspended' ? 'selected' : '' }}>موقف (Suspended)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">انتهاء الاشتراك</label>
                            <input type="date" name="subscription_end_at"
                                   value="{{ $user->subscription_end_at ? \Carbon\Carbon::parse($user->subscription_end_at)->format('Y-m-d') : '' }}"
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none color-scheme-dark">
                        </div>

                        {{-- ✅ إضافة حقل انتهاء الصلاحية المفقود --}}
                        <div>
                            <label class="block text-xs text-gray-500 mb-2 mr-1">تاريخ إغلاق الحساب (Expiry)</label>
                            <input type="date" name="expires_at"
                                   value="{{ $user->expires_at ? \Carbon\Carbon::parse($user->expires_at)->format('Y-m-d') : '' }}"
                                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:border-blue-500 outline-none color-scheme-dark">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit"
                            class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-2xl shadow-lg shadow-blue-600/20 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        حفظ التغييرات
                    </button>
                    <a href="{{ route('admin.users.index') }}"
                       class="w-full py-4 bg-gray-800 hover:bg-gray-700 text-gray-400 font-bold rounded-2xl text-center transition-all">
                        إلغاء
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection