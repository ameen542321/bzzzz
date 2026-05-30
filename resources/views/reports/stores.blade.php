@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير المتاجر</h1>

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

        {{-- طريقة الدفع --}}
        <div>
            <label class="text-gray-300 mb-1 block">طريقة الدفع</label>
            <select class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-gray-200">
                <option value="">الكل</option>
                <option value="cash">نقدًا</option>
                <option value="card">بطاقة</option>
                <option value="transfer">تحويل بنكي</option>
            </select>
        </div>

    </form>

</div>

{{-- الإحصائيات العامة --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">عدد المتاجر</h3>
        <p class="text-3xl font-bold text-blue-400">2</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المبيعات</h3>
        <p class="text-3xl font-bold text-green-400">18,420 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الأرباح</h3>
        <p class="text-3xl font-bold text-yellow-400">6,120 ريال</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- جدول المتاجر --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">أداء المتاجر</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[900px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المتجر</th>
                <th class="py-3">عدد الفواتير</th>
                <th class="py-3">إجمالي المبيعات</th>
                <th class="py-3">إجمالي التكلفة</th>
                <th class="py-3">إجمالي الأرباح</th>
                <th class="py-3">متوسط الفاتورة</th>
                <th class="py-3">أكثر طريقة دفع</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">
                <td class="py-3">المتجر الرئيسي</td>
                <td class="py-3">120</td>
                <td class="py-3">12,540 ريال</td>
                <td class="py-3">8,220 ريال</td>
                <td class="py-3 text-green-400 font-semibold">4,320 ريال</td>
                <td class="py-3">104 ريال</td>
                <td class="py-3">نقدًا</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">فرع 2</td>
                <td class="py-3">63</td>
                <td class="py-3">5,880 ريال</td>
                <td class="py-3">4,020 ريال</td>
                <td class="py-3 text-green-400 font-semibold">1,860 ريال</td>
                <td class="py-3">93 ريال</td>
                <td class="py-3">بطاقة</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- أفضل المنتجات في كل متجر --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">أفضل المنتجات في كل متجر</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المتجر</th>
                <th class="py-3">المنتج</th>
                <th class="py-3">الكمية المباعة</th>
                <th class="py-3">إجمالي المبيعات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">المتجر الرئيسي</td>
                <td class="py-3">قهوة عربية</td>
                <td class="py-3">120</td>
                <td class="py-3">1,800 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">فرع 2</td>
                <td class="py-3">شاي أسود</td>
                <td class="py-3">95</td>
                <td class="py-3">950 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- أداء المحاسبين داخل كل متجر --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">أداء المحاسبين داخل المتاجر</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المتجر</th>
                <th class="py-3">المحاسب</th>
                <th class="py-3">عدد الفواتير</th>
                <th class="py-3">إجمالي المبيعات</th>
                <th class="py-3">إجمالي الأرباح</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">المتجر الرئيسي</td>
                <td class="py-3">سعود العتيبي</td>
                <td class="py-3">95</td>
                <td class="py-3">7,200 ريال</td>
                <td class="py-3 text-green-400 font-semibold">2,900 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">فرع 2</td>
                <td class="py-3">محمد القحطاني</td>
                <td class="py-3">63</td>
                <td class="py-3">5,880 ريال</td>
                <td class="py-3 text-green-400 font-semibold">1,860 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
