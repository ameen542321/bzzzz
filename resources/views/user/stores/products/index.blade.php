@extends('dashboard.app')

@section('title', 'المنتجات – متجر ' . $store->name)

@section('content')

<div class="owner-products-page max-w-7xl mx-auto px-4 py-6 text-right overflow-x-hidden" dir="rtl" x-data="storeProductsLookup()" x-init="init()">

    {{-- ===== الهيدر العلوي ===== --}}
    <div class="mb-6 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('user.stores.catalog', $store->id) }}"
                   class="p-2 rounded-lg bg-gray-900 border border-gray-700 text-gray-400 hover:text-white transition-all duration-200">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-green-500"></i>
                        المنتجات
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">{{ $store->name }}</p>
                </div>
            </div>

            <a href="{{ route('user.stores.products.create', ['store' => $store->id, 'category_id' => request('category_id')]) }}"
               class="bg-green-600 hover:bg-green-500 text-white px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span class="hidden sm:inline">منتج جديد</span>
            </a>
        </div>
    </div>

    {{-- ===== كروت الإحصائيات السريعة ===== --}}
    @php
        $totalProductsCount = $stats->total_count ?? 0;
        $totalCostValue = $stats->total_cost ?? 0;
        $totalStockValue = $stats->total_value ?? 0;
        $lowStockCount = $stats->low_stock_count ?? 0;
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6 text-[12px] sm:text-sm">
        <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700">
            <p class="text-gray-400 text-xs">إجمالي التكلفة</p>
            <p class="text-green-500 font-bold text-lg sm:text-xl">{{ number_format($totalCostValue, 0) }} <span class="text-xs text-gray-500">ر.س</span></p>
        </div>
        <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700">
            <p class="text-gray-400 text-xs">القيمة السوقية</p>
            <p class="text-blue-500 font-bold text-lg sm:text-xl">{{ number_format($totalStockValue, 0) }} <span class="text-xs text-gray-500">ر.س</span></p>
        </div>
        <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700">
            <p class="text-gray-400 text-xs">عدد المنتجات</p>
            <p class="text-purple-500 font-bold text-lg sm:text-xl">{{ $totalProductsCount }}</p>
        </div>
        <div class="bg-gray-800 p-4 rounded-2xl border border-gray-700">
            <p class="text-gray-400 text-xs">المخزون المنخفض</p>
            <p class="text-{{ $lowStockCount > 0 ? 'red' : 'gray' }}-500 font-bold text-lg sm:text-xl">{{ $lowStockCount }}</p>
        </div>
    </div>

    {{-- ===== الفلترة والبحث ===== --}}
    <div class="mb-6 bg-gray-800/50 p-4 rounded-2xl border border-gray-700">
        <form method="GET" action="{{ route('user.stores.products.index', $store->id) }}" class="flex flex-col lg:flex-row gap-2 w-full">
            <div class="relative flex-grow">
                <input type="text" name="search" value="{{ request('search') }}"
                       x-model="searchQuery"
                       @input="filterClientProducts"
                       placeholder="🔍 بحث عن منتج..."
                       class="bg-gray-900 border border-gray-700 rounded-xl py-2.5 px-4 pr-10 text-sm text-white w-full"
                       id="searchInput">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
            </div>

            <select name="category_id" class="bg-gray-900 border border-gray-700 rounded-xl py-2.5 px-4 text-sm text-white w-full lg:w-auto">
                <option value="">📁 جميع الأقسام</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2.5 rounded-xl transition flex items-center gap-2 justify-center">
                <i class="fas fa-search"></i>
                <span>بحث</span>
            </button>

            @if(request('search') || request('category_id'))
                <a href="{{ route('user.stores.products.index', $store->id) }}"
                   class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2.5 rounded-xl transition flex items-center gap-2 justify-center">
                    <i class="fas fa-times"></i>
                    <span>إلغاء</span>
                </a>
            @endif
        </form>

        <div class="mt-3 text-[11px] text-gray-400">
            تلميح سريع: اضغط <kbd class="px-1.5 py-0.5 rounded bg-gray-900 border border-gray-700 text-gray-300">/</kbd> للانتقال مباشرة إلى مربع البحث.
        </div>

        <div class="mt-3 border border-gray-700 rounded-xl p-2 bg-gray-900/40 max-h-52 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <template x-for="p in visibleProducts()" :key="p.id">
                    <button type="button" @click="openProductCard(p.id)"
                        class="text-right bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 hover:border-green-500 transition">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-sm text-white truncate" x-text="p.name"></p>
                            <span class="text-[10px] text-gray-500 truncate max-w-[120px]" x-text="p.barcode || 'بدون باركود'"></span>
                        </div>
                        <p class="text-xs text-green-400 mt-1" x-text="p.price_label"></p>
                        <p class="text-xs text-gray-400 mt-0.5" x-text="p.stock_label"></p>
                    </button>
                </template>
            </div>
            <div x-show="searchQuery && filteredProducts.length === 0 && !hasServerResults" class="text-xs text-yellow-400 p-2">
                لا توجد نتائج سريعة مطابقة.
            </div>
            <div class="pt-2" x-show="!searchQuery && filteredProducts.length > 5">
                <button type="button" @click="showAllMatches = !showAllMatches"
                        class="w-full text-xs py-2 rounded-lg border border-gray-700 bg-gray-900 hover:bg-gray-800 text-gray-300 transition">
                    <span x-text="showAllMatches ? 'إظهار أول 5 نتائج فقط' : 'عرض كل النتائج'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ===== بطاقات المنتجات (قابلة للطي) ===== --}}
    <div class="space-y-3">
        @forelse($products as $index => $product)
            @php
                $isFractional = ($product->product_type === 'fractional' && $product->roll_length > 0);
                $isSet = ($product->is_splittable && $product->items_per_unit > 0);
                $bgColor = $loop->iteration % 2 == 0
                    ? 'bg-gray-100 dark:bg-gray-800/40'
                    : 'bg-white dark:bg-gray-800/80';

                if ($isFractional) {
                    if ($product->roll_length > 0) {
                        $rolls = $product->quantity / $product->roll_length;
                        $displayQty = number_format($rolls, 2);
                        $unitName = 'رول';
                        $totalMeters = number_format($product->quantity, 2);
                        $minStockDisplay = number_format($product->min_stock, 2);
                        $lowStock = $rolls <= $product->min_stock;
                    } else {
                        $displayQty = number_format($product->quantity, 2);
                        $unitName = 'متر';
                        $totalMeters = $displayQty;
                        $minStockDisplay = number_format($product->min_stock, 2);
                        $lowStock = $product->quantity <= $product->min_stock;
                    }
                } elseif ($isSet) {
                    $itemsPerUnit = $product->items_per_unit ?: 1;
                    $totalSets = $product->quantity;
                    $totalPieces = $totalSets * $itemsPerUnit;
                    $displayQty = number_format($totalSets, 2);
                    $unitName = 'طقم';
                    $minStockDisplay = number_format($product->min_stock, 2);
                    $lowStock = $totalSets <= $product->min_stock;
                } else {
                    $displayQty = number_format($product->quantity, 0);
                    $unitName = 'حبة';
                    $minStockDisplay = number_format($product->min_stock, 0);
                    $lowStock = $product->quantity <= $product->min_stock;
                }

                $productCost = ($product->cost_price ?? 0) * ($isFractional ? $rolls : $product->quantity);
                $serialNumber = $loop->iteration;

                $headerPriceLabel = 'الحبة';
                $headerPriceValue = $product->price;

                if ($isSet) {
                    if (($product->piece_price ?? 0) > 0) {
                        $headerPriceLabel = 'الحبة';
                        $headerPriceValue = $product->piece_price;
                    } else {
                        $headerPriceLabel = 'الطقم';
                        $headerPriceValue = $product->price;
                    }
                } elseif ($isFractional) {
                    $headerPriceLabel = $product->roll_length > 0 ? 'الرول' : 'المتر';
                    $headerPriceValue = $product->price;
                }
            @endphp

            <div id="product-card-{{ $product->id }}"
                 class="{{ $bgColor }} rounded-xl border border-gray-200 dark:border-gray-700 hover:border-green-300 dark:hover:border-green-500/30 transition-all hover:shadow-lg hover:shadow-green-500/5">
                {{-- رأس البطاقة (دائماً ظاهر) --}}
                <div class="px-3.5 py-2.5 flex items-center justify-between gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors" onclick="toggleDetails('details_{{ $product->id }}', 'arrow_{{ $product->id }}')">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-gray-700 dark:text-white font-bold bg-gray-200 dark:bg-gray-900 w-7 h-7 rounded-lg flex items-center justify-center text-xs flex-shrink-0">{{ $serialNumber }}</span>
                            <span class="text-gray-800 dark:text-white text-sm font-bold truncate">{{ $product->name }}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 truncate">
                            النوع: {{ $isSet ? 'طقم' : ($isFractional ? 'رول' : 'عادي') }}
                            <span class="mx-1">•</span>
                            المخزون: <span class="{{ $lowStock ? 'text-red-400' : 'text-green-400' }} font-semibold">{{ $displayQty }} {{ $unitName }}</span>
                            <span class="mx-1">•</span>
                            {{ $headerPriceLabel }}: <span class="text-blue-400 font-semibold">{{ number_format($headerPriceValue, 0) }} ر.س</span>
                        </p>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs px-2 py-1 rounded-lg whitespace-nowrap {{ $product->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400' }}">
                            <span class="hidden xs:inline">{{ $product->status === 'active' ? 'نشط' : 'مخفي' }}</span>
                            <i class="fas {{ $product->status === 'active' ? 'fa-eye' : 'fa-eye-slash' }} xs:hidden"></i>
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs transition-transform duration-300" id="arrow_{{ $product->id }}"></i>
                    </div>
                </div>

                <div id="details_{{ $product->id }}" class="hidden border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/20">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <h3 class="text-gray-800 dark:text-white font-bold text-lg">{{ $product->name }}</h3>
                                @if($product->description)
                                    <p class="text-gray-400 text-sm mt-1 break-words">{{ $product->description }}</p>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 items-stretch">
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px]">الكمية</p>
                                    <p class="text-gray-800 dark:text-white font-bold">{{ $displayQty }} {{ $unitName }}</p>
                                </div>
                                @if($isSet)
                                <div class="bg-purple-100 dark:bg-purple-900/20 p-3 rounded-lg border border-purple-200 dark:border-purple-800 h-full flex flex-col justify-between">
                                    <p class="text-purple-400 text-[10px]">مكونات</p>
                                    <p class="text-gray-800 dark:text-white font-bold">{{ $product->items_per_unit }} حبة</p>
                                    <p class="text-gray-400 text-[9px]">إجمالي القطع الحالية: {{ number_format($totalPieces, 0) }} حبة</p>
                                </div>
                                @endif
                                @if($isFractional && $product->roll_length > 0)
                                <div class="bg-blue-100 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800 h-full flex flex-col justify-between">
                                    <p class="text-blue-400 text-[10px]">طول الرول</p>
                                    <p class="text-gray-800 dark:text-white font-bold">{{ $product->roll_length }} متر</p>
                                    <p class="text-gray-400 text-[9px]">إجمالي {{ $totalMeters }} متر</p>
                                </div>
                                @endif
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px]">الحد الأدنى</p>
                                    <p class="text-gray-800 dark:text-white font-bold">{{ $minStockDisplay }} {{ $unitName }}</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px]">القسم</p>
                                    <p class="text-blue-400">{{ $product->category->name ?? 'غير مصنف' }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-stretch">
                                <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px] flex items-center gap-1">
                                        <i class="fa-regular fa-calendar-plus text-green-500"></i>
                                        تاريخ الإضافة
                                    </p>
                                    <p class="text-white text-sm font-medium" dir="ltr">
                                        {{ $product->created_at ? $product->created_at->format('Y-m-d') : '--' }}
                                    </p>
                                    <p class="text-gray-400 text-[9px]" dir="ltr">
                                        {{ $product->created_at ? $product->created_at->format('h:i A') : '' }}
                                    </p>
                                </div>
                                <div class="bg-gray-800/50 p-3 rounded-lg border border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px] flex items-center gap-1">
                                        <i class="fa-regular fa-calendar-check text-blue-500"></i>
                                        آخر تعديل
                                    </p>
                                    <p class="text-white text-sm font-medium" dir="ltr">
                                        {{ $product->updated_at ? $product->updated_at->format('Y-m-d') : '--' }}
                                    </p>
                                    <p class="text-gray-400 text-[9px]" dir="ltr">
                                        {{ $product->updated_at ? $product->updated_at->format('h:i A') : '' }}
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-stretch">
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px]">سعر البيع</p>
                                    <p class="text-blue-400 font-bold text-lg sm:text-xl">{{ number_format($product->price, 0) }} <span class="text-xs text-gray-500">ر.س</span></p>
                                    @if($isSet && $product->piece_price > 0)
                                        <p class="text-gray-400 text-[9px]">الحبة: {{ number_format($product->piece_price, 0) }} ر.س</p>
                                    @endif
                                </div>
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 h-full flex flex-col justify-between">
                                    <p class="text-gray-500 text-[10px]">التكلفة</p>
                                    <p class="text-green-400 font-bold text-lg sm:text-xl">{{ number_format($product->cost_price, 0) }} <span class="text-xs text-gray-500">ر.س</span></p>
                                    <p class="text-gray-400 text-[9px]">إجمالي تكلفة المخزون الحالي: {{ number_format($productCost, 0) }} ر.س</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="bg-gray-800 p-4 rounded-lg">
                                <h3 class="text-white font-bold mb-3 text-sm border-b border-gray-700 pb-2">التحكم</h3>
                                <div class="space-y-2">
                                    <a href="{{ route('user.stores.products.stock', [$store->id, $product->id]) }}"
                                       class="w-full flex items-center justify-center gap-2 py-2.5 bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-400 rounded-lg transition-colors duration-200 border border-indigo-500/30">
                                        <i class="fa-solid fa-warehouse"></i>
                                        <span>إدارة المخزون</span>
                                    </a>

                                    <a href="{{ route('user.stores.products.edit', [$store->id, $product->id]) }}"
                                       class="w-full flex items-center justify-center gap-2 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 rounded-lg transition-colors duration-200 border border-blue-500/30">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        <span>تعديل المنتج</span>
                                    </a>

                                    <form action="{{ route('user.stores.products.toggle-status', [$store->id, $product->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل تريد {{ $product->status === 'active' ? 'إخفاء' : 'تفعيل' }} المنتج؟')">
                                        @csrf @method('PUT')
                                        <button type="submit"
                                                class="w-full flex items-center justify-center gap-2 py-2.5 {{ $product->status === 'active' ? 'bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 border border-yellow-500/30' : 'bg-green-600/20 hover:bg-green-600/30 text-green-400 border border-green-500/30' }} rounded-lg transition-colors duration-200">
                                            <i class="fa-solid {{ $product->status === 'active' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                            <span>{{ $product->status === 'active' ? 'إخفاء المنتج' : 'تفعيل المنتج' }}</span>
                                        </button>
                                    </form>

                                    <form action="{{ route('user.stores.products.destroy', [$store->id, $product->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل تريد نقل المنتج إلى سلة المحذوفات؟')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="w-full flex items-center justify-center gap-2 py-2.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition-colors duration-200 border border-red-500/30">
                                            <i class="fa-solid fa-trash-can"></i>
                                            <span>حذف المنتج</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-gray-800/30 rounded-2xl border border-gray-700">
                <i class="fas fa-box-open text-5xl text-gray-600 mb-4"></i>
                <p class="text-gray-500 text-lg">لا توجد منتجات</p>
                @if(request('search') || request('category_id'))
                    <a href="{{ route('user.stores.products.index', $store->id) }}"
                       class="mt-4 inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-xl">
                        عرض جميع المنتجات
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ===== الترقيم وسلة المحذوفات ===== --}}
    <div class="mt-6 p-4 bg-gray-800/50 rounded-xl border border-gray-700 flex flex-col lg:flex-row items-center justify-between gap-4">
        <div class="w-full lg:w-auto owner-products-pagination">
            {{ $products->links('pagination::simple-tailwind') }}
        </div>

        <a href="{{ route('user.stores.products.trash', $store->id) }}"
           class="flex items-center gap-2 text-gray-400 hover:text-red-400 transition-colors text-sm py-2 px-4 rounded-lg bg-gray-900 border border-gray-700">
            <i class="fa-solid fa-trash-can-arrow-up"></i>
            <span>سلة المحذوفات</span>
            <span class="bg-red-500/20 text-red-400 px-2 py-0.5 rounded-lg text-xs">{{ $trashedCount }}</span>
        </a>
    </div>

    {{-- ===== أدوات الاستيراد/التصدير (بنهاية الصفحة) ===== --}}
    <div class="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-2xl p-4">
        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
            <p class="text-blue-200 text-xs sm:text-sm">
                <i class="fa-solid fa-circle-info ml-1"></i>
                أدوات النقل: الكمية في ملف CSV تكون دائمًا صفر، وإذا كان أحد السعرين غير مكتمل يتم تصفير سعر البيع والتكلفة معًا.
            </p>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('user.stores.products.export.csv', $store->id) }}"
                   class="bg-blue-600/90 hover:bg-blue-500 text-white px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2">
                    <i class="fa-solid fa-file-csv"></i>
                    <span>تصدير CSV</span>
                </a>

                <form action="{{ route('user.stores.products.import.csv', $store->id) }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-2 bg-gray-900/70 border border-gray-700 rounded-xl px-2 py-1.5">
                    @csrf
                    <input type="file" name="csv_file" accept=".csv,text/csv" required class="text-xs text-gray-300 file:ml-2 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-gray-700 file:text-white hover:file:bg-gray-600">
                    <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white px-3 py-2 rounded-lg text-sm flex items-center gap-2">
                        <i class="fa-solid fa-file-import"></i>
                        <span>استيراد</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@php
    $quickProducts = collect($products->items())->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'barcode' => $product->barcode,
            'price_label' => 'سعر البيع: ' . number_format($product->price, 0) . ' ر.س',
            'stock_label' => 'الكمية: ' . number_format($product->quantity, 2),
        ];
    })->values();
