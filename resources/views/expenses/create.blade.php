@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">إضافة مصروف</h1>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 max-w-xl mx-auto">

    <form>

        {{-- نوع المصروف --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">نوع المصروف</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">

                {{-- لاحقًا foreach من جدول التصنيفات --}}
                <option value="food">طعام</option>
                <option value="electricity">كهرباء</option>
                <option value="water">ماء</option>
                <option value="gas">غاز</option>
                <option value="rent">إيجار</option>
                <option value="internet">إنترنت</option>
                <option value="cleaning">تنظيف</option>
                <option value="maintenance">صيانة</option>
                <option value="other">أخرى</option>

            </select>
        </div>

        {{-- المبلغ --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">المبلغ</label>
            <input type="number" min="1"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                   placeholder="مثال: 350">
        </div>

        {{-- التاريخ --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">التاريخ</label>
            <input type="date"
                   value="{{ date('Y-m-d') }}"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
        </div>

        {{-- المتجر --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">المتجر</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="1">المتجر الرئيسي</option>
                <option value="2">فرع 2</option>
            </select>
        </div>

        {{-- ملاحظة --}}
        <div class="mb-4">
            <label class="text-gray-300 mb-1 block">ملاحظة (اختياري)</label>
            <textarea rows="3"
                      class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200"
                      placeholder="مثال: فاتورة كهرباء شهر ديسمبر"></textarea>
        </div>

        {{-- زر الحفظ --}}
        <div class="flex justify-end mt-6">
            <button class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">
                حفظ المصروف
            </button>
        </div>

    </form>

</div>

@endsection
