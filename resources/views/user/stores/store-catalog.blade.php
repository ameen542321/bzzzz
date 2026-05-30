@extends('dashboard.app')

@section('title', 'لوحة تحكم المتجر – ' . $store->name)

@section('content')

<div class="max-w-7xl mx-auto py-10 space-y-10">

{{-- الهيدر مع زر الرجوع --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">لوحة تحكم المتجر</h1>
            <p class="text-gray-400 mt-2">إدارة متجر: <span class="text-blue-400">{{ $store->name }}</span></p>
        </div>

        {{-- زر الرجوع لصفحة المتاجر --}}
        <a href="{{ route('user.stores.show', $store->id) }}"
           class="flex items-center gap-2 px-5 py-2.5 bg-gray-900 border border-gray-800 text-gray-300 rounded-xl hover:bg-gray-800 hover:text-white transition-all group">
            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
            <span class="font-bold">العودة للمتجر</span>
        </a>
    </div>

{{-- روابط الوصول السريع --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

    {{-- الأقسام --}}
    <a href="{{ route('user.stores.categories.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-layer-group text-orange-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">الأقسام</h2>
                <p class="text-gray-400 mt-1">إدارة أقسام المنتجات</p>
            </div>
        </div>
    </a>

    {{-- المنتجات --}}
    <a href="{{ route('user.stores.products.index', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-box text-purple-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">المنتجات</h2>
                <p class="text-gray-400 mt-1">إضافة وتعديل المنتجات</p>
            </div>
        </div>
    </a>

    {{-- المبيعات --}}
    <a href="{{ route('user.stores.daily', $store->id) }}"
       class="bg-gray-900 border border-gray-800 p-6 rounded-xl hover:bg-gray-800 transition block">
        <div class="flex items-center gap-4">
            <i class="fa-solid fa-cash-register text-green-400 text-4xl"></i>
            <div>
                <h2 class="text-xl font-bold text-white">المبيعات</h2>
                <p class="text-gray-400 mt-1">إدارة المبيعات والفواتير</p>
            </div>
        </div>
    </a>

</div>

{{-- البطاقات الإحصائية --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    @php
        // حساب إحصائيات المنتجات لجميع منتجات المتجر
        $productsStats = App\Models\Product::where('store_id', $store->id)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(
                    CASE
                        WHEN product_type = "fractional" AND roll_length > 0 THEN (quantity / roll_length) * COALESCE(cost_price, 0)
                        ELSE quantity * COALESCE(cost_price, 0)
                    END
                ) as total_cost_value,
                SUM(
                    CASE
                        WHEN product_type = "fractional" AND roll_length > 0 THEN (quantity / roll_length) * price
                        ELSE quantity * price
                    END
                ) as total_market_value
            ')
            ->first();
    @endphp

    {{-- إجمالي التكلفة --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-money-bill-wave text-green-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">إجمالي التكلفة</h3>
        <p class="text-3xl font-bold text-green-400">
            {{ number_format($productsStats->total_cost_value ?? 0, 0) }}
            <span class="text-sm text-gray-400">ر.س</span>
        </p>
        <p class="text-gray-500 text-xs mt-2">قيمة المخزون حسب التكلفة</p>
    </div>

    {{-- القيمة السوقية --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-chart-line text-blue-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">القيمة السوقية</h3>
        <p class="text-3xl font-bold text-blue-400">
            {{ number_format($productsStats->total_market_value ?? 0, 0) }}
            <span class="text-sm text-gray-400">ر.س</span>
        </p>
        <p class="text-gray-500 text-xs mt-2">قيمة المخزون حسب البيع</p>
    </div>

    {{-- عدد المنتجات --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-boxes-stacked text-purple-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">عدد المنتجات</h3>
        <p class="text-3xl font-bold text-purple-400">
            {{ $productsStats->total_count ?? 0 }}
            <span class="text-sm text-gray-400">منتج</span>
        </p>
        <p class="text-gray-500 text-xs mt-2">إجمالي أنواع المنتجات</p>
    </div>

    {{-- منخفض المخزون --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center hover:bg-gray-800 transition">
        <i class="fa-solid fa-exclamation-triangle text-yellow-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm mb-1">منخفض المخزون</h3>
        <p class="text-3xl font-bold text-yellow-400">
            {{ $lowStockCount ?? 0 }}
            <span class="text-sm text-gray-400">منتج</span>
        </p>
        <p class="text-gray-500 text-xs mt-2">تحت الحد الأدنى</p>
    </div>

</div>

{{-- ملاحظة: إبقاء بطاقاتك الأصلية لعدد الأقسام والمحذوفات --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    {{-- عدد الأقسام --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
        <i class="fa-solid fa-layer-group text-orange-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm">عدد الأقسام</h3>
        <p class="text-3xl font-bold text-orange-400">{{ $categoriesCount }}</p>
    </div>

    {{-- سلة المحذوفات --}}
    <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl text-center">
        <i class="fa-solid fa-trash-can text-red-400 text-3xl mb-3"></i>
        <h3 class="text-gray-400 text-sm">سلة المحذوفات</h3>
        <p class="text-3xl font-bold text-red-400">{{ $trashedCount }}</p>
    </div>
</div>

    {{-- آخر المنتجات --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">آخر المنتجات المضافة</h2>
            <a href="{{ route('user.stores.products.index', $store->id) }}"
               class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1">
                <span>عرض الكل</span>
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <table class="w-full text-right">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-sm">
                    <th class="py-3 px-4">المنتج</th>
                    <th class="py-3 px-4 text-center">القسم</th>
                    <th class="py-3 px-4 text-center">السعر</th>
                    <th class="py-3 px-4 text-center">الكمية</th>
                    <th class="py-3 px-4 text-center">القيمة</th>
                </tr>
            </thead>

            <tbody class="text-gray-300 text-sm">
                @forelse($latestProducts as $product)
                    @php
                        $displayQuantity = $product->quantity;
                        $displayMinStock = $product->min_stock;

                        if ($product->product_type === 'fractional' && $product->roll_length > 0) {
                            $displayQuantity = $product->quantity / $product->roll_length;
                        }

                        $productValue = $displayQuantity * $product->price;
                        $isLowStock = $displayQuantity <= $displayMinStock;
                    @endphp
                    <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                @if($isLowStock)
                                    <span class="text-red-500 text-xs" title="مخزون منخفض">
                                        <i class="fa-solid fa-exclamation-circle"></i>
                                    </span>
                                @endif
                                <span>{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-1 rounded bg-gray-800 text-xs">
                                {{ $product->category->name ?? '—' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono">
                            {{ number_format($product->price, 0) }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="{{ $isLowStock ? 'text-red-400' : 'text-green-400' }} font-bold">
                                {{ number_format($displayQuantity, 2) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-center font-mono text-blue-400">
                            {{ number_format($productValue, 0) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-box-open text-3xl mb-2"></i>
                                <p>لا توجد منتجات مضافة حديثًا</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- منخفض المخزون --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">منتجات منخفضة المخزون</h2>
            <a href="{{ route('user.stores.supply.index', $store->id) }}"
               class="text-yellow-400 hover:text-yellow-300 text-sm flex items-center gap-1">
                <span>عرض الكل</span>
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <table class="w-full text-right">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-sm">
                    <th class="py-3 px-4">المنتج</th>
                    <th class="py-3 px-4 text-center">الكمية الحالية</th>
                    <th class="py-3 px-4 text-center">الحد الأدنى</th>
                    <th class="py-3 px-4 text-center">النسبة</th>
                    <th class="py-3 px-4 text-center">الحالة</th>
                </tr>
            </thead>

            <tbody class="text-gray-300 text-sm">
                @forelse($lowStockProducts as $product)
                    @php
                        $displayQuantity = $product->quantity;
                        $displayMinStock = $product->min_stock;

                        if ($product->product_type === 'fractional' && $product->roll_length > 0) {
                            $displayQuantity = $product->quantity / $product->roll_length;
                        }

                        $percentage = $displayMinStock > 0
                            ? ($displayQuantity / $displayMinStock) * 100
                            : 0;
                        $isCritical = $displayQuantity <= 0;
                        $isVeryLow = $percentage <= 50;
                    @endphp
                    <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                        <td class="py-3 px-4">{{ $product->name }}</td>
                        <td class="py-3 px-4 text-center font-bold {{ $isCritical ? 'text-red-500' : 'text-yellow-400' }}">
                            {{ number_format($displayQuantity, 2) }}
                        </td>
                        <td class="py-3 px-4 text-center">{{ number_format($displayMinStock, 2) }}</td>
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1">
                                <div class="w-20 h-2 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full {{ $isCritical ? 'bg-red-500' : ($isVeryLow ? 'bg-yellow-500' : 'bg-orange-500') }}"
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                <span class="text-xs {{ $isCritical ? 'text-red-400' : ($isVeryLow ? 'text-yellow-400' : 'text-orange-400') }}">
                                    {{ number_format($percentage, 0) }}%
                                </span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($isCritical)
                                <span class="px-3 py-1 rounded bg-red-900/50 text-red-300 text-xs font-bold">نفذ</span>
                            @elseif($isVeryLow)
                                <span class="px-3 py-1 rounded bg-yellow-900/50 text-yellow-300 text-xs font-bold">منخفض جدًا</span>
                            @else
                                <span class="px-3 py-1 rounded bg-orange-900/50 text-orange-300 text-xs font-bold">منخفض</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-check-circle text-3xl mb-2 text-green-500"></i>
                                <p>جميع المنتجات في مستوى جيد</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- سجل الحركات --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">آخر حركات المخزون</h2>
            <a href="{{ route('user.stores.products.index', $store->id) }}"
               class="text-blue-400 hover:text-blue-300 text-sm flex items-center gap-1">
                <span>عرض الكل</span>
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
        </div>

        <table class="w-full text-right">
            <thead>
                <tr class="text-gray-400 border-b border-gray-800 text-sm">
                    <th class="py-3 px-4">التاريخ</th>
                    <th class="py-3 px-4 text-center">المنتج</th>
                    <th class="py-3 px-4 text-center">النوع</th>
                    <th class="py-3 px-4 text-center">الكمية</th>
                    <th class="py-3 px-4">ملاحظة</th>
                </tr>
            </thead>

            <tbody class="text-gray-300 text-sm">
                @forelse($latestMovements as $move)
                    @php
                        $product = $move->product ?? null;
                    @endphp
                    <tr class="border-b border-gray-800 hover:bg-gray-800/50 transition">
                        <td class="py-3 px-4">
                            <div class="flex flex-col">
                                <span class="font-mono text-xs">{{ $move->created_at->format('Y-m-d') }}</span>
                                <span class="text-gray-500 text-xs">{{ $move->created_at->format('H:i') }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($product)
                                <span class="line-clamp-1 max-w-[150px]" title="{{ $product->name }}">
                                    {{ $product->name }}
                                </span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center">
                            @if($move->type === 'increase')
                                <span class="flex items-center justify-center gap-1 px-3 py-1 rounded bg-green-900/30 text-green-300 text-xs">
                                    <i class="fa-solid fa-plus text-[8px]"></i>
                                    إضافة
                                </span>
                            @else
                                <span class="flex items-center justify-center gap-1 px-3 py-1 rounded bg-red-900/30 text-red-300 text-xs">
                                    <i class="fa-solid fa-minus text-[8px]"></i>
                                    خصم
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-center font-mono font-bold">
                            {{ number_format($move->quantity, 2) }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="line-clamp-1" title="{{ $move->note ?? 'بدون ملاحظة' }}">
                                {{ $move->note ?: '—' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fa-solid fa-history text-3xl mb-2"></i>
                                <p>لا توجد حركات مخزون</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

<style>
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
</style>

@endsection
