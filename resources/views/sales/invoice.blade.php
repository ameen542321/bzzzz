@extends('dashboard.app')

@section('content')
<div class="bg-white p-10 max-w-3xl mx-auto shadow-lg" style="width: 210mm; min-height: 297mm;">

    <!-- هيدر الفاتورة -->
    <div class="flex justify-between items-center border-b pb-4 mb-6">
        <div>
            <h1 class="text-4xl font-bold" style="color:#d4af37;">CARLED</h1>
            <p class="text-gray-600">نظام المحاسب - فاتورة بيع</p>
        </div>

        <div class="text-right">
            <p class="text-gray-700 font-semibold">رقم الفاتورة: {{ $sale->id }}</p>
            <p class="text-gray-700">التاريخ: {{ $sale->created_at->format('Y-m-d H:i') }}</p>
        </div>
    </div>

    <!-- بيانات المتجر -->
    <div class="mb-6">
        <h2 class="text-xl font-bold mb-2" style="color:#d4af37;">بيانات المتجر</h2>
        <p class="text-gray-700">اسم المتجر: {{ $sale->store->name ?? 'غير محدد' }}</p>
        <p class="text-gray-700">المحاسب: {{ $sale->user->name }}</p>
    </div>

    <!-- جدول المنتجات -->
    <h2 class="text-xl font-bold mb-3" style="color:#d4af37;">تفاصيل المنتجات</h2>

    <table class="w-full border-collapse mb-6">
        <thead>
            <tr class="bg-gray-200 text-gray-700">
                <th class="border p-2">المنتج</th>
                <th class="border p-2">السعر</th>
                <th class="border p-2">الكمية</th>
                <th class="border p-2">الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->items as $item)
                <tr>
                    <td class="border p-2">{{ $item->product->name }}</td>
                    <td class="border p-2">{{ number_format($item->price, 2) }} ريال</td>
                    <td class="border p-2">{{ $item->quantity }}</td>
                    <td class="border p-2">{{ number_format($item->total, 2) }} ريال</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- الإجماليات -->
    <div class="text-right mb-10">
        <p class="text-lg font-semibold">الإجمالي:
            <span style="color:#d4af37;">{{ number_format($sale->total, 2) }} ريال</span>
        </p>

        <p class="text-lg font-semibold">المدفوع:
            <span style="color:#d4af37;">{{ number_format($sale->paid, 2) }} ريال</span>
        </p>

        <p class="text-lg font-semibold">الباقي:
            <span style="color:#d4af37;">{{ number_format($sale->change_amount, 2) }} ريال</span>
        </p>
    </div>

    <!-- زر الطباعة -->
    <div class="text-center mt-10">
        <button
            onclick="window.print()"
            class="px-6 py-3 bg-[#d4af37] text-black font-bold rounded hover:bg-[#c19d2f] transition">
            طباعة الفاتورة
        </button>
    </div>

</div>
@endsection
