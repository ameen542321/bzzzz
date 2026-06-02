@extends('dashboard.app')

@section('title', 'إدارة مخزون المنتج – ' . $product->name)

@section('content')

<div class="max-w-5xl mx-auto py-10 px-4 sm:px-6">

    {{-- الهيدر --}}
    <div class="flex items-center justify-between mb-8 gap-4">
        <a href="{{ route('user.stores.products.index', $store->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white transition shadow-sm">
            <i class="fa-solid fa-arrow-right text-sm"></i>
            <span class="text-sm font-medium">رجوع</span>
        </a>

        <div class="text-center flex-1">
            <h1 class="text-xl md:text-2xl font-bold text-white">إدارة مخزون المنتج</h1>
            <p class="text-blue-400 text-xs md:text-sm font-medium mt-1">
                {{ $product->name }}
                <span class="text-gray-600 mx-2">|</span>
                @if($product->product_type === 'fractional')
                    <i class="fa-solid fa-scissors text-[10px] ml-1"></i> منتج مجزأ (أمتار)
                @elseif($product->is_splittable)
                    <i class="fa-solid fa-boxes-stacked text-[10px] ml-1"></i> نظام أطقم (قابل للتجزئة)
                @else
                    <i class="fa-solid fa-box text-[10px] ml-1"></i> منتج عادي
                @endif
            </p>
        </div>

        <div class="hidden md:block w-32"></div>
    </div>

    @php
        $isFractional = ($product->product_type === 'fractional' && $product->roll_length > 0);
        $isSet = $product->is_splittable;

        if ($isFractional) {
            $displayQuantity = $product->quantity / $product->roll_length;
            $displayMinStock = $product->min_stock;
            $unitLabel = 'رول';
        } elseif ($isSet) {
            $displayQuantity = $product->quantity;
            $displayMinStock = $product->min_stock;
            $unitLabel = 'طقم';
        } else {
            $displayQuantity = $product->quantity;
            $displayMinStock = $product->min_stock;
            $unitLabel = 'حبة';
        }
        $isLowStock = $displayQuantity <= $displayMinStock;
    @endphp

    {{-- بطاقات المعلومات السريعة --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- الرصيد الحالي --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-lg relative overflow-hidden">
            <div class="relative z-10 text-center">
                <p class="text-gray-500 text-xs uppercase font-bold tracking-widest mb-2">الرصيد الحالي ({{ $unitLabel }})</p>
                <p class="text-4xl font-black text-white">{{ number_format($displayQuantity, 2) }}</p>
                @if($isSet)
                    <div class="mt-3 pt-3 border-t border-gray-800 flex justify-center gap-4">
                        <span class="text-[11px] text-purple-400 font-bold bg-purple-500/10 px-2 py-0.5 rounded">
                            إجمالي: {{ number_format($product->quantity * $product->items_per_unit, 0) }} حبة
                        </span>
                    </div>
                @elseif($isFractional)
                    <div class="mt-3 pt-3 border-t border-gray-800">
                         <span class="text-[11px] text-blue-400 font-bold">إجمالي: {{ number_format($product->quantity, 2) }} متر</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- حد التنبيه --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-lg text-center">
            <p class="text-gray-500 text-xs uppercase font-bold tracking-widest mb-2">حد الأمان (تنبيه)</p>
            <p class="text-4xl font-black text-gray-400">{{ number_format($displayMinStock, 2) }}</p>
            <p class="text-[10px] text-gray-600 mt-2 italic">يتم التنبيه عند الوصول لهذا الحد</p>
        </div>

        {{-- الحالة --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center">
            <p class="text-gray-500 text-xs uppercase font-bold tracking-widest mb-3">حالة المستودع</p>
            @if($isLowStock)
                <div class="bg-red-500/10 border border-red-500/20 text-red-500 px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm">
                    <span class="w-2 h-2 bg-red-500 rounded-full animate-ping"></span>
                    مخزون منخفض
                </div>
            @else
                <div class="bg-green-500/10 border border-green-500/20 text-green-400 px-4 py-2 rounded-full flex items-center gap-2 font-bold text-sm">
                    <i class="fa-solid fa-check-circle"></i>
                    مستوى آمن
                </div>
            @endif
        </div>
    </div>

    {{-- نماذج العمليات (توريد / سحب) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">

        {{-- نموذج الزيادة --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-xl border-t-4 border-t-green-600">
            <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-plus text-green-500"></i> توريد للمخزن
            </h3>
            <form action="{{ route('user.stores.products.stock.increase', [$store->id, $product->id]) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-[2]">
                            <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">الكمية</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" required
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500/50 outline-none transition"
                                   placeholder="0.00">
                        </div>
                        @if($isSet)
                        <div class="flex-1">
                            <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">الوحدة</label>
                            <select name="unit_type" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-2 py-3 focus:ring-2 focus:ring-green-500/50 outline-none cursor-pointer">
                                <option value="unit">طقم</option>
                                <option value="piece">حبة مفردة</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">ملاحظة العمليّة</label>
                        <input type="text" name="note" placeholder="مثلاً: توريد بضاعة جديدة، مرتجع من عميل..."
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500/50 outline-none transition">
                    </div>

                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-4 rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-green-900/20">
                        تأكيد زيادة المخزون
                    </button>
                </div>
            </form>
        </div>

        {{-- نموذج السحب --}}
        <div class="bg-gray-900 border border-gray-800 p-6 rounded-2xl shadow-xl border-t-4 border-t-red-600">
            <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                <i class="fa-solid fa-circle-minus text-red-500"></i> سحب / عجز / إتلاف
            </h3>
            <form action="{{ route('user.stores.products.stock.decrease', [$store->id, $product->id]) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="flex-[2]">
                            <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">الكمية</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" required
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500/50 outline-none transition"
                                   placeholder="0.00">
                        </div>
                        @if($isSet)
                        <div class="flex-1">
                            <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">الوحدة</label>
                            <select name="unit_type" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-2 py-3 focus:ring-2 focus:ring-red-500/50 outline-none cursor-pointer">
                                <option value="unit">طقم</option>
                                <option value="piece">حبة مفردة</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-gray-400 text-[10px] uppercase font-bold mb-1 ml-1">سبب السحب</label>
                        <input type="text" name="note" placeholder="مثلاً: كسر، تالف، جرد دوري..."
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-red-500/50 outline-none transition">
                    </div>

                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-xl transition-all active:scale-[0.98] shadow-lg shadow-red-900/20">
                        تأكيد سحب الكمية
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- سجل الحركات الأخير --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl overflow-hidden">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between">
            <h3 class="text-white font-bold flex items-center gap-2">
                <i class="fa-solid fa-history text-blue-500"></i> سجل الحركات الأخيرة
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-800/40 text-gray-500 uppercase text-[10px] tracking-widest">
                    <tr>
                        <th class="py-4 px-6 font-medium">العمليّة</th>
                        <th class="py-4 px-6 font-medium">الكمية ({{ $unitLabel }})</th>
                        <th class="py-4 px-6 font-medium">الرصيد قبل</th>
                        <th class="py-4 px-6 font-medium">الرصيد بعد</th>
                        <th class="py-4 px-6 font-medium">المستخدم</th>
                        <th class="py-4 px-6 font-medium">التاريخ والوقت</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @forelse($movements as $move)
                        @php
                            $moveQty = $isFractional ? ($move->quantity / $product->roll_length) : $move->quantity;
                            $beforeQty = $isFractional ? ($move->previous_balance / $product->roll_length) : $move->previous_balance;
                            $afterQty = $isFractional ? ($move->current_balance / $product->roll_length) : $move->current_balance;
                        @endphp
                        <tr class="hover:bg-gray-800/20 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-bold {{ $move->type === 'increase' ? 'text-green-500' : 'text-red-500' }}">
                                        {{ $move->type === 'increase' ? '↑ توريد' : '↓ سحب' }}
                                    </span>
                                    @if($move->note)
                                        <span class="text-[10px] text-gray-500 italic mt-1">{{ $move->note }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono font-bold text-white text-base">
                                    {{ number_format($moveQty, 2) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono text-gray-300">
                                    {{ number_format($beforeQty, 2) }}
                                </span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-mono text-blue-400 font-bold">
                                    {{ number_format($afterQty, 2) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-gray-400">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center text-[10px] text-blue-400 border border-gray-700">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    {{ $move->user->name ?? 'نظام' }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-[11px] text-gray-500 font-mono">
                                {{ $move->created_at->format('Y-m-d') }}<br>
                                {{ $move->created_at->format('H:i A') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <i class="fa-solid fa-inbox text-gray-800 text-5xl mb-4 block"></i>
                                <p class="text-gray-600 italic">لا توجد حركات مخزنية مسجلة حتى الآن</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[type="number"]').forEach((input) => {
            input.addEventListener('wheel', function(event) {
                event.preventDefault();
            }, { passive: false });
        });
    });
</script>
@endsection
