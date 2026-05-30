@extends('dashboard.app')

@section('content')

<div class="max-w-7xl mx-auto py-10">

    {{-- عنوان الصفحة --}}
    <h1 class="text-3xl font-bold text-white mb-8">
        إدارة الأقسام والمنتجات – {{ $store->name }}
    </h1>

    {{-- البطاقات --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- الأقسام --}}
        <a href="{{ route('user.categories.index') }}"
           class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
            <i class="fa-solid fa-layer-group text-orange-400 text-4xl mb-3"></i>
            <h2 class="text-xl font-bold text-white">الأقسام</h2>
            <p class="text-gray-400 mt-1">إضافة وتعديل وحذف الأقسام</p>
        </a>

        {{-- المنتجات --}}
        <a href="{{ route('user.products.index') }}"
           class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
            <i class="fa-solid fa-box text-purple-400 text-4xl mb-3"></i>
            <h2 class="text-xl font-bold text-white">المنتجات</h2>
            <p class="text-gray-400 mt-1">إضافة وتعديل وحذف المنتجات</p>
        </a>

    </div>

</div>

@endsection
