@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير الأرباح</h1>

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

        {{-- المحاسب --}}
        <div>
            <label class="text-gray-300 mb-1 block">المحاسب</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>

                {{-- لاحقًا foreach --}}
                <option value="1">سعود العتيبي</option>
                <option value="2">محمد القحطاني</option>
            </select>
        </div>

        {{-- المتجر --}}
        <div>
            <label class="text-gray-300 mb-1 block">المتجر</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>

                {{-- لاحقًا foreach --}}
                <option value="1">المتجر الرئيسي</option>
                <option value="2">فرع 2</option>
            </select>
        </div>

    </form>

</div>

{{-- الإحصائيات --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المبيعات</h3>
        <p class="text-3xl font-bold text-blue-400">12,540 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي التكلفة</h3>
        <p class="text-3xl font-bold text-red-400">8,220 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الأرباح</h3>
        <p class="text-3xl font-bold text-green-400">4,320 ريال</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- جدول الأرباح حسب الفاتورة --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">الأرباح حسب الفاتورة</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">رقم الفاتورة</th>
                <th class="py-3">التاريخ</th>
                <th class="py-3">المحاسب</th>
                <th class="py-3">إجمالي البيع</th>
                <th class="py-3">إجمالي التكلفة</th>
                <th class="py-3">الربح</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">#1023</td>
                <td class="py-3">2025-12-20</td>
                <td class="py-3">سعود العتيبي</td>
                <td class="py-3">150 ريال</td>
                <td class="py-3">90 ريال</td>
                <td class="py-3 text-green-400 font-semibold">60 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">#1024</td>
                <td class="py-3">2025-12-20</td>
                <td class="py-3">محمد القحطاني</td>
                <td class="py-3">80 ريال</td>
                <td class="py-3">55 ريال</td>
                <td class="py-3 text-green-400 font-semibold">25 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- جدول الأرباح حسب المنتج --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">الأرباح حسب المنتج</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">سعر الشراء</th>
                <th class="py-3">سعر البيع</th>
                <th class="py-3">الكمية المباعة</th>
                <th class="py-3">إجمالي الربح</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة عربية</td>
                <td class="py-3">8 ريال</td>
                <td class="py-3">15 ريال</td>
                <td class="py-3">120</td>
                <td class="py-3 text-green-400 font-semibold">840 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">شاي أسود</td>
                <td class="py-3">5 ريال</td>
                <td class="py-3">10 ريال</td>
                <td class="py-3">95</td>
                <td class="py-3 text-green-400 font-semibold">475 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
