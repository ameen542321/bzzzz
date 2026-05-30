@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">المصروفات</h1>

<div class="flex justify-end mb-6">
    <a href="{{ route('expenses.create') }}"
       class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-white">
        إضافة مصروف
    </a>
</div>

{{-- الفلاتر --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-6">

    <form class="grid grid-cols-1 md:grid-cols-5 gap-4">

        {{-- من تاريخ --}}
        <div>
            <label class="text-gray-300 mb-1 block">من تاريخ</label>
            <input type="date"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
        </div>

        {{-- إلى تاريخ --}}
        <div>
            <label class="text-gray-300 mb-1 block">إلى تاريخ</label>
            <input type="date"
                   class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
        </div>

        {{-- النوع --}}
        <div>
            <label class="text-gray-300 mb-1 block">نوع المصروف</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
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

        {{-- المتجر --}}
        <div>
            <label class="text-gray-300 mb-1 block">المتجر</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
                <option value="1">المتجر الرئيسي</option>
                <option value="2">فرع 2</option>
            </select>
        </div>

        {{-- المستخدم --}}
        <div>
            <label class="text-gray-300 mb-1 block">المستخدم</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
                <option value="1">المدير العام</option>
                <option value="2">المستخدم</option>
            </select>
        </div>

    </form>

</div>

{{-- جدول المصروفات --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">التاريخ</th>
                <th class="py-3">النوع</th>
                <th class="py-3">المبلغ</th>
                <th class="py-3">المتجر</th>
                <th class="py-3">المستخدم</th>
                <th class="py-3">ملاحظة</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-20</td>
                <td class="py-3">كهرباء</td>
                <td class="py-3 text-red-400">350 ريال</td>
                <td class="py-3">المتجر الرئيسي</td>
                <td class="py-3">المدير العام</td>
                <td class="py-3">فاتورة شهر ديسمبر</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-18</td>
                <td class="py-3">طعام</td>
                <td class="py-3 text-red-400">120 ريال</td>
                <td class="py-3">فرع 2</td>
                <td class="py-3">المستخدم</td>
                <td class="py-3">وجبة للعمال</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
