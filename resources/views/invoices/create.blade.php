@extends('dashboard.app')

@section('title', 'إصدار فاتورة جديدة')

@section('content')
@php
    $storeTaxNumber = !empty($isOwnerContext)
        ? (optional($store)->tax_number ?? '')
        : (optional(optional(auth('accountant')->user())->store)->tax_number ?? '');
@endphp
<div class="max-w-4xl mx-auto px-4 py-8 text-right" dir="rtl">

    {{-- رأس الصفحة --}}
    <div class="flex flex-col gap-6 mb-8">
        {{-- السطر العلوي للأزرار في الجوال والعنوان في الكبيرة --}}
        <div class="flex items-center justify-between w-full">
            <div class="hidden md:block">
                <h1 class="text-2xl font-black text-white tracking-tight">إصدار فاتورة جديدة</h1>
                <p class="text-gray-400 text-sm mt-1">إدخال يدوي مباشر للبيانات والمبالغ</p>
            </div>

            <div class="flex-shrink-0 ml-auto md:ml-0">
                 <a href="{{ !empty($isOwnerContext) ? route('user.stores.invoices.index', $store->id) : route('accountant.invoices.index') }}"
                    class="inline-flex items-center gap-2 bg-gray-800/50 hover:bg-red-900/30 text-gray-400 hover:text-red-400 px-4 py-2.5 rounded-xl transition border border-gray-700 hover:border-red-500/50 active:scale-95 text-sm font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    الرجوع للفواتير
                </a>
            </div>
        </div>

        {{-- العنوان في الجوال --}}
        <div class="md:hidden">
            <h1 class="text-xl font-black text-white tracking-tight">إصدار فاتورة جديدة</h1>
            <p class="text-gray-400 text-xs mt-1">إدخال يدوي مباشر للبيانات والمبالغ</p>
        </div>
    </div>

    {{-- تنبيهات الأخطاء --}}
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ !empty($isOwnerContext) ? route('user.stores.invoices.store', $store->id) : route('accountant.invoices.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 gap-6">

            {{-- القسم الأيمن: بيانات العميل والمركبة --}}
            <div class="space-y-6">
                <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
                    <h3 class="text-blue-400 text-sm font-bold mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        معلومات العميل والمركبة
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-gray-400 text-sm">اسم العميل</label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-all"
                                placeholder="مثلاً: عميل نقدي">
                        </div>

                        <div class="space-y-2">
                            <label class="text-gray-400 text-sm">رقم الجوال</label>
                            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500 outline-none text-left"
                                placeholder="05xxxxxxxx">
                        </div>

                        <div class="space-y-2">
                            <label class="text-gray-400 text-sm">نوع المركبة</label>
                            <input type="text" name="vehicle_type" value="{{ old('vehicle_type') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500 outline-none"
                                placeholder="مثلاً: تويوتا كامري">
                        </div>

                        <div class="space-y-2">
                            <label class="text-gray-400 text-sm font-bold">رقم اللوحة</label>

                            {{-- تصميم أبسط وأوضح للوحة السعودية --}}
                            <div class="w-full max-w-[320px] rounded-xl border-2 border-gray-300 bg-white overflow-hidden mx-auto md:mx-0">
                                <div class="grid grid-cols-12 items-center">
                                    <div class="col-span-2 h-full bg-blue-900 text-white flex flex-col items-center justify-center py-2">
                                        <span class="text-[9px] font-bold leading-none">KSA</span>
                                        <span class="text-[9px] font-bold leading-none mt-1">السعودية</span>
                                    </div>
                                    <div class="col-span-10 px-3 py-2">
                                        <input type="text" name="plate_number" id="plate_number"
                                            class="w-full bg-transparent text-gray-900 text-[26px] md:text-[28px] font-black text-center tracking-[0.2em] outline-none placeholder-gray-400"
                                            placeholder="أ ب ج ١ ٢ ٣"
                                            dir="rtl"
                                            maxlength="12"
                                            autocomplete="off">
                                    </div>
                                </div>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1 italic">مثال: أ ب ج 1 2 3</p>
                        </div>
                        <div class="sm:col-span-2 space-y-2">
                            <label class="text-gray-400 text-sm">الرقم الضريبي للعميل (اختياري)</label>
                            <input type="text" name="tax_number" value="{{ old('tax_number') }}"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500 outline-none">
                        </div>
                    </div>
                </div>
<div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
    <div class="flex items-center justify-between mb-4">
        <label class="text-gray-400 text-sm font-bold uppercase">صفوف وصف العمل</label>
        <button type="button" id="add-service-line"
                class="bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 border border-indigo-500/30 px-3 py-1.5 rounded-lg text-xs">
            + إضافة صف
        </button>
    </div>
    <div id="service-lines-list" class="space-y-2">
        @php($serviceLines = old('service_lines', ['']))
        @php($serviceValues = old('service_values', ['']))
        @php($serviceQtys = old('service_qtys', ['1']))
        @foreach($serviceLines as $line)
            <div class="flex items-center gap-2 service-line-row">
                <div class="flex-1 min-w-[140px]">
                    <input type="text" name="service_lines[]" value="{{ $line }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm"
                           placeholder="مثال: تضليل أمامي / تغيير زيت / تنظيف داخلي">
                </div>
                <div class="w-20">
                    <input type="number" step="1" min="0" name="service_qtys[]" value="{{ $serviceQtys[$loop->index] ?? 1 }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-qty-input"
                           placeholder="الكمية">
                </div>
                <div class="w-24">
                    <input type="number" step="0.01" min="0" name="service_values[]" value="{{ $serviceValues[$loop->index] ?? '' }}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-value-input"
                           placeholder="السعر">
                </div>
                <div class="w-24">
                    <input type="number" step="0.01" min="0" name="service_totals[]" value="{{ (float) ($serviceQtys[$loop->index] ?? 1) * (float) ($serviceValues[$loop->index] ?? 0) }}"
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-green-300 text-sm text-center service-total-input"
                           placeholder="الإجمالي" readonly>
                </div>
                <div class="w-10">
                    <button type="button"
                            class="remove-service-line w-full h-full bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg text-xs">
                        ✕
                    </button>
                </div>
            </div>
        @endforeach
    </div>
    <p class="text-[11px] text-gray-500 mt-2">أدخل الكمية والسعر لكل صف، وسيتم احتساب إجمالي الصف والمبلغ قبل الضريبة تلقائيًا.</p>
</div>
                <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
                    <label class="text-gray-400 text-sm block mb-2 font-bold"> ملاحظات</label>
                    <textarea name="notes" rows="4"
                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white focus:border-blue-500 outline-none resize-none"
                        placeholder=" ضمان ...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- القسم الأيسر: الحسابات المالية --}}
            <div class="space-y-6">
                {{-- تنبيه إخلاء المسؤولية --}}
                <div id="tax_warning" class="hidden p-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-[11px] text-amber-200"><strong>تنبيه:</strong> تم اختيار ضريبة والمتجر لا يملك رقماً ضريبياً مسجلاً.</p>
                    </div>
                </div>

                <div class="bg-gray-900/50 border border-gray-800 rounded-2xl p-6">
                    <h3 class="text-gray-400 text-xs font-bold uppercase mb-6">الحساب المالي</h3>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-gray-500 text-xs font-bold">المبلغ (قبل الضريبة)</label>
                            <input type="number" step="0.01" name="subtotal" id="subtotal" value="{{ old('subtotal') }}" required
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white font-mono text-xl focus:border-blue-500 outline-none"
                                placeholder="0.00">
                        </div>

                        <div class="space-y-2">
                            <label class="text-gray-500 text-xs font-bold">نسبة الضريبة (%)</label>
                            <div class="relative">
                                <select name="tax_rate" id="tax_rate"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white font-mono focus:border-blue-500 outline-none appearance-none cursor-pointer">
                                    <option value="0" {{ old('tax_rate', 0) == 0 ? 'selected' : '' }}>0% (بدون)</option>
                                    <option value="15" {{ old('tax_rate') == 15 ? 'selected' : '' }}>15% (القياسية)</option>
                                </select>
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-800 space-y-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">مبلغ الضريبة:</span>
                                <span id="tax_amount_display" class="text-white font-mono font-bold">0.00 ر.س</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-500/5 rounded-xl border border-green-500/10">
                                <span class="text-green-500 font-bold">الإجمالي:</span>
                                <span id="total_amount_display" class="text-2xl font-black text-green-400 font-mono">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-blue-600/20 transition-all flex items-center justify-center gap-2 group">
                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    اعتماد وإصدار الفاتورة
                </button>
            </div>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const plateInput = document.getElementById('plate_number');

    plateInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\s+/g, ''); // إزالة المسافات الحالية للمعالجة

        // فصل الحروف العربية عن الأرقام
        let letters = value.replace(/[0-9]/g, '').split('').slice(0, 3);
        let numbers = value.replace(/[^\d]/g, '').split('').slice(0, 4);

        // تنسيق الحروف بمسافات (أ ب ج)
        let formattedLetters = letters.join(' ');

        // تنسيق الأرقام
        let formattedNumbers = numbers.join('');

        // الدمج النهائي: حروف + فراغ كبير + أرقام
        let finalValue = formattedLetters;
        if (numbers.length > 0) {
            finalValue += (letters.length > 0 ? '  ' : '') + formattedNumbers;
        }

        e.target.value = finalValue;
    });
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceLinesList = document.getElementById('service-lines-list');
        const addServiceLineBtn = document.getElementById('add-service-line');

        function createServiceLineRow(value = '') {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 service-line-row';
            row.innerHTML = `
                <div class="flex-1 min-w-[140px]">
                    <input type="text" name="service_lines[]" value="${value}"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm"
                           placeholder="مثال: تضليل أمامي / تغيير زيت / تنظيف داخلي">
                </div>
                <div class="w-20">
                    <input type="number" step="1" min="0" name="service_qtys[]" value="1"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-qty-input"
                           placeholder="الكمية">
                </div>
                <div class="w-24">
                    <input type="number" step="0.01" min="0" name="service_values[]"
                           class="w-full bg-gray-800 border border-gray-700 rounded-lg px-2 py-2 text-white text-sm text-center service-value-input"
                           placeholder="السعر">
                </div>
                <div class="w-24">
                    <input type="number" step="0.01" min="0" name="service_totals[]" value="0"
                           class="w-full bg-gray-900 border border-gray-700 rounded-lg px-2 py-2 text-green-300 text-sm text-center service-total-input"
                           placeholder="الإجمالي" readonly>
                </div>
                <div class="w-10">
                    <button type="button"
                            class="remove-service-line w-full h-full bg-red-600/20 hover:bg-red-600/30 text-red-300 border border-red-500/30 rounded-lg text-xs">
                        ✕
                    </button>
                </div>
            `;
            return row;
        }

        function recalcServiceTotals() {
            let subtotal = 0;
            const rows = serviceLinesList?.querySelectorAll('.service-line-row') || [];
            rows.forEach((row) => {
                const qtyInput = row.querySelector('input[name="service_qtys[]"]');
                const valueInput = row.querySelector('input[name="service_values[]"]');
                const totalInput = row.querySelector('input[name="service_totals[]"]');

                const qty = parseFloat(qtyInput?.value || '0') || 0;
                const value = parseFloat(valueInput?.value || '0') || 0;
                const rowTotal = qty * value;

                if (totalInput) {
                    totalInput.value = rowTotal.toFixed(2);
                }
                subtotal += rowTotal;
            });

            const subtotalInput = document.getElementById('subtotal');
            if (subtotalInput) {
                subtotalInput.value = subtotal.toFixed(2);
                subtotalInput.dispatchEvent(new Event('input'));
            }
        }

        addServiceLineBtn?.addEventListener('click', function() {
            serviceLinesList?.appendChild(createServiceLineRow());
            recalcServiceTotals();
        });

        serviceLinesList?.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-service-line');
            if (!removeBtn) return;
            const rows = serviceLinesList.querySelectorAll('.service-line-row');
            if (rows.length <= 1) {
                const input = rows[0]?.querySelector('input[name="service_lines[]"]');
                if (input) input.value = '';
                const valueInput = rows[0]?.querySelector('input[name="service_values[]"]');
                if (valueInput) valueInput.value = '';
                const totalInput = rows[0]?.querySelector('input[name="service_totals[]"]');
                if (totalInput) totalInput.value = '0.00';
                const qtyInput = rows[0]?.querySelector('input[name="service_qtys[]"]');
                if (qtyInput) qtyInput.value = '1';
                recalcServiceTotals();
                return;
            }
            removeBtn.closest('.service-line-row')?.remove();
            recalcServiceTotals();
        });

        serviceLinesList?.addEventListener('input', function(e) {
            if (e.target.closest('.service-line-row')) {
                recalcServiceTotals();
            }
        });

        recalcServiceTotals();
    });
