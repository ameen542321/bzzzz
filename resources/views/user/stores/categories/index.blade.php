@extends('dashboard.app')

@section('title', 'أقسام متجر ' . $store->name)

@section('content')

<div class="max-w-7xl mx-auto py-10">

{{-- العنوان + الأزرار --}}
<div class="flex items-center justify-between mb-10">

    {{-- زر الرجوع --}}
    <a href="{{ route('user.stores.catalog', $store->id) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
        <i class="fa-solid fa-arrow-right text-sm"></i>
        <span class="text-sm font-medium">رجوع</span>
    </a>

    {{-- العنوان --}}
    <h1 class="text-3xl font-bold text-white tracking-wide">
        <i class="fa-solid fa-layer-group ml-2 text-blue-400"></i>
        الأقسام
    </h1>

    {{-- الأزرار --}}
    <div class="flex items-center gap-3">

        {{-- زر إضافة نشاط --}}
        <a href="{{ route('user.stores.categories.create', ['store' => $store->id, 'is_main_category' => 1]) }}"
           class="flex items-center bg-purple-600 hover:bg-purple-700 text-white px-5 py-2 rounded-lg transition duration-200 shadow-sm">
            <i class="fa-solid fa-plus ml-2"></i>
            إضافة نشاط
        </a>

        {{-- زر إضافة قسم --}}
        <a href="{{ route('user.stores.categories.create', ['store' => $store->id, 'is_main_category' => 0]) }}"
           class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg transition duration-200 shadow-sm">
            <i class="fa-solid fa-plus ml-2"></i>
            إضافة قسم
        </a>

    </div>

</div>

