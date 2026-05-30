@props(['employee', 'modalId' => 'creditSaleCollectionModal'])

{{-- 1. أضف x-data لإدارة حالة القوائم المفتوحة --}}
<div id="{{ $modalId }}"
     x-data="{ activeSaleId: null }"
     class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">

    <div class="w-full max-w-3xl px-4">
        <div class="bg-gray-900/95 border border-gray-800 shadow-2xl rounded-2xl p-8 md:p-9">

            {{-- العنوان + زر الإغلاق --}}
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-100">
                    تحصيل البيع الآجل — {{ $employee->name }}
                </h2>
                <button type="button"
                        onclick="document.getElementById('{{ $modalId }}').classList.add('hidden')"
                        class="w-9 h-9 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-sm transition flex items-center justify-center"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-6">
                @php
                    $pendingSales = $employee->creditSales()->where('status', 'pending')->get();
                @endphp

                @if($pendingSales->isEmpty())
                    <div class="text-center py-10 text-gray-400 bg-gray-800 border border-gray-700 rounded-xl">
                        لا توجد عمليات بيع آجل غير محصّلة.
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($pendingSales as $sale)
                            <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 space-y-4">
                                <div class="flex justify-between items-center">
                                    <div class="space-y-1">
                                        <p class="text-gray-100 font-semibold text-lg">
                                            المتبقي: {{ number_format($sale->remaining_amount, 2) }} ريال
                                        </p>
                                        <p class="text-sm text-gray-400">التاريخ: {{ $sale->date }}</p>
                                    </div>

                                    {{-- 2. زر فتح الخيارات باستخدام Alpine --}}
                                    <button @click="activeSaleId = (activeSaleId === {{ $sale->id }} ? null : {{ $sale->id }})"
                                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-xl text-sm transition">
                                        خيارات التحصيل
                                    </button>
                                </div>

                                {{-- 3. التحكم في الظهور باستخدام x-show --}}
                                <div x-show="activeSaleId === {{ $sale->id }}"
                                     x-transition
                                     class="space-y-3" x-cloak>

                                    {{-- تحصيل كامل --}}
                                    <a href="{{ route('user.employees.credit-sale.collect.full', [$employee->id, $sale->id]) }}"
                                       class="block bg-green-600 hover:bg-green-700 text-white text-center py-2 rounded-xl transition">
                                        تحصيل كامل
                                    </a>

                                    {{-- تحصيل جزئي --}}
<div class="bg-gray-900/90 border border-gray-700/80 rounded-xl p-4 space-y-3">
    <div class="relative">
        {{-- ربط قيمة المدخل بمتغير في Alpine باستخدام x-model --}}
        <input type="number"
               x-model="partialAmount"
               placeholder="مبلغ التحصيل الجزئي"
               class="w-full bg-gray-800/80 border border-gray-700/80 text-gray-200 rounded-xl px-10 py-2 text-sm">
        <i class="fa-solid fa-money-bill text-gray-500 absolute left-3 top-1/2 -translate-y-1/2"></i>
    </div>

    {{-- زر التأكيد يقوم بالتوجيه مباشرة --}}
    {{-- زر التأكيد باستخدام رابط لارافيل الرسمي --}}
<button @click="
        if(!partialAmount || partialAmount <= 0) {
            alert('الرجاء إدخال مبلغ صحيح');
            return;
        }

        // توليد الرابط باستخدام لارافيل مع وضع علامة مكان المبلغ
        let baseUrl = '{{ route('user.employees.credit-sale.collect.partial', [$employee->id, ':saleId', ':amount']) }}';

        // استبدال العلامات بالقيم الحقيقية من Alpine.js
        let finalUrl = baseUrl.replace(':saleId', activeSaleId)
                             .replace(':amount', partialAmount);

        window.location.href = finalUrl;
    "
    class="w-full bg-yellow-600 hover:bg-yellow-700 text-white py-2 rounded-xl transition text-sm">
    تأكيد التحصيل الجزئي
</button>
</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- 4. استخدام @once لضمان تحميل السكريبت مرة واحدة فقط مهما تكرر المكون --}}
@once
    @push('scripts')
    <script>
        window.collectCreditPartial = function(employeeId, saleId) {
            const amountInput = document.getElementById('creditPartialAmount-' + saleId);
            const amount = amountInput ? amountInput.value : null;

            if (!amount || amount <= 0) {
                alert("الرجاء إدخال مبلغ صحيح");
                return;
            }

            // التوجيه للرابط
            window.location.href = `/user/employees/${employeeId}/credit-sale/${saleId}/collect-partial/${amount}`;
        }
    </script>
    @endpush
@endonce
