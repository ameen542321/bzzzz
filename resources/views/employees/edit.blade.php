@extends('dashboard.app')

@section('content')

<div class="px-6 py-8 max-w-3xl mx-auto">

    <!-- العنوان + زر الرجوع -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-100">تعديل بيانات موظف</h1>
            <p class="text-gray-400 text-sm mt-1">قم بتحديث بيانات الموظف وتعديل معلوماته الأساسية</p>
        </div>

        <a href="{{ request('return_to', route('user.employees.index')) }}"
           class="inline-flex items-center gap-2 text-gray-300 hover:text-white bg-gray-800 border border-gray-700 px-4 py-2 rounded-lg shadow hover:bg-gray-700 transition">
            <i class="fa-solid fa-arrow-right"></i>
            رجوع
        </a>
    </div>

    <!-- بطاقة النموذج -->
    <div class="bg-gray-900 border border-gray-800 shadow-xl rounded-xl p-8">

        <form action="{{ route('user.employees.update', $employee->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <input type="hidden" name="return_to" value="{{ request('return_to') }}">

            <!-- الاسم -->
            <div>
                <label class="block text-gray-300 font-medium mb-1">اسم الموظف</label>
                <div class="relative">
                    <input type="text" name="name" value="{{ $employee->name }}" required
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-user text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                @error('name')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- الجوال -->
            <div>
                <label class="block text-gray-300 font-medium mb-1">رقم الجوال</label>
                <div class="relative">
                    <input type="text" name="phone" value="{{ $employee->phone }}"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-phone text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                @error('phone')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- الراتب -->
            <div>
                <label class="block text-gray-300 font-medium mb-1">الراتب الشهري</label>
                <div class="relative">
                    <input type="number" name="salary" value="{{ $employee->salary }}" required step="0.01"
                           class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                  focus:ring-blue-500 focus:border-blue-500">
                    <i class="fa-solid fa-money-bill text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                @error('salary')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- المتجر -->
            <div>
                <label class="block text-gray-300 font-medium mb-1">المتجر</label>
                <div class="relative">
                    <select name="store_id"
                            class="w-full bg-gray-800 border border-gray-700 text-gray-200 rounded-lg px-10 py-2
                                   focus:ring-blue-500 focus:border-blue-500">
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}" {{ $employee->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->name }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-store text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
                </div>
                @error('store_id')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- زر التحديث -->
            <div class="pt-4 flex justify-between">



                <button
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-lg shadow hover:bg-blue-700 transition font-semibold">
                    تحديث البيانات
                </button>
            </div>

        </form>

    </div>

</div>

@endsection
