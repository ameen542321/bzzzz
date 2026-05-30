@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">تقرير المصروفات</h1>

{{-- الفلاتر --}}
<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8">

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

    </form>

</div>

{{-- الإحصائيات العامة --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المصروفات</h3>
        <p class="text-3xl font-bold text-red-400">4,320 ريال</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">أعلى نوع مصروف</h3>
        <p class="text-xl font-semibold text-gray-200">الكهرباء</p>
    </div>

    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">أكثر متجر صرف</h3>
        <p class="text-xl font-semibold text-gray-200">المتجر الرئيسي</p>
    </div>

</div>

<hr class="border-gray-700 mb-8">

{{-- المصروفات حسب النوع --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المصروفات حسب النوع</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[700px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">النوع</th>
                <th class="py-3">عدد العمليات</th>
                <th class="py-3">إجمالي المبلغ</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">كهرباء</td>
                <td class="py-3">3</td>
                <td class="py-3 text-red-400">1,050 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">طعام</td>
                <td class="py-3">5</td>
                <td class="py-3 text-red-400">620 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">ماء</td>
                <td class="py-3">2</td>
                <td class="py-3 text-red-400">180 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- المصروفات حسب المتجر --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المصروفات حسب المتجر</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[700px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المتجر</th>
                <th class="py-3">عدد العمليات</th>
                <th class="py-3">إجمالي المبلغ</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">المتجر الرئيسي</td>
                <td class="py-3">6</td>
                <td class="py-3 text-red-400">2,900 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">فرع 2</td>
                <td class="py-3">4</td>
                <td class="py-3 text-red-400">1,420 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

<hr class="border-gray-700 mb-8">

{{-- المصروفات حسب المستخدم --}}
<h2 class="text-xl text-gray-200 font-semibold mb-4">المصروفات حسب المستخدم</h2>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4 mb-8 overflow-x-auto">

    <table class="w-full text-right min-w-[700px]">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">المستخدم</th>
                <th class="py-3">عدد العمليات</th>
                <th class="py-3">إجمالي المبلغ</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            <tr class="border-b border-gray-700">
                <td class="py-3">المدير العام</td>
                <td class="py-3">7</td>
                <td class="py-3 text-red-400">3,200 ريال</td>
            </tr>

            <tr class="border-b border-gray-700">
                <td class="py-3">المستخدم</td>
                <td class="py-3">3</td>
                <td class="py-3 text-red-400">1,120 ريال</td>
            </tr>

        </tbody>
    </table>

</div>

@endsection
