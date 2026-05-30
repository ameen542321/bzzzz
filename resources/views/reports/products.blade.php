@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير المنتجات</h1>

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

        {{-- الفئة --}}
        <div>
            <label class="text-gray-300 mb-1 block">الفئة</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>

                {{-- لاحقًا foreach --}}
                <option value="1">القهوة</option>
                <option value="2">الشاي</option>
            </select>
        </div>

        {{-- حالة المخزون --}}
        <div>
            <label class="text-gray-300 mb-1 block">حالة المخزون</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
                <option value="low">منخفض</option>
                <option value="out">منتهي</option>
                <option value="active">متوفر</option>
            </select>
        </div>

    </form>

</div>

{{-- الإحصائيات --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">عدد المنتجات</h3>
        <p class="text-3xl font-bold text-blue-400">42</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي القطع المباعة</h3>
        <p class="text-3xl font-bold text-yellow-400">1,230</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الأرباح</h3>
        <p class="text-3xl font-bold text-green-400">4,320 ريال</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- جدول المنتجات --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">تفاصيل المنتجات</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[900px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">الفئة</th>
                <th class="py-3">سعر الشراء</th>
                <th class="py-3">متوسط سعر البيع</th>
                <th class="py-3">الكمية المباعة</th>
                <th class="py-3">الربح لكل قطعة</th>
                <th class="py-3">إجمالي الربح</th>
                <th class="py-3">المخزون</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة عربية</td>
                <td class="py-3">القهوة</td>
                <td class="py-3">8 ريال</td>
                <td class="py-3">15 ريال</td>
                <td class="py-3">120</td>
                <td class="py-3 text-green-400 font-semibold">7 ريال</td>
                <td class="py-3 text-green-400 font-semibold">840 ريال</td>
                <td class="py-3">35</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">شاي أسود</td>
                <td class="py-3">الشاي</td>
                <td class="py-3">5 ريال</td>
                <td class="py-3">10 ريال</td>
                <td class="py-3">95</td>
                <td class="py-3 text-green-400 font-semibold">5 ريال</td>
                <td class="py-3 text-green-400 font-semibold">475 ريال</td>
                <td class="py-3">12</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- المنتجات الراكدة --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المنتجات الراكدة (لم تُبع)</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">الفئة</th>
                <th class="py-3">المخزون</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">كابتشينو</td>
                <td class="py-3">القهوة</td>
                <td class="py-3">18</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة تركية</td>
                <td class="py-3">القهوة</td>
                <td class="py-3">22</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
