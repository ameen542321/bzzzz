@extends('dashboard.app')

@section('title', 'بحث المنتجات - ' . $store->name)

@section('content')
<div class="accountant-product-search max-w-7xl mx-auto px-4 py-6 text-right" dir="rtl" x-data="productLookup()" x-init="init()">

    {{-- ===== الهيدر العلوي ===== --}}
    <div class="mb-6 bg-gray-800 border border-gray-700 p-4 rounded-2xl shadow-sm">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('accountant.dashboard') }}"
                   class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                        <i class="fas fa-search text-blue-400"></i>
                        بحث المنتجات
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">{{ $store->name }}</p>
                </div>
            </div>

            <div class="text-gray-400 text-sm bg-gray-100 dark:bg-gray-700 px-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600">
                المحاسب: <span class="font-bold text-blue-400">{{ $accountant->name }}</span>
            </div>
        </div>
    </div>

    {{-- ===== البحث السريع (بدون قوائم Select) ===== --}}
    <div class="mb-6 bg-gray-800 border border-gray-700 p-4 rounded-2xl shadow-sm space-y-3">
        <form method="GET" action="{{ route('accountant.pos.searchProduct') }}" class="flex flex-col sm:flex-row gap-2 w-full">
            <div class="relative flex-grow">
                <input type="text" name="search" value="{{ request('search') }}"
                       x-model="searchQuery"
                       @input="filterClientProducts"
                       placeholder="🔍 ابحث باسم المنتج أو الباركود لمعرفة السعر والكمية"
                       class="bg-gray-900 border border-gray-700 rounded-xl py-2.5 px-4 pr-10 text-sm text-white w-full focus:border-blue-400 focus:ring-2 focus:ring-blue-400/20 transition"
                       id="searchInput" autofocus>
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600 text-white px-6 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 justify-center shadow-sm hover:shadow-md">
                <i class="fas fa-search text-sm"></i>
                <span>بحث</span>
            </button>

            @if(request('search') || request('category_id'))
                <a href="{{ route('accountant.pos.searchProduct') }}"
                   class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-white px-4 py-2.5 rounded-xl transition-all duration-200 flex items-center gap-2 justify-center">
                    <i class="fas fa-times"></i>
                    <span>إلغاء</span>
                </a>
            @endif
        </form>

        <div class="border border-gray-700 rounded-xl p-2 bg-gray-900/40 max-h-52 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <template x-for="p in filteredProducts" :key="p.id">
                    <button type="button" @click="openProductCard(p.id)"
                        class="text-right bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 hover:border-blue-500 transition">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-bold text-sm text-white truncate" x-text="p.name"></p>
                            <span class="text-[10px] text-gray-500" x-text="p.barcode || 'بدون باركود'"></span>
                        </div>
                        <p class="text-xs text-blue-400 mt-1" x-text="p.price_label"></p>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="p.stock_label"></p>
                    </button>
                </template>
            </div>
            <div x-show="searchQuery && filteredProducts.length === 0" class="text-xs text-yellow-400 p-2">
                لا توجد نتائج مطابقة.
            </div>
        </div>
    </div>

    {{-- ===== بطاقات المنتجات ===== --}}
    <div class="space-y-3">
        @forelse($products as $index => $product)
            @php
                // استخدام القيم المحسوبة من الكونترولر
                $isFractional = $product->is_fractional;
                $isSet = $product->is_set;
                $isNormal = $product->is_normal;

                // تناوب الألوان للبطاقات - مع دعم الوضع الداكن
                $bgCardColor = $loop->iteration % 2 == 0
                    ? 'bg-gray-100 dark:bg-gray-800/40'
                    : 'bg-white dark:bg-gray-800/80';

                // تحسين عرض الكمية مع الاعتماد على البيانات المهيأة من الكنترولر
                $displayQty = $product->display_quantity ?? number_format($product->quantity, 2);
                $unitName = $product->display_unit ?? 'قطعة';
                $displayMinStock = $product->display_min_stock ?? number_format($product->min_stock, 2);
                $lowStock = $product->low_stock ?? false;
                $serialNumber = $loop->iteration;

                if ($isSet) {
                    $totalPieces = $product->total_pieces ?? ($product->quantity * ($product->items_per_unit ?: 1));
                } elseif ($isFractional) {
                    $totalMeters = $product->total_meters ?? number_format($product->quantity, 2);
                }

                // تحديد نوع المنتج للعرض
                if ($isSet) {
                    $typeColor = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800';
                    $typeText = 'طقم';
                } elseif ($isFractional) {
                    $typeColor = 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800';
                    $typeText = 'رول';
                } else {
                    $typeColor = 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600';
                    $typeText = 'عادي';
                }
            @endphp

            <div id="product-card-{{ $product->id }}" class="{{ $bgCardColor }} rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all hover:shadow-md">
                {{-- رأس البطاقة --}}
                <div class="px-4 py-3 flex flex-wrap items-center justify-between gap-3 cursor-pointer" onclick="toggleDetails('details_{{ $product->id }}', 'arrow_{{ $product->id }}')">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        {{-- ✅✅✅ الرقم المسلسل المحسن --}}
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white font-bold text-xs shadow-sm border border-blue-400 dark:border-blue-500">
                            {{ $serialNumber }}
                        </span>

                        {{-- نوع المنتج --}}
                        <span class="px-2.5 py-1 rounded-full text-[11px] font-medium flex-shrink-0 {{ $typeColor }}">
                            {{ $typeText }}
                        </span>

                        {{-- اسم المنتج --}}
                        <span class="text-white text-sm font-bold truncate flex-1">{{ $product->name }}</span>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        {{-- المخزون مع الكسور --}}
                        <span class="text-gray-400 text-sm whitespace-nowrap">
                            <span class="hidden sm:inline">المخزون: </span>
                            <span class="{{ $lowStock ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} font-bold">
                                {{ $displayQty }} {{ $unitName }}
                            </span>
                        </span>

                        {{-- سعر البيع --}}
                        <span class="text-gray-400 text-sm whitespace-nowrap hidden md:inline border-r border-gray-300 dark:border-gray-700 pr-2 mr-1">
                            <span class="text-blue-400 font-bold">
                                @if($isSet)
                                    طقم: {{ number_format($product->price, 0) }} ر.س
                                @elseif($isFractional)
                                    رول: {{ number_format($product->price, 0) }} ر.س
                                @else
                                    بيع: {{ number_format($product->price, 0) }} ر.س
                                @endif
                            </span>
                        </span>

                        {{-- سعر الحبة المفردة للأطقم --}}
                        @if($isSet && $product->piece_price > 0)
                        <span class="text-gray-400 text-sm whitespace-nowrap hidden md:inline">
                            <span class="text-blue-400 font-bold">
                                حبة: {{ number_format($product->piece_price, 0) }} ر.س
                            </span>
                        </span>
                        @endif

                        {{-- سعر المتر للرولات --}}
                        @if($isFractional && $product->roll_length > 0 && isset($product->meter_price))
                        <span class="text-gray-400 text-sm whitespace-nowrap hidden md:inline">
                            <span class="text-blue-400 font-bold">
                                متر: {{ $product->meter_price }} ر.س
                            </span>
                        </span>
                        @endif

                        {{-- سهم التفاصيل --}}
                        <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs transition-transform duration-300 flex-shrink-0" id="arrow_{{ $product->id }}"></i>
                    </div>
                </div>

                {{-- التفاصيل --}}
                <div id="details_{{ $product->id }}" class="hidden border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50/50 dark:bg-gray-800/20">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                        {{-- العمود الأيمن: معلومات المنتج --}}
                        <div class="lg:col-span-2 space-y-4">
                            {{-- اسم المنتج كامل --}}
                            <div>
                                <h3 class="text-white font-bold text-lg">{{ $product->name }}</h3>
                                @if($product->description)
                                    <p class="text-gray-400 text-sm mt-1">{{ $product->description }}</p>
                                @endif
                            </div>

                            {{-- معلومات المخزون --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-400 text-[10px] font-medium">الكمية</p>
                                    <p class="text-white font-bold text-lg">{{ $displayQty }} {{ $unitName }}</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-400 text-[10px] font-medium">الحد الأدنى</p>
                                    <p class="{{ $lowStock ? 'text-red-400' : 'text-white' }} font-bold text-lg">{{ $displayMinStock }} {{ $unitName }}</p>
                                </div>

                                @if($isSet)
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-400 text-[10px] font-medium">الكمية بالحبات</p>
                                    <p class="text-white font-bold text-lg">
                                        {{ number_format($product->total_pieces ?? ($product->quantity * ($product->items_per_unit ?: 1)), 2) }} حبة
                                    </p>
                                </div>
                                @endif

                                @if($isFractional && $product->roll_length > 0)
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <p class="text-blue-700 dark:text-blue-400 text-[10px] font-medium">طول الرول</p>
                                    <p class="text-white font-bold text-lg">{{ $product->roll_length }} متر</p>
                                    <p class="text-gray-400 text-[9px] mt-1">إجمالي {{ $product->total_meters ?? number_format($product->quantity, 2) }} متر</p>
                                </div>
                                @endif
                            </div>

                            {{-- الأسعار --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-400 text-[10px] font-medium">
                                        @if($isSet)
                                            سعر الطقم كامل
                                        @elseif($isFractional)
                                            سعر الرول
                                        @else
                                            سعر البيع
                                        @endif
                                    </p>
                                    <p class="text-blue-400 font-bold text-2xl">
                                        {{ number_format($product->price, 0) }}
                                        <span class="text-xs text-gray-400 font-normal">ر.س</span>
                                    </p>
                                </div>

                                @if($isSet && $product->piece_price > 0)
                                <div class="bg-gray-100 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="text-gray-400 text-[10px] font-medium">سعر الحبة المفردة</p>
                                    <p class="text-blue-400 font-bold text-2xl">
                                        {{ number_format($product->piece_price, 0) }}
                                        <span class="text-xs text-gray-400 font-normal">ر.س</span>
                                    </p>
                                </div>
                                @elseif($isFractional && $product->roll_length > 0 && isset($product->meter_price))
                                <div class="bg-blue-100 dark:bg-blue-900/30 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                                    <p class="text-blue-700 dark:text-blue-400 text-[10px] font-medium">سعر المتر</p>
                                    <p class="text-blue-400 font-bold text-2xl">
                                        {{ $product->meter_price }}
                                        <span class="text-xs text-gray-400 font-normal">ر.س</span>
                                    </p>
                                </div>
                                @endif
                            </div>

                            {{-- معلومات إضافية للأطقم --}}
                            @if($isSet && isset($product->total_pieces))
                            <div class="bg-gray-100/50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-700 dark:text-gray-300 text-sm font-medium mb-3 flex items-center gap-2">
                                    <i class="fas fa-cubes text-blue-400"></i>
                                    تفاصيل الطقم
                                </p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">عدد الأطقم</p>
                                        <p class="text-white font-bold text-lg">{{ number_format($product->quantity, 2) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">حبات في الطقم</p>
                                        <p class="text-white font-bold text-lg">{{ $product->items_per_unit }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">إجمالي الحبات</p>
                                        <p class="text-white font-bold text-lg">{{ number_format($product->total_pieces, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- معلومات إضافية للرولات --}}
                            @if($isFractional && $product->roll_length > 0)
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-800">
                                <p class="text-blue-700 dark:text-blue-400 text-sm font-medium mb-3 flex items-center gap-2">
                                    <i class="fas fa-ruler text-blue-400"></i>
                                    تفاصيل الرول
                                </p>
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">عدد الرولات</p>
                                        <p class="text-white font-bold text-lg">{{ number_format($product->display_rolls ?? ($product->quantity / $product->roll_length), 2) }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">طول الرول</p>
                                        <p class="text-white font-bold text-lg">{{ $product->roll_length }} م</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-gray-400 text-xs">إجمالي الأمتار</p>
                                        <p class="text-white font-bold text-lg">{{ $product->total_meters ?? number_format($product->quantity, 2) }} م</p>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- خيارات التجزئة للمنتجات الكسرية --}}
                            @if($isFractional && $product->fractions && $product->fractions->count() > 0)
                            <div class="bg-gray-100/50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <p class="text-gray-700 dark:text-gray-300 text-sm font-medium mb-3 flex items-center gap-2">
                                    <i class="fas fa-tags text-blue-400"></i>
                                    خيارات التجزئة المتاحة
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($product->fractions as $fraction)
                                        <span class="text-xs bg-white dark:bg-gray-900 text-blue-400 px-3 py-1.5 rounded-full border border-blue-200 dark:border-blue-800 shadow-sm">
                                            {{ $fraction->option_label }} ({{ number_format($fraction->price, 2) }} ر.س)
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700">
                <i class="fas fa-box-open text-5xl text-gray-400 dark:text-gray-500 mb-4"></i>
                <p class="text-gray-400 text-lg">لا توجد منتجات</p>
                @if(request('search') || request('category_id'))
                    <a href="{{ route('accountant.pos.searchProduct') }}"
                       class="mt-4 inline-block bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-white px-6 py-2 rounded-xl transition">
                        عرض جميع المنتجات
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    {{-- ===== الترقيم ===== --}}
    @if($products->hasPages())
        <div class="mt-6 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
            {{ $products->links() }}
        </div>
    @endif
</div>

@php
    $quickProducts = collect($products->items())->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'barcode' => $product->barcode,
            'price_label' => 'سعر البيع: ' . number_format($product->price, 0) . ' ر.س',
            'stock_label' => 'الكمية: ' . number_format($product->quantity, 2),
        ];
    })->values();
@endphp

{{-- JavaScript للتحكم في إظهار/إخفاء التفاصيل --}}
<script>
function productLookup() {
    return {
        searchQuery: @json(request('search', '')),
        products: @json($quickProducts),
        filteredProducts: [],
        init() {
            this.filterClientProducts();
        },
        filterClientProducts() {
            const q = (this.searchQuery || '').toLowerCase().trim();
            if (!q) {
                this.filteredProducts = this.products;
                return;
            }
            this.filteredProducts = this.products.filter((p) =>
                (p.name || '').toLowerCase().includes(q) || (p.barcode || '').toLowerCase().includes(q)
            );
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

// إغلاق جميع التفاصيل عند الضغط على ESC
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

// التركيز على حقل البحث
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput')?.focus();
});
</script>

<style>
.rotate-180 {
    transform: rotate(180deg);
}


/* إجبار ألوان داكنة في الجوال لهذه الصفحة */
@media (max-width: 1024px) {
  .accountant-product-search .bg-white,
  .accountant-product-search .bg-gray-100,
  .accountant-product-search .bg-blue-100,
  .accountant-product-search .bg-blue-50,
  .accountant-product-search .bg-gray-50\/50,
  .accountant-product-search .bg-gray-100\/50 {
      background-color: #1f2937 !important;
  }

  .accountant-product-search .border-gray-200,
  .accountant-product-search .border-blue-200,
  .accountant-product-search .border-gray-300 {
      border-color: #374151 !important;
  }

  .accountant-product-search .text-gray-800,
  .accountant-product-search .text-blue-700,
  .accountant-product-search .text-gray-700 {
      color: #f3f4f6 !important;
  }
}
</style>
@endsection
