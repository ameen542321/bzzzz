
@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير المبيعات</h1>

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

    </form>

</div>

{{-- الإحصائيات --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المبيعات</h3>
        <p class="text-3xl font-bold text-blue-400">12,540 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">عدد الفواتير</h3>
        <p class="text-3xl font-bold text-yellow-400">183</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">متوسط قيمة الفاتورة</h3>
        <p class="text-3xl font-bold text-green-400">68 ريال</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- المبيعات حسب طريقة الدفع --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المبيعات حسب طريقة الدفع</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">نقدًا</h3>
        <p class="text-2xl font-bold text-gray-200">7,200 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">بطاقة</h3>
        <p class="text-2xl font-bold text-gray-200">4,100 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">تحويل بنكي</h3>
        <p class="text-2xl font-bold text-gray-200">1,240 ريال</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- أفضل المنتجات مبيعًا --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">أفضل المنتجات مبيعًا</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">الكمية المباعة</th>
                <th class="py-3">إجمالي المبيعات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة عربية</td>
                <td class="py-3">120</td>
                <td class="py-3">1,800 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">شاي أسود</td>
                <td class="py-3">95</td>
                <td class="py-3">950 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- أسوأ المنتجات مبيعًا --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">أقل المنتجات مبيعًا</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المنتج</th>
                <th class="py-3">الكمية المباعة</th>
                <th class="py-3">إجمالي المبيعات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">كابتشينو</td>
                <td class="py-3">3</td>
                <td class="py-3">45 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">قهوة تركية</td>
                <td class="py-3">1</td>
                <td class="py-3">10 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