</script>

<script>

    document.addEventListener('DOMContentLoaded', function() {
        const subtotalInput = document.getElementById('subtotal');
        const taxRateInput = document.getElementById('tax_rate');
        const taxDisplay = document.getElementById('tax_amount_display');
        const totalDisplay = document.getElementById('total_amount_display');
        const taxWarning = document.getElementById('tax_warning');

        // جلب الرقم الضريبي للمتجر بشكل آمن حسب السياق (مالك/محاسب)
        const storeTaxNumber = @json($storeTaxNumber ?? '');
        const hasTaxNumber = storeTaxNumber.trim() !== "";

        function calculate() {
            const subtotal = parseFloat(subtotalInput.value) || 0;
            const taxRate = parseFloat(taxRateInput.value) || 0;

            const taxAmount = subtotal * (taxRate / 100);
            const total = subtotal + taxAmount;

            taxDisplay.innerText = taxAmount.toFixed(2) + ' ر.س';
            totalDisplay.innerText = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // منطق التنبيه
            if(taxRate > 0 && !hasTaxNumber) {
                taxWarning.classList.remove('hidden');
            } else {
                taxWarning.classList.add('hidden');
            }
        }

        subtotalInput.addEventListener('input', calculate);
        taxRateInput.addEventListener('change', calculate);
        calculate(); // تشغيل أولي
    });
</script>

@endsection
