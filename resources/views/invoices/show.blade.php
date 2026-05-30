@extends('dashboard.app')

@section('title', 'تفاصيل فاتورة #' . $invoice->invoice_number)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6 text-right" dir="rtl">

    @php
        $isOwnerContext = isset($store);
        $backUrl = $isOwnerContext
            ? route('user.stores.invoices.index', $store->id)
            : route('accountant.invoices.index');

        $printUrl = $isOwnerContext
            ? route('user.stores.invoices.print', [$store->id, $invoice->id])
            : route('accountant.quick-sale.invoice.print', $invoice->id);

        $pdfUrl = $isOwnerContext
            ? route('user.stores.invoices.pdf', [$store->id, $invoice->id])
            : route('accountant.quick-sale.invoice.pdf', $invoice->id);

        $editUrl = $isOwnerContext
            ? route('user.stores.invoices.edit', [$store->id, $invoice->id])
            : route('accountant.invoices.edit', $invoice->id);

        $paymentType = match (optional($invoice->sale)->sale_type) {
            'cash' => 'نقداً',
            'card' => 'شبكة',
            'mixed' => 'مختلط',
            'credit' => 'آجل',
            'internal_use' => 'نقداً',
            default => 'غير محدد',
        };

        $accountantName = optional(optional($invoice->sale)->accountant)->name
            ?? optional(optional(optional($invoice->sale)->store)->user)->name
            ?? '—';

        $manualRows = collect(preg_split('/\r\n|\r|\n/', (string) ($invoice->description ?? '')))
            ->map(fn ($line) => trim((string) $line))
            ->filter()
            ->reject(fn ($line) => in_array($line, ['وصف العمل:', 'المنتجات المدخلة:'], true))
            ->map(function ($line) use ($invoice) {
                $line = trim((string) preg_replace('/^-\s*/u', '', $line));
                preg_match('/^(.*?)\s*\(الكمية:\s*([0-9.,]+)\s*×\s*السعر:\s*([0-9.,]+)(?:\s*=\s*([0-9.,]+))?/u', $line, $matches);

                $description = trim((string) ($matches[1] ?? $line));
                $qty = (float) str_replace(',', '', (string) ($matches[2] ?? 1));
                $unitPrice = (float) str_replace(',', '', (string) ($matches[3] ?? 0));
                $lineSubtotal = $qty * $unitPrice;
                $lineTax = $lineSubtotal * (((float) ($invoice->tax_rate ?? 0)) / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                return [
                    'description' => $description,
                    'quantity' => $qty > 0 ? $qty : 1,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();
    @endphp

    {{-- Header --}}
    <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ $backUrl }}" class="p-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-xl transition-all">
                <svg class="w-6 h-6 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-white">تفاصيل الفاتورة <span class="font-mono text-blue-400">#{{ $invoice->invoice_number }}</span></h1>
                <p class="text-xs text-gray-500 mt-1">تاريخ الإصدار: {{ $invoice->created_at->format('Y/m/d - h:i A') }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ $printUrl }}" target="_blank" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2z"/></svg>
                طباعة
            </a>
            <a href="{{ $pdfUrl }}" class="inline-flex items-center gap-2 bg-cyan-600 hover:bg-cyan-700 text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v12m0 0l-4-4m4 4l4-4M4 20h16"/></svg>
                تحميل PDF
            </a>
            <a href="{{ $editUrl }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.586a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/></svg>
                تعديل الفاتورة
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- left main --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Customer + vehicle + store --}}
            <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6 space-y-5">
                <h3 class="text-gray-300 text-sm font-black">بيانات الفاتورة</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-800">
                        <div class="text-gray-500 text-xs mb-1">اسم العميل</div>
                        <div class="text-white font-bold">{{ $invoice->customer_name ?: 'عميل نقدي' }}</div>
                    </div>
                    <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-800">
                        <div class="text-gray-500 text-xs mb-1">الهاتف</div>
                        <div class="text-white font-mono">{{ $invoice->customer_phone ?: '—' }}</div>
                    </div>
                    <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-800">
                        <div class="text-gray-500 text-xs mb-1">نوع المركبة</div>
                        <div class="text-white font-bold">{{ $invoice->vehicle_type ?: '—' }}</div>
                    </div>
                    <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-800">
                        <div class="text-gray-500 text-xs mb-1">رقم اللوحة</div>
                        <div class="text-blue-400 font-black">{{ $invoice->plate_number ?: '—' }}</div>
                    </div>
                    <div class="bg-gray-800/40 rounded-xl p-4 border border-gray-800 md:col-span-2">
                        <div class="text-gray-500 text-xs mb-1">الرقم الضريبي للعميل</div>
                        <div class="text-white font-mono">{{ $invoice->tax_number ?: 'غير متوفر' }}</div>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div class="bg-gray-900/50 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-gray-800">
                    <h3 class="text-gray-300 text-sm font-black">المنتجات والخدمات</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-right">
                        <thead class="bg-gray-800/40 text-gray-400">
                            <tr>
                                <th class="p-4">الوصف</th>
                                <th class="p-4 text-center">الكمية</th>
                                <th class="p-4 text-center">السعر</th>
                                <th class="p-4 text-left">الإجمالي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800 text-white">
                            @forelse(($invoice->sale->items ?? []) as $item)
                                <tr>
                                    <td class="p-4">{{ optional($item->product)->name ?: '—' }}</td>
                                    <td class="p-4 text-center">{{ $item->quantity ?? 0 }}</td>
                                    <td class="p-4 text-center">{{ number_format((float)($item->price ?? 0), 2) }}</td>
                                    <td class="p-4 text-left font-bold text-blue-400">{{ number_format((float)($item->total ?? 0), 2) }}</td>
                                </tr>
                            @empty
                                @forelse($manualRows as $row)
                                    <tr>
                                        <td class="p-4">{{ $row['description'] }}</td>
                                        <td class="p-4 text-center">{{ number_format((float) $row['quantity'], 0) }}</td>
                                        <td class="p-4 text-center">{{ number_format((float) $row['unit_price'], 2) }}</td>
                                        <td class="p-4 text-left text-blue-400 font-bold">{{ number_format((float) $row['total'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="p-6 text-center text-gray-500" colspan="4">لا توجد عناصر</td>
                                    </tr>
                                @endforelse
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($invoice->notes)
                <div class="bg-amber-500/5 border border-amber-500/20 rounded-2xl p-5 text-amber-100 text-sm">
                    <div class="font-black mb-2">ملاحظات</div>
                    <div>{{ $invoice->notes }}</div>
                </div>
            @endif
        </div>

        {{-- summary --}}
        <div class="space-y-6">
            <div class="bg-blue-600 rounded-2xl p-6 text-white">
                <div class="text-xs text-white/80 mb-1">الإجمالي النهائي</div>
                <div class="text-4xl font-black">{{ number_format((float)$invoice->total_amount, 2) }} <span class="text-base font-normal">ر.س</span></div>
                <div class="mt-4 space-y-2 border-t border-white/20 pt-4 text-sm">
                    <div class="flex justify-between"><span class="text-white/80">قبل الضريبة</span><span>{{ number_format((float)$invoice->subtotal, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-white/80">الضريبة</span><span>{{ number_format((float)$invoice->tax_amount, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-white/80">النسبة</span><span>%{{ (float)$invoice->tax_rate }}</span></div>
                </div>
            </div>

            <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
                <div class="text-gray-500 text-xs mb-3 font-bold uppercase">الحالة</div>
                @php
                    $statusConfig = [
                        'paid' => ['label' => 'مدفوعة', 'class' => 'text-green-400 bg-green-500/10 border-green-500/20'],
                        'printed' => ['label' => 'مطبوعة', 'class' => 'text-blue-400 bg-blue-500/10 border-blue-500/20'],
                        'pending' => ['label' => 'معلقة', 'class' => 'text-yellow-400 bg-yellow-500/10 border-yellow-500/20'],
                        'canceled' => ['label' => 'ملغاة', 'class' => 'text-red-400 bg-red-500/10 border-red-500/20'],
                    ];
                    $status = $statusConfig[$invoice->status] ?? $statusConfig['pending'];
                @endphp
                <div class="p-3 rounded-xl border {{ $status['class'] }} font-bold text-sm">{{ $status['label'] }}</div>
            </div>

            <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">نوع الدفع</span>
                    <span class="text-white font-bold">{{ $paymentType }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">المحاسب</span>
                    <span class="text-white font-bold">{{ $accountantName }}</span>
                </div>
            </div>
        </div>

    </div>


</div>
@endsection