@endphp

<script>
function storeProductsLookup() {
    return {
        searchQuery: @json(request('search', '')),
        showAllMatches: false,
        hasServerResults: @json(($products->count() ?? 0) > 0),
        products: @json($quickProducts),
        filteredProducts: [],
        init() {
            this.filterClientProducts();
            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                    e.preventDefault();
                    const input = document.getElementById('searchInput');
                    if (input) input.focus();
                }
            });
        },
        filterClientProducts() {
            const q = (this.searchQuery || '').toLowerCase().trim();
            this.showAllMatches = false;
            if (!q) { this.filteredProducts = this.products; return; }
            this.filteredProducts = this.products.filter((p) =>
                (p.name || '').toLowerCase().includes(q)
                || (p.barcode || '').toLowerCase().includes(q)
                || (p.description || '').toLowerCase().includes(q)
            );
        },
        visibleProducts() {
            if (this.searchQuery) return this.filteredProducts;
            return this.showAllMatches ? this.filteredProducts : this.filteredProducts.slice(0, 5);
        },
        openProductCard(id) {
            const card = document.getElementById(`product-card-${id}`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const detailsId = `details_${id}`;
                const arrowId = `arrow_${id}`;
                const details = document.getElementById(detailsId);
                if (details && details.classList.contains('hidden')) {
                    toggleDetails(detailsId, arrowId);
                }
            }
        }
    }
}

function toggleDetails(detailsId, arrowId) {
    const details = document.getElementById(detailsId);
    const arrow = document.getElementById(arrowId);

    if (details) {
        if (details.classList.contains('hidden')) {
            details.classList.remove('hidden');
            arrow.classList.add('rotate-180');
        } else {
            details.classList.add('hidden');
            arrow.classList.remove('rotate-180');
        }
    }
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('[id^="details_"]').forEach(el => {
            el.classList.add('hidden');
        });
        document.querySelectorAll('[id^="arrow_"]').forEach(el => {
            el.classList.remove('rotate-180');
        });
    }
});
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}

.owner-products-page .owner-products-pagination nav {
    width: auto;
    max-width: 100%;
}

.owner-products-page .owner-products-pagination > nav > div {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 480px) {
    .xs\:inline {
        display: inline;
    }
    .xs\:hidden {
        display: none;
    }
}
</style>

@endsection
