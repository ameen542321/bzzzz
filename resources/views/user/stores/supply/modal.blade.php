<form id="supplyForm"
      class="space-y-6 text-right animate-modalIn"
      data-product-kind="{{ $product->product_type === 'fractional' ? 'fractional' : ($product->is_splittable ? 'splittable' : 'normal') }}"
      data-roll-length="{{ (float) ($product->roll_length ?? 0) }}"
      data-current-roll-cost="{{ number_format((float) ($product->cost_price ?? 0), 2, '.', '') }}"
      data-current-meter-cost="{{ (float) ($product->roll_length ?? 0) > 0 ? number_format(((float) ($product->cost_price ?? 0) / (float) $product->roll_length), 2, '.', '') : '0.00' }}"
      data-items-per-unit="{{ (int) ($product->items_per_unit ?? 0) }}">
    @csrf

    <!-- Header with Product Info -->
    <div class="p-4 bg-gray-900 rounded-xl border border-gray-700/50 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h4 class="font-bold text-white mb-1">تفاصيل التوريد</h4>
                <p class="text-sm text-gray-400">إضافة كميات جديدة للمخزن</p>
            </div>
            <div class="p-2 bg-yellow-500/10 rounded-lg border border-yellow-500/20">
                <i class="fas fa-box-open text-yellow-500"></i>
            </div>
        </div>
    </div>

    <!-- Quantity Input -->
    <div class="relative group">
        <div class="absolute -inset-0.5 bg-blue-500/20 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
        <div class="relative bg-gray-900/80 border border-gray-700/50 rounded-xl p-4 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-gray-300 text-sm font-medium flex items-center">
                    <i class="fas fa-cubes ml-2 text-blue-400"></i>
                    <span id="quantityLabelText">
                        @if($product->product_type === 'fractional')
                            عدد الرولات الموردة
                        @elseif($product->is_splittable)
                            عدد الأطقم الموردة
                        @else
                            عدد الحبات الموردة
                        @endif
                    </span>
                </label>
                <span id="quantityUnitBadge" class="text-xs px-2 py-1 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30">
                    {{ $product->product_type === 'fractional' ? 'رول' : ($product->is_splittable ? 'طقم' : 'حبة') }}
                </span>
            </div>

            <div class="relative">
                <input type="number"
                       name="quantity"
                       id="quantityInput"
                       step="{{ $product->product_type === 'fractional' ? '0.01' : ($product->is_splittable ? '1' : '0.01') }}"
                       required
                       min="0"
                       placeholder="أدخل الكمية..."
                       class="w-full bg-gray-800/50 border-2 border-gray-700/50 text-white rounded-lg px-4 py-3 pr-12 outline-none
                              focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
                              hover:border-gray-600 transition-all duration-200
                              placeholder-gray-600"
                       oninput="updateTotalValue()">

                <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                    <i class="fas fa-hashtag text-gray-500"></i>
                </div>

                <!-- Quick Add Buttons -->
                <div class="flex space-x-2 space-x-reverse mt-3">
                    <button type="button"
                            onclick="quickAdd(1)"
                            class="flex-1 px-3 py-1.5 text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg border border-gray-700 transition-colors">
                        +1
                    </button>
                    <button type="button"
                            onclick="quickAdd(2)"
                            class="flex-1 px-3 py-1.5 text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg border border-gray-700 transition-colors">
                        +2
                    </button>
                    <button type="button"
                            onclick="quickAdd(5)"
                            class="flex-1 px-3 py-1.5 text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg border border-gray-700 transition-colors">
                        +5
                    </button>
                    <button type="button"
                            onclick="quickAdd(6)"
                            class="flex-1 px-3 py-1.5 text-xs bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg border border-gray-700 transition-colors">
                        +6
                    </button>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-700/50 text-xs text-gray-400 space-y-1">
                @if($product->product_type === 'fractional')
                    @php
                        $rolls = ($product->roll_length ?? 0) > 0 ? floor((float) $product->quantity / (float) $product->roll_length) : 0;
                        $remainingMeters = ($product->roll_length ?? 0) > 0 ? fmod((float) $product->quantity, (float) $product->roll_length) : (float) $product->quantity;
                    @endphp
                    <p>المتوفر الآن: <span class="text-white font-bold">{{ number_format($rolls, 0) }} رول</span> + <span class="text-white font-bold">{{ number_format($remainingMeters, 2) }} متر</span></p>
                @elseif($product->is_splittable)
                    @php
                        $kits = floor((float) $product->quantity);
                        $pieces = round((((float) $product->quantity) - $kits) * max((int) ($product->items_per_unit ?? 1), 1));
                    @endphp
                    <p>المتوفر الآن: <span class="text-white font-bold">{{ number_format($kits, 0) }} طقم</span> + <span class="text-white font-bold">{{ number_format($pieces, 0) }} حبة</span></p>
                @else
                    <p>المتوفر الآن: <span class="text-white font-bold">{{ number_format((float) $product->quantity, 2) }} حبة</span></p>
                @endif
            </div>
        </div>
    </div>

    @if($product->product_type === 'fractional' || $product->is_splittable)
    <div class="relative group">
        <div class="relative bg-gray-900/80 border border-gray-700/50 rounded-xl p-4 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-gray-300 text-sm font-medium flex items-center">
                    <i class="fas fa-ruler-combined ml-2 text-cyan-400"></i>
                    وحدة إدخال التوريد
                </label>
            </div>

            <select name="unit_type" id="unitTypeInput" onchange="updateTotalValue()"
                    class="w-full bg-gray-800/50 border-2 border-gray-700/50 text-white rounded-lg px-4 py-3 outline-none
                           focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 hover:border-gray-600 transition-all duration-200">
                @if($product->product_type === 'fractional')
                    <option value="unit">رول</option>
                    <option value="meter">متر (سيتم التحويل إلى رول تلقائياً)</option>
                @elseif($product->is_splittable)
                    <option value="unit">طقم</option>
                    <option value="piece">حبة (سيتم التحويل إلى طقم تلقائياً)</option>
                @endif
            </select>

            <p class="text-xs text-gray-500 mt-2">
                ملاحظة: عند اختيار متر/حبة سيتم التحويل تلقائياً للمخزون الأساسي.
            </p>
        </div>
    </div>
    @endif

    <!-- Purchase Price Input -->
    <div class="relative group">
        <div class="absolute -inset-0.5 bg-green-500/20 rounded-xl blur opacity-20 group-hover:opacity-30 transition duration-300"></div>
        <div class="relative bg-gray-900/80 border border-gray-700/50 rounded-xl p-4 backdrop-blur-sm">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-gray-300 text-sm font-medium flex items-center">
                    <i class="fas fa-money-bill-wave ml-2 text-green-400"></i>
                    <span id="priceLabelText">
                        @if($product->product_type === 'fractional')
                            سعر تكلفة الرول
                        @elseif($product->is_splittable)
                            سعر تكلفة الطقم
                        @else
                            سعر تكلفة الحبة
                        @endif
                    </span>
                </label>
                <span class="text-xs px-2 py-1 rounded-full bg-green-500/20 text-green-300 border border-green-500/30">
                    ر.س
                </span>
            </div>

            <div class="relative">
                <input type="number"
                       name="purchase_price"
                       id="priceInput"
                       data-current-price="{{ number_format($product->cost_price, 2, '.', '') }}"
                       step="0.01"
                       value=""
                       min="0"
                       placeholder="اتركه فارغاً إذا لا يوجد تغيير بالسعر"
                       class="w-full bg-gray-800/50 border-2 border-gray-700/50 text-white rounded-lg px-4 py-3 pr-12 outline-none
                              focus:border-green-500 focus:ring-2 focus:ring-green-500/20
                              hover:border-gray-600 transition-all duration-200
                              placeholder-gray-600"
                       oninput="updateTotalValue()">

                <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                    <i class="fas fa-coins text-gray-500"></i>
                </div>
            </div>
            <p id="priceFieldHint" class="mt-2 text-xs text-gray-500">
                إن لم تغيّر السعر اترك الحقل فارغاً.
            </p>

            <!-- Current Price Display -->
                <div class="mt-3 pt-3 border-t border-gray-700/50">
                    <div class="flex justify-between items-center text-sm">
                        <span id="currentPriceLabel" class="text-gray-400">
                            @if($product->product_type === 'fractional')
                                تكلفة الرول الحالية:
                            @elseif($product->is_splittable)
                                تكلفة الطقم الحالية:
                            @else
                                تكلفة الحبة الحالية:
                            @endif
                        </span>
                        <span id="currentPriceValue" class="text-gray-300 font-bold">{{ number_format($product->cost_price, 2) }} ر.س</span>
                    </div>
                    @if($product->product_type === 'fractional' && (float) $product->roll_length > 0)
                        <div class="flex justify-between items-center text-xs mt-2 text-gray-500">
                            <span>تكلفة المتر التقريبية:</span>
                            <span>{{ number_format(((float) $product->cost_price / (float) $product->roll_length), 2) }} ر.س</span>
                        </div>
                    @elseif($product->is_splittable && (int) $product->items_per_unit > 0)
                        <div class="flex justify-between items-center text-xs mt-2 text-gray-500">
                            <span>تكلفة الحبة التقريبية:</span>
                            <span>{{ number_format(((float) $product->cost_price / (int) $product->items_per_unit), 2) }} ر.س</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    <!-- Calculated Values -->
    <div id="calculatedValues" class="bg-gray-900/50 border border-gray-700/30 rounded-xl p-4 space-y-3 hidden">
        <div class="flex justify-between items-center">
            <span class="text-gray-400 flex items-center">
                <i class="fas fa-calculator ml-2 text-yellow-400"></i>
                القيمة الإجمالية:
            </span>
            <span id="totalValue" class="text-xl font-bold text-green-400">0.00 ر.س</span>
        </div>
        <div class="text-xs text-gray-500 text-center border-t border-gray-700/30 pt-3">
            <i class="fas fa-info-circle ml-1"></i>
            <span id="helperMeaning">يتم الحساب تلقائياً بناءً على الكمية وسعر التكلفة بمعناهما الصحيح.</span>
        </div>
    </div>

    <!-- Additional Notes -->
    <div class="relative group">
        <div class="relative bg-gray-900/80 border border-gray-700/50 rounded-xl p-4 backdrop-blur-sm">
            <label class="block text-gray-300 text-sm font-medium mb-3 flex items-center">
                <i class="fas fa-sticky-note ml-2 text-purple-400"></i>
                اسم الموزع / ملاحظات (اختياري)
            </label>
            <textarea name="notes"
                      rows="2"
                      placeholder="مثال: مؤسسة النور - دفعة صباحية"
                      class="w-full bg-gray-800/50 border-2 border-gray-700/50 text-white rounded-lg px-4 py-3 outline-none
                             focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20
                             hover:border-gray-600 transition-all duration-200
                             placeholder-gray-600 resize-none"></textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="grid grid-cols-2 gap-3 pt-4">
        <button type="button"
                onclick="closeModal()"
                class="py-3.5 bg-gray-800 hover:bg-gray-700
                       text-gray-300 font-bold rounded-xl transition-all duration-200
                       border border-gray-700 hover:border-gray-600
                       flex items-center justify-center space-x-2 space-x-reverse">
            <i class="fas fa-times"></i>
            <span>إلغاء</span>
        </button>

        <button type="submit"
                id="submitBtn"
                class="py-3.5 bg-blue-600 hover:bg-blue-500
                       text-white font-bold rounded-xl transition-all duration-200
                       shadow-lg hover:shadow-xl hover:shadow-blue-500/20
                       flex items-center justify-center space-x-2 space-x-reverse
                       group">
            <i class="fas fa-check group-hover:scale-110 transition-transform"></i>
            <span>تأكيد التوريد</span>
        </button>
    </div>

    <!-- Helper Info -->
    <div class="text-center pt-4 border-t border-gray-700/30">
        <p class="text-xs text-gray-500">
            <i class="fas fa-shield-alt ml-1"></i>
            جميع البيانات محفوظة بشكل آمن
        </p>
    </div>
