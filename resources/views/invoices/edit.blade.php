@extends('dashboard.app')

@section('title', 'تعديل فاتورة #' . $invoice->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 space-y-6 text-right" dir="rtl">
    @php
        $isOwnerContext = isset($store);

        $backUrl = $isOwnerContext
            ? route('user.stores.invoices.show', [$store->id, $invoice->id])
            : route('accountant.invoices.show', $invoice->id);

        $updateUrl = $isOwnerContext
            ? route('user.stores.invoices.update', [$store->id, $invoice->id])
            : route('accountant.invoices.update', $invoice->id);

        $descriptionText = (string) ($invoice->description ?? '');
        $serviceLinesFromDescription = collect(preg_split('/\r\n|\r|\n/', $descriptionText))
            ->map(fn($line) => trim((string) $line))
            ->filter(fn($line) => str_starts_with($line, '-'))
            ->map(function ($line) {
                $line = trim((string) preg_replace('/^-\s*/u', '', $line));
                preg_match('/^(.*?)\s*\(الكمية:\s*([0-9.,]+)\s*×\s*السعر:\s*([0-9.,]+)/u', $line, $matches);

                return [
                    'name' => trim((string) ($matches[1] ?? $line)),
                    'qty' => (float) str_replace(',', '', (string) ($matches[2] ?? 1)),
                    'price' => isset($matches[3]) ? (float) str_replace(',', '', (string) $matches[3]) : '',
                ];
            })
            ->values();

        $serviceLines = old('service_lines', $serviceLinesFromDescription->pluck('name')->all() ?: ['']);
        $serviceQtys = old('service_qtys', $serviceLinesFromDescription->pluck('qty')->all() ?: [1]);
        $serviceValues = old('service_values', $serviceLinesFromDescription->pluck('price')->all() ?: ['']);
    @endphp

    <div class="bg-gray-900/60 border border-gray-800 rounded-2xl p-5 flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl md:text-2xl font-black text-white">تعديل الفاتورة <span class="font-mono text-amber-400">#{{ $invoice->invoice_number }}</span></h1>
            <p class="text-xs text-gray-500 mt-1">يمكنك تحديث بيانات العميل والحالة من هذه الصفحة.</p>
        </div>

        <a href="{{ $backUrl }}" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-xl text-sm font-bold">
            رجوع للتفاصيل
        </a>
    </div>

    <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
        <form method="POST" action="{{ $updateUrl }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div>
                <label class="text-gray-500 text-xs block mb-1">اسم العميل</label>
                <input type="text" name="customer_name" value="{{ old('customer_name', $invoice->customer_name) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">الهاتف</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone', $invoice->customer_phone) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">نوع المركبة</label>
                <input type="text" name="vehicle_type" value="{{ old('vehicle_type', $invoice->vehicle_type) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">رقم اللوحة</label>
                <input type="text" name="plate_number" value="{{ old('plate_number', $invoice->plate_number) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">الرقم الضريبي</label>
                <input type="text" name="tax_number" value="{{ old('tax_number', $invoice->tax_number) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">الحالة</label>
                <select name="status" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
                    @foreach(['paid' => 'مدفوعة', 'pending' => 'معلقة', 'printed' => 'مطبوعة', 'canceled' => 'ملغاة'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $invoice->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-gray-500 text-xs block mb-1">ملاحظات</label>
                <textarea name="notes" rows="4" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">{{ old('notes', $invoice->notes) }}</textarea>
            </div>

            <div class="md:col-span-2 bg-gray-900/40 border border-gray-700 rounded-2xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-gray-300 text-sm font-black">المنتجات</label>
                    <button type="button" id="add-service-line"
                            class="bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 px-3 py-1.5 rounded-lg text-xs">
                        + إضافة صف
                    </button>
                </div>

                <div class="hidden md:grid md:grid-cols-12 gap-2 text-[11px] text-gray-400 px-1">
                    <div class="md:col-span-5">المنتج</div>
                    <div class="md:col-span-2 text-center">الكمية</div>
                    <div class="md:col-span-2 text-center">السعر</div>
                    <div class="md:col-span-2 text-center">الإجمالي</div>
                    <div class="md:col-span-1 text-center">حذف</div>
                </div>

                <div id="service-lines-list" class="space-y-2">
                    @foreach($serviceLines as $index => $line)
                        <div class="grid grid-cols-12 gap-2 items-center service-line-row">
                            <div class="col-span-12 md:col-span-5">
                                <input type="text" name="service_lines[]" value="{{ $line }}"
                                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm"
                                       placeholder="اسم المنتج / الخدمة">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <input type="number" step="1" min="0" name="service_qtys[]" value="{{ $serviceQtys[$index] ?? 1 }}"
                                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-qty-input"
                                       placeholder="الكمية">
                            </div>
                            <div class="col-span-4 md:col-span-2">
                                <input type="number" step="0.01" min="0" name="service_values[]" value="{{ $serviceValues[$index] ?? '' }}"
                                       class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-value-input"
                                       placeholder="السعر">
                            </div>
                            <div class="col-span-3 md:col-span-2">
                                <input type="number" step="0.01" min="0" name="service_totals[]" value="{{ (float) ($serviceQtys[$index] ?? 1) * (float) ($serviceValues[$index] ?? 0) }}"
                                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-green-300 text-sm text-center service-total-input"
                                       placeholder="الإجمالي" readonly>
                            </div>
                            <div class="col-span-1 md:col-span-1">
                                <button type="button"
                                        class="remove-service-line w-full h-[38px] bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg text-xs">✕</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-gray-500">يمكنك إضافة/تعديل صفوف المنتجات أو الخدمات مع الكمية والسعر والإجمالي.</p>
            </div>

            <div class="md:col-span-2">
                <label class="text-gray-500 text-xs block mb-1">وصف إضافي (اختياري)</label>
                <textarea name="description" rows="3" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">المجموع قبل الضريبة</label>
                <input type="number" step="0.01" min="0" name="subtotal" value="{{ old('subtotal', $invoice->subtotal) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div>
                <label class="text-gray-500 text-xs block mb-1">نسبة الضريبة (%)</label>
                <input type="number" step="0.01" min="0" name="tax_rate" value="{{ old('tax_rate', $invoice->tax_rate ?? optional($invoice->sale)->tax_rate) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
            </div>

            <div class="md:col-span-2">
                <div class="text-gray-300 text-sm font-black mb-3">المنتجات</div>

                @if(($invoice->sale->items ?? collect())->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($invoice->sale->items as $index => $item)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-gray-800/40 border border-gray-700 rounded-xl p-3">
                                <input type="hidden" name="item_ids[]" value="{{ $item->id }}">

                                <div class="md:col-span-6">
                                    <label class="text-gray-500 text-xs block mb-1">اسم المنتج</label>
                                    <input type="text" value="{{ optional($item->product)->name ?: ($item->custom_name ?: 'منتج') }}" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-gray-200" readonly>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="text-gray-500 text-xs block mb-1">الكمية</label>
                                    <input type="number" step="0.01" min="0" name="item_quantities[]" value="{{ old('item_quantities.' . $index, $item->quantity) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
                                </div>

                                <div class="md:col-span-3">
                                    <label class="text-gray-500 text-xs block mb-1">السعر</label>
                                    <input type="number" step="0.01" min="0" name="item_prices[]" value="{{ old('item_prices.' . $index, $item->price) }}" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2 text-white">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-gray-800/40 border border-gray-700 rounded-xl p-4 text-sm text-gray-400">
                        لا توجد منتجات مرتبطة بهذه الفاتورة. يمكنك تعديل وصف العمل والمبالغ من الحقول أعلاه.
                    </div>
                @endif
            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <a href="{{ $backUrl }}" class="inline-flex items-center bg-gray-700 hover:bg-gray-600 text-white px-5 py-2.5 rounded-xl font-bold">إلغاء</a>
                <button type="submit" class="inline-flex items-center bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl font-bold">حفظ التعديلات</button>
            </div>
        </form>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const serviceLinesList = document.getElementById('service-lines-list');
        const addServiceLineBtn = document.getElementById('add-service-line');
        const subtotalInput = document.querySelector('input[name="subtotal"]');
        const taxRateInput = document.querySelector('input[name="tax_rate"]');
        if (!serviceLinesList || !addServiceLineBtn) {
            return;
        }
        const hasProductItems = document.querySelectorAll('input[name="item_ids[]"]').length > 0;

        function recalculateLineTotal(row) {
            const qtyInput = row.querySelector('.service-qty-input');
            const valueInput = row.querySelector('.service-value-input');
            const totalInput = row.querySelector('.service-total-input');

            const qty = parseFloat(qtyInput?.value || 0);
            const value = parseFloat(valueInput?.value || 0);
            totalInput.value = ((isNaN(qty) ? 0 : qty) * (isNaN(value) ? 0 : value)).toFixed(2);
            recalculateInvoiceTotalsFromServices();
        }

        function recalculateInvoiceTotalsFromServices() {
            if (hasProductItems || !subtotalInput) {
                return;
            }

            let subtotal = 0;
            serviceLinesList.querySelectorAll('.service-total-input').forEach((input) => {
                const value = parseFloat(input.value || 0);
                subtotal += isNaN(value) ? 0 : value;
            });

            subtotalInput.value = subtotal.toFixed(2);

            if (taxRateInput) {
                const currentRate = parseFloat(taxRateInput.value || 0);
                taxRateInput.value = isNaN(currentRate) ? '0' : currentRate;
            }
        }

        function attachRowListeners(row) {
            row.querySelector('.service-qty-input')?.addEventListener('input', () => recalculateLineTotal(row));
            row.querySelector('.service-value-input')?.addEventListener('input', () => recalculateLineTotal(row));
            row.querySelector('.remove-service-line')?.addEventListener('click', () => {
                const rows = serviceLinesList.querySelectorAll('.service-line-row');
                if (rows.length <= 1) {
                    row.querySelector('input[name="service_lines[]"]').value = '';
                    row.querySelector('.service-qty-input').value = 1;
                    row.querySelector('.service-value-input').value = '';
                    recalculateLineTotal(row);
                    return;
                }
                row.remove();
                recalculateInvoiceTotalsFromServices();
            });
            recalculateLineTotal(row);
        }

        function createServiceLineRow() {
            const row = document.createElement('div');
            row.className = 'grid grid-cols-12 gap-2 items-center service-line-row';
            row.innerHTML = `
                <div class="col-span-12 md:col-span-5">
                    <input type="text" name="service_lines[]"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm"
                           placeholder="اسم المنتج / الخدمة">
                </div>
                <div class="col-span-4 md:col-span-2">
                    <input type="number" step="1" min="0" name="service_qtys[]" value="1"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-qty-input">
                </div>
                <div class="col-span-4 md:col-span-2">
                    <input type="number" step="0.01" min="0" name="service_values[]"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-value-input">
                </div>
                <div class="col-span-3 md:col-span-2">
                    <input type="number" step="0.01" min="0" name="service_totals[]" value="0"
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-green-300 text-sm text-center service-total-input" readonly>
                </div>
                <div class="col-span-1 md:col-span-1">
                    <button type="button" class="remove-service-line w-full h-[38px] bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg text-xs">✕</button>
                </div>
            `;
            attachRowListeners(row);
            return row;
        }

        serviceLinesList.querySelectorAll('.service-line-row').forEach(attachRowListeners);
        addServiceLineBtn.addEventListener('click', () => serviceLinesList.appendChild(createServiceLineRow()));
    });
</script>
