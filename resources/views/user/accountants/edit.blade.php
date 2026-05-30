@extends('dashboard.app')

@section('title', 'تعديل بيانات المحاسب')

@section('content')

<div class="px-6 py-8 max-w-3xl mx-auto">

  {{-- الهيدر --}}
<div class="flex items-center justify-between mb-8">

    {{-- زر الرجوع (يمين) --}}
    <a href="{{ request('return_to', route('user.accountants.index')) }}"
       class="inline-flex items-center gap-2 text-gray-300 hover:text-white bg-gray-800 border border-gray-700 px-4 py-2 rounded-lg shadow hover:bg-gray-700 transition">
        <i class="fa-solid fa-arrow-right"></i>
        رجوع
    </a>

    {{-- العنوان (وسط) --}}
    <h1 class="text-3xl font-bold text-gray-100 text-center flex-1">
        تعديل بيانات المحاسب
    </h1>

    {{-- يسار (فارغ أو زر إضافة) --}}
    <div class="w-32"></div>
    {{-- أو لو تبغى زر إضافة مستقبلاً:
    <a href="#" class="text-gray-300 hover:text-white">إضافة</a>
    --}}
</div>


    {{-- الفورم الموحد --}}
    <form id="updateForm"
          action="{{ route('user.accountants.update', $accountant->id) }}"
          method="POST"
          class="space-y-10">

        @csrf
        @method('PUT')

        <input type="hidden" name="return_to" value="{{ request('return_to') }}">

        {{-- بطاقة بيانات الموظف --}}
        <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

            <h2 class="text-xl font-semibold text-gray-200 mb-6">بيانات الموظف</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- الاسم --}}
                <div>
                    <label class="block text-gray-300 font-medium mb-1">اسم الموظف</label>
                    <div class="relative">
                        <input type="text" name="name"
                               value="{{ old('name', $accountant->employee->name) }}"
                               class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                      focus:ring-blue-500 focus:border-blue-500">
                        <i class="fa-solid fa-user text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- الجوال --}}
                <div>
                    <label class="block text-gray-300 font-medium mb-1">رقم الجوال</label>
                    <div class="relative">
                        <input type="text" name="phone"
                               value="{{ old('phone', $accountant->employee->phone) }}"
                               class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                      focus:ring-blue-500 focus:border-blue-500">
                        <i class="fa-solid fa-phone text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- المتجر --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-300 font-medium mb-1">المتجر</label>
                    <div class="relative">
                        <select name="store_id"
                                class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                       focus:ring-blue-500 focus:border-blue-500">
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}"
                                    {{ $accountant->employee->store_id == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }}
                                </option>
                            @endforeach
                        </select>
                        <i class="fa-solid fa-store text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

            </div>

        </div>

        {{-- بطاقة بيانات حساب المحاسب --}}
        <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

            <h2 class="text-xl font-semibold text-gray-200 mb-6">بيانات حساب المحاسب</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- البريد الإلكتروني --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-300 font-medium mb-1">البريد الإلكتروني</label>
                    <div class="relative">
                        <input type="email" name="email"
                               value="{{ old('email', $accountant->email) }}"
                               class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                      focus:ring-blue-500 focus:border-blue-500">
                        <i class="fa-solid fa-envelope text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- كلمة المرور --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-300 font-medium mb-1">كلمة المرور الجديدة (اختياري)</label>
                    <div class="relative">
                        <input type="password" name="password"
                               placeholder="اتركه فارغًا إذا لا تريد التغيير"
                               class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                      focus:ring-blue-500 focus:border-blue-500">
                        <i class="fa-solid fa-lock text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

                {{-- الحالة --}}
                <div class="md:col-span-2">
                    <label class="block text-gray-300 font-medium mb-1">حالة الحساب</label>
                    <div class="relative">
                        <select name="status"
                                class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                       focus:ring-blue-500 focus:border-blue-500">
                            <option value="active" {{ $accountant->status === 'active' ? 'selected' : '' }}>نشط</option>
                            <option value="suspended" {{ $accountant->status === 'suspended' ? 'selected' : '' }}>موقّف</option>
                        </select>
                        <i class="fa-solid fa-toggle-on text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    </div>
                </div>

            </div>

            {{-- زر التحديث --}}
            <div class="pt-6 flex justify-end">
                <button
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    تحديث البيانات
                </button>
            </div>

        </div>

    </form>

</div>

@endsection
