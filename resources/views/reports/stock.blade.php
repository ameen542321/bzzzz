@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير المخزون</h1>

{{-- الفلاتر --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-6">

    <form class="grid grid-cols-1 md:grid-cols-4 gap-4">

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

        {{-- نوع الحركة --}}
        <div>
            <label class="text-gray-300 mb-1 block">نوع الحركة</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
                <option value="in">إدخال مخزون</option>
                <option value="out">إخراج مخزون</option>
                <option value="sale">بيع</option>
                <option value="adjust">تعديل يدوي</option>
            </select>
        </div>

        {{-- المنتج --}}
        <div>
            <label class="text-gray-300 mb-1 block">المنتج</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>

                {{-- لاحقًا foreach --}}
                <option value="1">قهوة عربية</option>
                <option value="2">شاي أسود</option>
            </select>
        </div>

    </form>

</div>

{{-- الإحصائيات --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الإدخالات</h3>
        <p class="text-3xl font-bold text-blue-400">+ 540 قطعة</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الإخراجات</h3>
        <p class="text-3xl font-bold text-red-400">- 320 قطعة</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المبيعات</h3>
        <p class="text-3xl font-bold text-green-400">1,230 قطعة</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- جدول حركة المخزون --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">حركة المخزون</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[900px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">التاريخ</th>
                <th class="py-3">المنتج</th>
                <th class="py-3">نوع الحركة</th>
                <th class="py-3">قبل</th>
                <th class="py-3">بعد</th>
                <th class="py-3">الكمية</th>
                <th class="py-3">المستخدم</th>
                <th class="py-3">ملاحظة</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-20</td>
                <td class="py-3">قهوة عربية</td>
                <td class="py-3 text-blue-400 font-semibold">إدخال مخزون</td>
                <td class="py-3">50</td>
                <td class="py-3">100</td>
                <td class="py-3">+50</td>
                <td class="py-3">المدير العام</td>
                <td class="py-3">دفعة جديدة</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-20</td>
                <td class="py-3">شاي أسود</td>
                <td class="py-3 text-red-400 font-semibold">إخراج مخزون</td>
                <td class="py-3">40</td>
                <td class="py-3">30</td>
                <td class="py-3">-10</td>
                <td class="py-3">المستخدم</td>
                <td class="py-3">تالف</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-20</td>
                <td class="py-3">قهوة عربية</td>
                <td class="py-3 text-green-400 font-semibold">بيع</td>
                <td class="py-3">100</td>
                <td class="py-3">98</td>
                <td class="py-3">-2</td>
                <td class="py-3">المحاسب</td>
                <td class="py-3">فاتورة #1023</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">2025-12-20</td>
                <td class="py-3">قهوة عربية</td>
                <td class="py-3 text-yellow-400 font-semibold">تعديل يدوي</td>
                <td class="py-3">98</td>
                <td class="py-3">95</td>
                <td class="py-3">-3</td>
                <td class="py-3">المدير العام</td>
                <td class="py-3">تسوية مخزون</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- المنتجات منخفضة المخزون --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المنتجات منخفضة المخزون</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">المخزون</th>
                <th class="py-3">الحد الأدنى</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة عربية</td>
                <td class="py-3">5</td>
                <td class="py-3">10</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">شاي أسود</td>
                <td class="py-3">3</td>
                <td class="py-3">10</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