{{-- إحصائيات سريعة --}}
@php
    // حساب إحصائيات الأقسام
    $categoriesStats = App\Models\Category::where('store_id', $store->id)
        ->whereNull('deleted_at')
        ->withCount(['products' => function($query) {
            $query->whereNull('deleted_at');
        }])
        ->get();

    $totalActiveProducts = $categoriesStats->sum('products_count');
    $totalActiveCategories = $categoriesStats->count();
    $categoriesWithProducts = $categoriesStats->filter(fn($cat) => $cat->products_count > 0)->count();
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    {{-- عدد الأقسام النشطة --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-layer-group text-purple-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">الأقسام النشطة</h3>
        <p class="text-3xl font-bold text-purple-400">
            {{ $totalActiveCategories }}
            <span class="text-sm text-gray-400">قسم</span>
        </p>
    </div>

    {{-- إجمالي المنتجات --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-box text-blue-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">إجمالي المنتجات</h3>
        <p class="text-3xl font-bold text-blue-400">
            {{ $totalActiveProducts }}
            <span class="text-sm text-gray-400">منتج</span>
        </p>
    </div>

    {{-- الأقسام تحتوي منتجات --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-folder-open text-green-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">أقسام تحتوي منتجات</h3>
        <p class="text-3xl font-bold text-green-400">
            {{ $categoriesWithProducts }}
            <span class="text-sm text-gray-400">قسم</span>
        </p>
    </div>
</div>

@php
    $mainCategories = $categories->where('is_main_category', 1);
    $normalCategories = $categories->where('is_main_category', 0);
@endphp

{{-- في حال لا توجد أقسام --}}
@if($categories->isEmpty())
    <div class="bg-gray-900 border border-gray-800 p-10 rounded-xl text-center shadow-lg">
        <i class="fa-solid fa-folder-open text-gray-500 text-6xl mb-4"></i>
        <p class="text-gray-400 text-lg">لا توجد أقسام حتى الآن</p>
    </div>

@else

    {{-- الأنشطة --}}
    @if($mainCategories->isNotEmpty())
        <h2 class="text-xl font-bold text-white mb-4">الأنشطة</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            @foreach($mainCategories as $category)
                @php
                    // حساب عدد المنتجات في هذا القسم
                    $productsCount = App\Models\Product::where('store_id', $store->id)
                        ->where('category_id', $category->id)
                        ->whereNull('deleted_at')
                        ->count();

                    $productsValue = App\Models\Product::where('store_id', $store->id)
                        ->where('category_id', $category->id)
                        ->whereNull('deleted_at')
                        ->sum('price');
                @endphp

                {{-- بطاقة النشاط --}}
                <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition duration-200 shadow-sm">

                    {{-- العنوان + زر تعديل --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-white truncate">
                                {{ $category->name }}
                            </h2>
                            <span class="text-purple-500 text-xs" title="نشاط رئيسي">
                                <i class="fa-solid fa-star"></i>
                            </span>
                        </div>

                        <a href="{{ route('user.stores.categories.edit', [$store->id, $category->id]) }}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-600/20 border border-blue-500/40 text-blue-300 hover:bg-blue-600 hover:text-white transition text-xs font-bold">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>تعديل</span>
                        </a>
                    </div>

                    {{-- الوصف --}}
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2 leading-relaxed">
                        {{ $category->description ?: 'لا يوجد وصف' }}
                    </p>

                    {{-- إحصائيات القسم --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gray-800/50 p-2 rounded-lg text-center">
                            <div class="text-xs text-gray-500 mb-1">المنتجات</div>
                            <div class="text-lg font-bold text-blue-400">{{ $productsCount }}</div>
                        </div>
                        <div class="bg-gray-800/50 p-2 rounded-lg text-center">
                            <div class="text-xs text-gray-500 mb-1">القيمة</div>
                            <div class="text-lg font-bold text-green-400">{{ number_format($productsValue, 0) }} ر.س</div>
                        </div>
                    </div>

                    {{-- الحالة --}}
                    <div class="mb-4">
                        @if($category->status === 'active')
                            <span class="px-3 py-1 rounded-full text-xs bg-green-900 text-green-300">
                                <i class="fa-solid fa-check ml-1"></i> مفعل
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs bg-yellow-900 text-yellow-300">
                                <i class="fa-solid fa-ban ml-1"></i> غير مفعل
                            </span>
                        @endif
                    </div>

                    {{-- الأزرار --}}
                    <div class="flex items-center justify-between mt-6">

                        {{-- زر تفعيل/تعطيل --}}
                        <form action="{{ route('user.stores.categories.toggle-status', [$store->id, $category->id]) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            @if($category->status === 'active')
                                <button class="flex items-center text-yellow-400 hover:text-yellow-300 text-sm transition">
                                    <i class="fa-solid fa-ban ml-1"></i>
                                    تعطيل
                                </button>
                            @else
                                <button class="flex items-center text-green-400 hover:text-green-300 text-sm transition">
                                    <i class="fa-solid fa-check ml-1"></i>
                                    تفعيل
                                </button>
                            @endif
                        </form>

                        {{-- زر حذف --}}
                        <form action="{{ route('user.stores.categories.destroy', [$store->id, $category->id]) }}"
                              method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا النشاط؟ سيتم حذف جميع المنتجات المرتبطة به.');">
                            @csrf
                            @method('DELETE')

                            <button class="flex items-center text-red-400 hover:text-red-300 text-sm transition">
                                <i class="fa-solid fa-trash ml-1"></i>
                                حذف
                            </button>
                        </form>

                    </div>

                </div>

            @endforeach
        </div>
    @endif

    {{-- الأقسام العادية --}}
    @if($normalCategories->isNotEmpty())
        <h2 class="text-xl font-bold text-white mb-4">الأقسام</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($normalCategories as $category)
                @php
                    // حساب عدد المنتجات في هذا القسم
                    $productsCount = App\Models\Product::where('store_id', $store->id)
                        ->where('category_id', $category->id)
                        ->whereNull('deleted_at')
                        ->count();

                    $productsValue = App\Models\Product::where('store_id', $store->id)
                        ->where('category_id', $category->id)
                        ->whereNull('deleted_at')
                        ->sum('price');
                @endphp

                {{-- بطاقة القسم --}}
                <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition duration-200 shadow-sm">

                    {{-- العنوان + زر تعديل --}}
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-white truncate">
                            {{ $category->name }}
                        </h2>

                        <a href="{{ route('user.stores.categories.edit', [$store->id, $category->id]) }}"
                           class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-600/20 border border-blue-500/40 text-blue-300 hover:bg-blue-600 hover:text-white transition text-xs font-bold">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>تعديل</span>
                        </a>
                    </div>

                    {{-- الوصف --}}
                    <p class="text-gray-400 text-sm mb-4 line-clamp-2 leading-relaxed">
                        {{ $category->description ?: 'لا يوجد وصف' }}
                    </p>

                    {{-- إحصائيات القسم --}}
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-gray-800/50 p-2 rounded-lg text-center">
                            <div class="text-xs text-gray-500 mb-1">المنتجات</div>
                            <div class="text-lg font-bold text-blue-400">{{ $productsCount }}</div>
                        </div>
                        <div class="bg-gray-800/50 p-2 rounded-lg text-center">
                            <div class="text-xs text-gray-500 mb-1">القيمة</div>
                            <div class="text-lg font-bold text-green-400">{{ number_format($productsValue, 0) }} ر.س</div>
                        </div>
                    </div>

                    {{-- الحالة --}}
                    <div class="mb-4">
                        @if($category->status === 'active')
                            <span class="px-3 py-1 rounded-full text-xs bg-green-900 text-green-300">
                                <i class="fa-solid fa-check ml-1"></i> مفعل
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs bg-yellow-900 text-yellow-300">
                                <i class="fa-solid fa-ban ml-1"></i> غير مفعل
                            </span>
                        @endif
                    </div>

                    {{-- الأزرار --}}
                    <div class="flex items-center justify-between mt-6">

                        {{-- زر تفعيل/تعطيل --}}
                        <form action="{{ route('user.stores.categories.toggle-status', [$store->id, $category->id]) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            @if($category->status === 'active')
                                <button class="flex items-center text-yellow-400 hover:text-yellow-300 text-sm transition">
                                    <i class="fa-solid fa-ban ml-1"></i>
                                    تعطيل
                                </button>
                            @else
                                <button class="flex items-center text-green-400 hover:text-green-300 text-sm transition">
                                    <i class="fa-solid fa-check ml-1"></i>
                                    تفعيل
                                </button>
                            @endif
                        </form>

                        {{-- زر حذف --}}
                        <form action="{{ route('user.stores.categories.destroy', [$store->id, $category->id]) }}"
                              method="POST"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا القسم؟ سيتم حذف جميع المنتجات المرتبطة به.');">
                            @csrf
                            @method('DELETE')

                            <button class="flex items-center text-red-400 hover:text-red-300 text-sm transition">
                                <i class="fa-solid fa-trash ml-1"></i>
                                حذف
                            </button>
                        </form>

                    </div>

                </div>

            @endforeach
        </div>
    @endif

@endif

{{-- سلة المحذوفات --}}
<div class="mt-10 text-center">
    <a href="{{ route('user.stores.categories.trash', $store->id) }}"
       class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-700 text-gray-400 hover:text-gray-300 hover:border-gray-600 transition">

        <i class="fa-solid fa-trash-can ml-2"></i>

        سلة المحذوفات

        <span class="ml-2 bg-gray-800 text-gray-300 px-2 py-0.5 rounded text-xs">
            {{ $trashCount }}
        </span>
    </a>
</div>

</div>

<style>
.line-clamp-2 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
}
</style>

@endsection