</form>
<style>
/* إصلاحات للموبايل */
@media (max-width: 640px) {
    #supplyForm {
        padding: 1rem 0.75rem;
        margin: 0;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
    }

    /* تحسين الحقول */
    .space-y-6 > div {
        margin-bottom: 1rem !important;
    }

    /* تحسين الـ inputs */
    input[type="number"],
    textarea {
        font-size: 16px !important; /* منع التكبير في iOS */
        padding: 0.75rem !important;
    }

    /* تحسين الأزرار */
    .grid-cols-2 {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }

    /* تحسين الـ quick buttons */
    .flex.space-x-2 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .flex.space-x-2 button {
        flex: 0 0 calc(50% - 0.25rem);
        font-size: 14px;
        padding: 0.5rem;
    }

    /* تحسين الهيدر */
    .p-4.bg-gradient-to-r {
        padding: 1rem !important;
        margin-bottom: 1rem !important;
    }
}

/* إصلاح خاص للـ touch devices */
@media (hover: none) and (pointer: coarse) {
    /* زيادة مساحة الـ tap */
    button,
    input[type="number"],
    textarea {
        min-height: 44px !important; /* الحد الأدنى لللمس في iOS */
    }

    /* تحسين الـ quick buttons للمس */
    .flex.space-x-2 button {
        min-height: 40px;
        min-width: 60px;
    }

    /* إزالة تأثيرات hover */
    button:hover,
    input:hover {
        transform: none !important;
    }
}

/* منع التكبير التلقائي في iOS */
input[type="number"],
input[type="text"] {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-size: 16px;
}
</style>
