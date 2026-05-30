@extends('layouts.app')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-semibold">منتجات فئة: {{ $category->name }}</h1>
        <p class="text-gray-400 mt-1">عدد المنتجات: {{ $category->products_count ?? 0 }}</p>
    </div>

    <a href="{{ route('categories.index') }}"
       class="bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded text-white">
        رجوع
    </a>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-4">

    <table class="w-full text-right">
        <thead>
            <tr class="text-gray-400 border-b border-gray-700">
                <th class="py-3">الصورة</th>
                <th class="py-3">اسم المنتج</th>
                <th class="py-3">السعر</th>
                <th class="py-3">المخزون</th>
                <th class="py-3">الحالة</th>
                <th class="py-3">إجراءات</th>
            </tr>
        </thead>

        <tbody class="text-gray-300">

            {{-- مثال واحد (لاحقًا foreach) --}}
            <tr class="border-b border-gray-700">

                {{-- صورة --}}
                <td class="py-3">
                    <img src="https://via.placeholder.com/40"
                         class="w-10 h-10 rounded border border-gray-700">
                </td>

                {{-- اسم المنتج --}}
                <td class="py-3">قهوة عربية</td>

                {{-- السعر --}}
                <td class="py-3">15 ريال</td>

                {{-- المخزون --}}
                <td class="py-3">120</td>

                {{-- حالة المخزون --}}
                <td class="py-3">
                    <span class="px-3 py-1 rounded bg-green-700 text-white text-sm">
                        متوفر
                    </span>
                </td>

                {{-- إجراءات --}}
                <td class="py-3 flex gap-2">

                    {{-- عرض --}}
                    <a href="#"
                       class="px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded text-white text-sm">
                        عرض
                    </a>

                    {{-- تعديل --}}
                    <a href="#"
                       class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 rounded text-white text-sm">
                        تعديل
                    </a>

                    {{-- إدارة المخزون --}}
                    <a href="#"
                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">
                        إدارة المخزون
                    </a>

                </td>

            </tr>

            {{-- مثال آخر لمنتج منخفض --}}
            <tr class="border-b border-gray-700">

                <td class="py-3">
                    <img src="https://via.placeholder.com/40"
                         class="w-10 h-10 rounded border border-gray-700">
                </td>

                <td class="py-3">شاي أسود</td>

                <td class="py-3">12 ريال</td>

                <td class="py-3 text-yellow-400 font-semibold">4</td>

                <td class="py-3">
                    <span class="px-3 py-1 rounded bg-yellow-600 text-white text-sm">
                        منخفض
                    </span>
                </td>

                <td class="py-3 flex gap-2">

                    <a href="#"
                       class="px-3 py-1 bg-gray-700 hover:bg-gray-600 rounded text-white text-sm">
                        عرض
                    </a>

                    <a href="#"
                       class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 rounded text-white text-sm">
                        تعديل
                    </a>

                    <a href="#"
                       class="px-3 py-1 bg-blue-600 hover:bg-blue-700 rounded text-white text-sm">
                        إدارة المخزون
                    </a>

                </td>

            </tr>

        </tbody>
    </table>

</div>

@endsection
