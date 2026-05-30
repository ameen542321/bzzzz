@extends('layouts.app')

@section('content')

<h1 class="text-2xl font-semibold mb-6">التقارير</h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    {{-- إجمالي المبيعات --}}
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي المبيعات</h3>
        <p class="text-3xl font-bold text-blue-400">12,540 ريال</p>
    </div>

    {{-- إجمالي الأرباح --}}
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">إجمالي الأرباح</h3>
        <p class="text-3xl font-bold text-green-400">4,320 ريال</p>
    </div>

    {{-- عدد الفواتير --}}
    <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
        <h3 class="text-gray-400 mb-2">عدد الفواتير</h3>
        <p class="text-3xl font-bold text-yellow-400">183</p>
    </div>

</div>

<hr class="border-gray-700 my-8">

{{-- روابط التقارير --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

    <a href="{{ route('reports.sales') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير المبيعات
    </a>

    <a href="{{ route('reports.profits') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير الأرباح
    </a>

    <a href="{{ route('reports.products') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير المنتجات
    </a>

    <a href="{{ route('reports.stock') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير المخزون
    </a>

    <a href="{{ route('reports.accountants') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير المحاسبين
    </a>

    <a href="{{ route('reports.stores') }}"
       class="bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg p-4 text-gray-200">
        تقرير المتاجر
    </a>

</div>

@endsection
