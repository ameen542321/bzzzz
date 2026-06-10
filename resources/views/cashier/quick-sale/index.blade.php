@extends('dashboard.app')

@section('title', 'البيع السريع المطوّر')

@section('content')
<div x-data="quickSale()" x-init="init()" class="max-w-full lg:max-w-5xl mx-auto py-4 md:py-10 px-2 md:px-6 text-right" dir="rtl">

    {{-- 🔥 الشريط العلوي --}}
    <div class="flex flex-col md:flex-row items-center justify-between bg-gray-900 border border-gray-800 px-4 py-4 rounded-2xl mb-6 gap-4 shadow-xl">
        <div class="flex items-center gap-4 w-full md:w-auto justify-between md:justify-start">
            <a href="{{ route('accountant.dashboard') }}" class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition">← رجوع</a>
            <h1 class="text-lg md:text-xl font-bold text-white">تسجيل بيع جديد</h1>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
            <a href="{{ route('accountant.quick-sale.tint-preview') }}" target="_blank" rel="noopener"
               class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg border border-indigo-400/40 text-sm font-black text-center transition whitespace-nowrap">
                معاينة التضليل
            </a>
            <div class="text-gray-400 text-sm bg-gray-800/50 px-4 py-2 rounded-lg border border-gray-700 text-center font-sans">
                المحاسب: <span class="font-bold text-blue-400">{{ auth('accountant')->user()->name }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- العمود الأيمن: البحث والسلة --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- البحث المرن مع التولتيب --}}
            <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-lg relative group">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-gray-400 text-xs mb-2 block font-bold italic">ابحث عن منتج</label>
                    <div class="relative">
                        <span class="text-gray-500 text-xs cursor-help border border-gray-700 rounded-full w-4 h-4 flex items-center justify-center">?</span>
                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[10px] p-2 rounded-lg w-40 shadow-xl z-50">
                            يمكنك البحث بالاسم أو الضغط على مسافة لعرض جميع المنتجات
                        </div>
                    </div>
                </div>
                <p class="text-[10px] text-gray-600 mb-1 pr-1 leading-tight">تستطيع البحث بواسطة حرف واحد أو اضغط مسافة تظهر جميع المنتجات</p>
                <input type="text" x-model="search" x-ref="searchInput" @input.debounce.200ms="searchProducts"
                       placeholder="اكتب اسم المنتج..."
                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-4 focus:ring-2 focus:ring-blue-500 outline-none transition-all text-right font-bold">

                {{-- أعلى 4 نتائج سريعة للإضافة المباشرة --}}
                <div x-show="results.length > 0" class="mt-3">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] text-gray-400 font-bold" x-text="showingFeatured ? 'الأكثر بيعًا' : 'أعلى 4 نتائج سريعة'"></span>
                        <span class="text-[10px] text-gray-500" x-text="showingFeatured ? '4 منتجات فقط' : ('النتائج: ' + results.length)"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        <template x-for="product in results.slice(0, 4)" :key="'top-' + product.id + '-' + product.price + '-' + product.piece_price + '-' + (product.price_updated_at || '')">
                            <button type="button"
                                    @click="addToCart(product)"
                                    :disabled="product.is_out_of_stock"
                                    :class="product.is_out_of_stock ? 'bg-red-900/20 border-red-800/70 hover:bg-red-900/30' : (product.is_low_stock ? 'bg-yellow-900/10 border-yellow-700/60 hover:bg-yellow-900/20' : 'bg-gray-800/80 border-gray-700 hover:border-blue-500/50 hover:bg-gray-800')"
                                    class="w-full p-2.5 rounded-lg border text-right transition shadow-sm"
                                    :title="product.is_out_of_stock ? 'غير قابل للإضافة - المنتج منتهي من المخزون' : 'إضافة مباشرة إلى السلة'">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="text-white font-bold text-[13px] truncate" x-text="product.name"></span>
                                            <span x-show="!product.is_out_of_stock && !product.is_low_stock" class="bg-emerald-500/15 text-emerald-400 text-[9px] px-1.5 py-0.5 rounded-md font-black border border-emerald-500/30">متوفر</span>
                                            <span x-show="product.is_low_stock && !product.is_out_of_stock" class="bg-yellow-500/15 text-yellow-400 text-[9px] px-1.5 py-0.5 rounded-md font-black border border-yellow-500/30">منخفض</span>
                                            <span x-show="product.is_out_of_stock" class="bg-red-600 text-white text-[9px] px-1.5 py-0.5 rounded-md font-black">منتهي</span>
                                        </div>

                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5 text-[10px]">
                                            <span class="text-gray-500">المخزون:</span>
                                            <span :class="product.is_out_of_stock ? 'text-red-400' : (product.is_low_stock ? 'text-yellow-400' : 'text-emerald-400')"
                                                  class="font-bold font-sans"
                                                  x-text="product.display_quantity"></span>
                                            <span class="text-gray-300" x-text="product.display_unit"></span>
                                        </div>
                                    </div>

                                    <div class="text-left shrink-0">
                                        <span class="text-blue-400 font-black block font-sans text-[13px]" x-text="displayPriceLabel(product)"></span>
                                        <span class="text-[9px] block mt-0.5"
                                              :class="product.is_out_of_stock ? 'text-red-400' : 'text-gray-500'"
                                              x-text="product.is_out_of_stock ? 'غير متاح' : 'إضافة مباشرة'"></span>
                                    </div>
                                </div>
                            </button>
                        </template>
                    </div>
                </div>

                <div x-show="results.length === 0" class="mt-4 bg-gray-800/40 border border-dashed border-gray-700 rounded-xl px-4 py-8 text-center">
                    <div class="text-3xl mb-2">🔎</div>
                    <p class="text-white font-bold text-sm">لا توجد نتائج مطابقة حالياً.</p>
                    <p class="text-gray-400 text-xs mt-1">جرّب اسمًا آخر أو اترك البحث فارغًا لعرض المنتجات الأكثر بيعًا.</p>
                </div>

                {{-- بقية النتائج المطورة --}}
                <div x-show="!showingFeatured && results.length > 4" class="absolute z-50 left-0 right-0 mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl max-h-72 overflow-y-auto">
                    <template x-for="product in results.slice(4)" :key="product.id + '-' + product.price + '-' + product.piece_price + '-' + (product.price_updated_at || '')">
                        <div @click="addToCart(product)"
                             class="p-3 sm:p-4 border-b border-gray-700 transition group text-right"
                             :title="product.is_out_of_stock ? 'غير قابل للإضافة - المنتج منتهي من المخزون' : 'إضافة إلى السلة'"
                             :class="product.is_out_of_stock ? 'cursor-not-allowed opacity-70 bg-red-900/20 hover:bg-red-900/30' : ((product.is_low_stock ? 'bg-yellow-900/10 hover:bg-yellow-900/20' : 'hover:bg-gray-700') + ' cursor-pointer')">

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-white font-bold block group-hover:text-blue-400 text-sm sm:text-base truncate" x-text="product.name"></span>
                                        <span x-show="!product.is_out_of_stock && !product.is_low_stock" class="bg-emerald-500/15 text-emerald-400 text-[10px] px-2 py-0.5 rounded-md font-black border border-emerald-500/30">متوفر</span>
                                        <span x-show="product.is_low_stock && !product.is_out_of_stock" class="bg-yellow-500/15 text-yellow-400 text-[10px] px-2 py-0.5 rounded-md font-black border border-yellow-500/30">كمية منخفضة</span>
                                        <span x-show="product.is_out_of_stock" class="bg-red-600 text-white text-[10px] px-2 py-0.5 rounded-md font-black animate-pulse">منتهي</span>
                                        <template x-if="product.is_splittable">
                                            <span class="bg-blue-500/10 text-blue-300 text-[10px] px-2 py-0.5 rounded-md font-bold border border-blue-500/20">يدعم الحبة</span>
                                        </template>
                                        <template x-if="product.product_type === 'fractional'">
                                            <span class="bg-yellow-500/10 text-yellow-300 text-[10px] px-2 py-0.5 rounded-md font-bold border border-yellow-500/20">رول / متر</span>
                                        </template>
                                    </div>

                                    <p class="text-gray-400 text-[11px] mt-1 leading-5 break-words" x-text="product.description || 'لا يوجد وصف'"></p>

                                    <div class="mt-2 text-[11px]">
                                        <div class="bg-gray-900/60 rounded-lg px-3 py-2 border border-gray-700/70 inline-flex flex-wrap items-center gap-1">
                                            <span class="text-gray-500">المخزون الحالي:</span>
                                            <span :class="product.is_out_of_stock ? 'text-red-400' : (product.is_low_stock ? 'text-yellow-400' : 'text-emerald-400')"
                                                  class="font-bold font-sans"
                                                  x-text="product.display_quantity"></span>
                                            <span class="text-gray-300" x-text="product.display_unit"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:text-left sm:min-w-[120px]">
                                    <span class="text-blue-400 font-black block font-sans text-base sm:text-lg" x-text="displayPriceLabel(product)"></span>
                                    <template x-if="product.product_type === 'fractional' && product.meter_price">
                                        <span class="text-[10px] text-cyan-400 block mt-1" x-text="'سعر المتر: ' + product.meter_price + ' ر.س'"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- السلة مع التولتيب --}}
            <div class="bg-gray-900 border border-gray-800 p-4 rounded-2xl shadow-lg">
                <div class="flex items-center justify-between mb-4 border-b border-gray-800 pb-2">
                    <h2 class="text-white font-bold flex items-center gap-2 text-right">🛒 قائمة البيع</h2>
                    <div class="relative group">
                        <span class="text-gray-500 text-xs cursor-help border border-gray-700 rounded-full w-4 h-4 flex items-center justify-center">?</span>
                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[10px] p-2 rounded-lg w-48 shadow-xl z-50">
                            اضغط على + أو - لتغيير الكمية. للمنتجات القابلة للتجزئة، اختر نوع البيع أولاً
                        </div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div x-show="cart.length === 0" class="bg-gray-800/30 border border-dashed border-gray-700 rounded-xl px-4 py-8 text-center">
                        <div class="text-3xl mb-2">🧺</div>
                        <p class="text-white font-bold text-sm">السلة فارغة حالياً</p>
                        <p class="text-gray-400 text-xs mt-1">ابحث عن منتج أو اختر من المنتجات الأكثر بيعًا لإضافته بسرعة.</p>
                    </div>
                    <template x-for="item in cart" :key="item.temp_id">
                        <div class="flex flex-col bg-gray-800/40 p-3 rounded-xl border border-gray-800 gap-2 text-right">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-white font-bold text-sm" x-text="item.name"></p>
                                        <template x-if="item.is_splittable && !item.is_fractional">
                                            <span class="text-[10px] bg-blue-900/50 text-blue-300 px-2 py-0.5 rounded border border-blue-700">نظام أطقم</span>
                                        </template>
                                        <template x-if="item.is_fractional">
                                            <div class="relative group inline-block">
                                                <span class="text-[10px] bg-yellow-900/50 text-yellow-300 px-2 py-0.5 rounded border border-yellow-700 cursor-help">رول</span>
                                                <div class="absolute bottom-full right-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[8px] p-1 rounded-lg w-32 shadow-xl z-50">
                                                    منتج رول - قابل للقص
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- خيار البيع (طقم أو حبة) --}}
                                    <template x-if="item.is_splittable && !item.is_fractional">
                                        <div class="mt-2 w-48">
                                            <select x-model="item.sale_unit" @change="updateSplittablePrice(item)" @wheel="preventWheelChange($event)"
                                                    class="w-full bg-gray-900 border border-blue-700 text-blue-300 text-[11px] rounded-lg px-2 py-1.5 outline-none">
                                                <option value="unit">البيع بـ (طقم كامل)</option>
                                                <option value="piece">البيع بـ (حبة منفردة)</option>
                                            </select>
                                        </div>
                                    </template>

                                    {{-- خيارات التجزئة (للمنتجات الرول) --}}
                                    <template x-if="item.is_fractional">
                                        <div class="space-y-2 mt-2">
                                            <select x-model="item.fraction_id" @change="updateFractionPrice(item)" @wheel="preventWheelChange($event)"
                                                    class="w-full bg-gray-900 border border-gray-700 text-gray-300 text-[11px] rounded-lg px-2 py-2 outline-none">
                                                <option value="0">— اختر نوع التجزئة —</option>
                                                <template x-for="fr in item.available_fractions" :key="fr.id">
                                                    <option :value="fr.id" x-text="fr.option_label + ' (' + Math.round(fr.price) + ' ر.س)'"></option>
                                                </template>
                                                <option value="custom">✨ مخصص (إدخال يدوي)</option>
                                            </select>

                                            <div x-show="item.fraction_id === 'custom'" class="grid grid-cols-3 gap-2 p-2 bg-gray-900/50 rounded-lg border border-blue-500/30">
                                                <div>
                                                    <label class="text-[9px] text-blue-400 block mb-1">حدد العمل</label>
                                                    <input type="text" x-model="item.custom_name" class="w-full bg-gray-800 border border-gray-700 text-white text-[10px] px-2 py-1 rounded">
                                                </div>
                                                <div>
                                                    <label class="text-[9px] text-blue-400 block mb-1">السعر</label>
                                                    <input type="number" x-model.number="item.price" @input="item.total = item.quantity * item.price" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-gray-700 text-white text-[10px] px-2 py-1 rounded">
                                                </div>
                                                <div>
                                                    <label class="text-[9px] text-yellow-500 block mb-1">الأمتار</label>
                                                    <input type="number" step="0.01" x-model.number="item.custom_consumption" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-gray-700 text-white text-[10px] px-2 py-1 rounded">
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                                <button @click="removeItem(item)" class="text-red-500 hover:bg-red-500/10 p-2 rounded-lg transition">🗑️</button>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-700/50 pt-2">
                                <div x-show="!item.is_fractional" class="flex items-center bg-gray-900 rounded-lg p-1 border border-gray-700">
                                    <button @click="decrease(item)" class="w-8 h-8 text-white hover:bg-gray-700 rounded-md font-bold">-</button>
                                    <span class="w-10 text-center text-white font-black text-lg" x-text="item.quantity"></span>
                                    <button @click="increase(item)" class="w-8 h-8 text-white hover:bg-gray-700 rounded-md font-bold">+</button>
                                </div>
                                <div x-show="item.is_fractional" class="text-[11px] text-yellow-300 bg-yellow-500/10 border border-yellow-500/20 rounded-lg px-3 py-2">
                                    خيار الرول يباع كسطر مستقل
                                </div>
                                <div class="text-green-400 font-black text-xl" x-text="Math.round(item.total) + ' ر.س'"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- العمود الأيسر: الإجماليات والدفع --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-900 border border-gray-800 p-5 rounded-2xl shadow-lg sticky top-6">
                <div class="space-y-4 mb-6">
                    <div class="bg-gray-800/40 border border-gray-700 rounded-xl p-3">
                        <h3 class="text-white font-black text-sm mb-3">بيانات الفاتورة</h3>
                        <p class="text-[11px] text-gray-400 leading-5">أدخل الأجور والضريبة والملاحظات قبل اختيار طريقة الدفع واعتماد العملية.</p>
                    </div>

                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">🛠️ أجور اليد (التركيب)</label>
                        <input type="number" step="1" x-model.number="labor_total" @focus="$event.target.select()" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-2xl text-center font-black outline-none shadow-inner">
                    </div>

                    <div x-show="labor_total > 0" x-transition>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">📝 وصف العمل / ملاحظات</label>
                        {{-- أزرار جاهزة لتسريع كتابة وصف عمل اليد بدون التأثير على الإدخال اليدوي --}}
                        <div class="flex flex-wrap gap-2 mb-2">
                            <template x-for="option in laborDescriptionOptions" :key="option">
                                <button type="button"
                                        @click="appendLaborDescription(option)"
                                        class="px-3 py-1.5 text-xs rounded-lg border border-gray-600 bg-gray-800/70 text-gray-200 hover:bg-blue-600 hover:border-blue-500 transition">
                                    <span x-text="option"></span>
                                </button>
                            </template>
                        </div>
                        <textarea x-model="description" placeholder="وصف سريع للعمل..." class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm outline-none text-right font-bold" rows="2"></textarea>
                    </div>

                    <div>
                        <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">⚖️ نسبة الضريبة</label>
                        <select x-model.number="tax_rate" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 font-black outline-none text-center">
                            <option value="0">بدون ضريبة (0%)</option>
                            <option value="15">ضريبة (15%)</option>
                        </select>
                    </div>

                    <div class="space-y-4 bg-gray-800/30 border border-gray-700 rounded-xl p-4">
                        {{-- أزرار أنواع الدفع --}}
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-gray-300 text-xs font-black">اختر نوع الدفع</label>
                            <span x-show="sale_type" class="text-[11px] text-emerald-400 font-bold" x-text="'تم الاختيار: ' + (sale_type === 'cash' ? 'كاش' : sale_type === 'card' ? 'شبكة' : sale_type === 'mixed' ? 'مختلط' : 'آجل')"></span>
                        </div>
                        <div class="grid grid-cols-4 gap-2" :class="!sale_type ? 'ring-2 ring-red-500/30 rounded-2xl p-1' : ''">
                            <button type="button" @click="setPaymentType('cash')"
                                    :class="sale_type === 'cash' ? 'bg-green-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'"
                                    class="py-3 rounded-xl text-xs font-black text-white transition-all">كاش</button>

                            <button type="button" @click="setPaymentType('card')"
                                    :class="sale_type === 'card' ? 'bg-blue-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'"
                                    class="py-3 rounded-xl text-xs font-black text-white transition-all">شبكة</button>

                            <button type="button" @click="setPaymentType('mixed')"
                                    :class="sale_type === 'mixed' ? 'bg-purple-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'"
                                    class="py-3 rounded-xl text-xs font-black text-white transition-all">مختلط</button>

                            <button type="button" @click="setPaymentType('credit')"
                                    :class="sale_type === 'credit' ? 'bg-yellow-600 ring-2 ring-white/20 scale-105' : 'bg-gray-800 opacity-60'"
                                    class="py-3 rounded-xl text-xs font-black text-white transition-all">آجل</button>
                        </div>
                        <p x-show="!sale_type" class="text-[11px] text-red-400 font-bold text-center">يجب اختيار نوع الدفع قبل تأكيد العملية.</p>

                        <div x-show="sale_type && sale_type !== 'credit'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-2">
                            <label class="text-gray-400 text-xs font-bold block mb-1 pr-1 italic text-right">💵 المبلغ المستلم</label>
                            <input type="number" step="1" x-model.number="paid_amount" @focus="$event.target.select()" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-3 text-2xl text-center font-black outline-none shadow-inner">
                        </div>

                        {{-- ✅ قسم الدفع المختلط (كاش + شبكة) --}}
                        <div x-show="sale_type === 'mixed'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="mt-4 p-4 bg-purple-900/20 border border-purple-600/50 rounded-xl space-y-3">
                                <p class="text-xs text-purple-400 flex items-center gap-2">
                                    <i class="fa-solid fa-info-circle"></i>
                                    أدخل المبلغ المدفوع نقداً والمبلغ المدفوع بالشبكة
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-gray-400 text-xs block mb-1">💵 نقداً</label>
                                        <input type="number" step="1" x-model.number="mixedCash" @input="updateMixedTotal" @wheel="preventWheelChange($event)"
                                               class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-center font-black outline-none">
                                    </div>
                                    <div>
                                        <label class="text-gray-400 text-xs block mb-1">💳 شبكة</label>
                                        <input type="number" step="1" x-model.number="mixedCard" @input="updateMixedTotal" @wheel="preventWheelChange($event)"
                                               class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-center font-black outline-none">
                                    </div>
                                </div>
                                <div class="flex justify-between text-sm border-t border-gray-700 pt-2">
                                    <span class="text-gray-400">إجمالي المدفوع:</span>
                                    <span class="text-green-400 font-black text-lg" x-text="mixedTotal + ' ر.س'"></span>
                                </div>
                        </div>

                        {{-- ✅ خيار الآجل الجزئي (يظهر مع كاش، شبكة، أو مختلط) --}}
                        <div x-show="sale_type === 'cash' || sale_type === 'card' || sale_type === 'mixed'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="mt-4 p-4 bg-gray-800/80 border border-yellow-600/30 rounded-xl">
                                <div class="flex items-center gap-2 mb-3">
                                    <input type="checkbox" x-model="hasPartialCredit" @change="if(!hasPartialCredit){partial_credit_amount=0;}" id="hasPartialCredit" class="w-4 h-4">
                                    <label for="hasPartialCredit" class="text-white text-sm font-bold">تسجيل المبلغ المتبقي كآجل</label>
                                </div>

                                <div x-show="hasPartialCredit"
                                     x-transition:enter="transition ease-out duration-300"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-200"
                                     x-transition:leave-start="opacity-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 -translate-y-1"
                                     class="space-y-3">
                                    <select x-model="employee_id" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border border-yellow-600/50 text-white rounded-xl px-3 py-3 text-sm">
                                        <option value="">— اختر الموظف —</option>
                                        <template x-for="person in creditPersons" :key="person.id">
                                            <option :value="person.id" x-text="person.name"></option>
                                        </template>
                                    </select>

                                    {{--
                                        حقل "قيمة المديونية" للآجل الجزئي:
                                        - يظهر فقط إذا كان نوع البيع: cash أو card.
                                        - مستقل عن إجمالي الفاتورة والمبلغ المستلم (حسب طلب العمل).
                                    --}}
                                    <div x-show="sale_type === 'cash' || sale_type === 'card'" class="space-y-1">
                                        <label class="text-yellow-400 text-xs font-bold block pr-1 italic text-right">🧾 قيمة المديونية</label>
                                        <input type="number" step="1" min="0" x-model.number="partial_credit_amount" @focus="$event.target.select()" @wheel="preventWheelChange($event)" placeholder="أدخل قيمة المديونية" class="w-full bg-gray-800 border border-yellow-600/40 text-white rounded-xl px-4 py-3 text-center font-black outline-none shadow-inner">
                                        <p class="text-[10px] text-yellow-500 pr-1">هذه القيمة مستقلة ولن تغيّر المبلغ المستلم تلقائياً، لكن يجب أن يكون (المستلم + المديونية) ≥ إجمالي الفاتورة.</p>
                                    </div>

                                    <div class="bg-yellow-900/30 border border-yellow-600/30 rounded-lg p-3">
                                        <p class="text-sm text-yellow-400 flex items-center justify-between">
                                            <span>المديونية المسجلة:</span>
                                            <span class="font-black text-lg" x-text="Math.max(0, Math.round(partial_credit_amount || 0)) + ' ر.س'"></span>
                                        </p>
                                    </div>
                                </div>
                        </div>

                        {{-- خيار الآجل الكامل (يظهر فقط مع آجل) --}}
                        <div x-show="sale_type === 'credit'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="mt-2 space-y-3">
                                <select x-model="employee_id" @wheel="preventWheelChange($event)" class="w-full bg-gray-800 border-2 border-yellow-600/50 text-white rounded-xl px-3 py-3 text-sm font-bold text-right outline-none">
                                    <option value="">— حدد الموظف —</option>
                                    <template x-for="person in creditPersons" :key="person.id">
                                        <option :value="person.id" x-text="person.name"></option>
                                    </template>
                                </select>

                                <div>
                                    <label class="text-yellow-400 text-xs font-bold block mb-1 pr-1 italic text-right">🧾 القيمة الآجلة</label>
                                    <input type="number" step="1" min="0" x-model="agreed_credit_total" @focus="$event.target.select()" @wheel="preventWheelChange($event)" placeholder="أدخل قيمة المديونية" class="w-full bg-gray-800 border-2 border-yellow-600/40 text-white rounded-xl px-4 py-3 text-2xl text-center font-black outline-none shadow-inner">
                                    <p class="text-[10px] text-yellow-500 mt-1 pr-1">يجب إدخال قيمة آجل لا تقل عن إجمالي الفاتورة الحالي: <span class="font-black" x-text="Math.round(base_final_total) + ' ر.س'"></span>.</p>
                                </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800/80 rounded-xl p-4 mb-6 space-y-3 border border-gray-700 text-right text-sm">
                    <div class="flex justify-between text-gray-400 font-bold items-center">
                        <span>أجور اليد:</span>
                        <span x-text="Math.round(labor_total || 0) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400 font-bold items-center">
                        <span>الضريبة:</span>
                        <span x-text="Math.round(tax_value) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-gray-400 font-bold items-center">
                        <span>مجموع المنتجات:</span>
                        <span x-text="Math.round(items_total) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-white font-bold text-xl items-center border-t border-gray-700 pt-2">
                        <span class="text-blue-400 font-black">الإجمالي النهائي:</span>
                        <span class="text-blue-400 text-2xl font-black" x-text="Math.round(final_total) + ' ر.س'"></span>
                    </div>
                    <div class="flex justify-between text-yellow-500 font-bold items-center border-t border-gray-700 pt-2">
                        <span x-text="sale_type === 'credit' ? 'إجمالي المديونية:' : 'المتبقي (دين):'"></span>
                        <span class="font-black" x-text="Math.round(Math.max(0, remaining)) + ' ر.س'"></span>
                    </div>
                </div>

                <div class="space-y-4">
                    {{-- ✅ خيار إنشاء الفاتورة مع التولتيب --}}
                    <div class="flex items-center gap-2 bg-gray-800/50 p-3 rounded-xl border border-gray-700 mt-4">
                        <input type="checkbox" x-model="has_invoice" id="has_invoice" class="w-5 h-5 rounded border-gray-600 bg-gray-700 text-blue-600 focus:ring-blue-500">
                        <label for="has_invoice" class="text-white text-sm font-bold cursor-pointer">إصدار فاتورة ضريبية للمطبوعات</label>
                        <div class="relative group mr-auto">
                            <span class="text-gray-500 text-xs cursor-help border border-gray-700 rounded-full w-4 h-4 flex items-center justify-center">?</span>
                            <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[10px] p-2 rounded-lg w-32 shadow-xl z-50">
                                يتم إنشاء فاتورة ضريبية بعد الحفظ
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-800/40 border border-gray-700 rounded-xl p-4 mb-3">
                    <label class="flex items-center justify-between gap-3 cursor-pointer select-none">
                        <span class="flex items-center gap-2">
                            <span class="block text-gray-300 text-xs font-bold">تثبيت تاريخ مختلف للعمليات المتتالية</span>
                            <span class="relative group inline-flex">
                                <span class="text-gray-500 text-xs cursor-help border border-gray-700 rounded-full w-4 h-4 flex items-center justify-center">?</span>
                                <span class="absolute bottom-full right-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[10px] p-2 rounded-lg w-64 shadow-xl z-50 leading-relaxed">
                                    فعّل هذا الخيار إذا كنت ستدخل عملية أو عدة عمليات متأخرة؛ سيبقى التاريخ محفوظاً لهذا المتصفح حتى تلغي التفعيل.
                                </span>
                            </span>
                        </span>
                        <input type="checkbox" x-model="use_custom_operation_date"
                               @change="handleOperationDateToggle()"
                               class="w-5 h-5 rounded border-gray-600 bg-gray-900 text-blue-600 focus:ring-blue-500/40">
                    </label>

                    <div x-show="use_custom_operation_date" x-transition class="mt-3">
                        <div class="mb-3 flex items-start gap-2 rounded-xl border border-red-500/40 bg-red-500/10 px-3 py-2 text-red-200 shadow-lg shadow-red-950/20">
                            <span class="text-lg leading-none">⚠️</span>
                            <div class="text-[12px] font-black leading-relaxed">
                                تنبيه: التاريخ مثبت الآن على
                                <span class="font-sans text-white" x-text="operation_date"></span>
                                وسيتم استخدامه لكل عملية تحفظها حتى تلغي التثبيت.
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mb-2">
                            <label class="block text-gray-400 text-xs font-bold">تاريخ العمليات الفعلي</label>
                            <span class="relative group inline-flex">
                                <span class="text-gray-500 text-xs cursor-help border border-gray-700 rounded-full w-4 h-4 flex items-center justify-center">?</span>
                                <span class="absolute bottom-full right-0 mb-2 hidden group-hover:block bg-gray-800 border border-gray-700 text-white text-[10px] p-2 rounded-lg w-64 shadow-xl z-50 leading-relaxed">
                                    سيُرسل هذا التاريخ مع كل عملية بيع تحفظها من هذه الشاشة إلى أن توقف خيار التثبيت أو تغيّر التاريخ.
                                </span>
                            </span>
                        </div>
                        <input type="date" x-model="operation_date" @change="persistOperationDateSetting()" @wheel="preventWheelChange($event)" max="{{ now()->toDateString() }}"
                               class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm font-bold outline-none focus:ring-2 focus:ring-blue-500/40">
                        <p class="mt-2 text-[11px] text-amber-300 bg-amber-500/10 border border-amber-500/20 rounded-lg px-3 py-2 leading-relaxed">
                            تنبيه: التاريخ المثبت سيُطبَّق على العمليات القادمة أيضاً، وليس على العملية الحالية فقط. ألغِ التفعيل بعد الانتهاء من إدخال عمليات ذلك اليوم.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-800/40 border border-gray-700 rounded-xl p-4 space-y-3">
                    <h3 class="text-white font-black text-sm">ملخص العملية قبل التأكيد</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-gray-900/70 rounded-lg px-3 py-2 border border-gray-700">
                            <div class="text-gray-400">نوع الدفع</div>
                            <div class="text-white font-black mt-1" x-text="sale_type ? (sale_type === 'cash' ? 'كاش' : sale_type === 'card' ? 'شبكة' : sale_type === 'mixed' ? 'مختلط' : 'آجل') : 'غير محدد'"></div>
                        </div>
                        <div class="bg-gray-900/70 rounded-lg px-3 py-2 border border-gray-700">
                            <div class="text-gray-400">الإجمالي</div>
                            <div class="text-blue-400 font-black mt-1" x-text="Math.round(final_total) + ' ر.س'"></div>
                        </div>
                        <div class="bg-gray-900/70 rounded-lg px-3 py-2 border border-gray-700">
                            <div class="text-gray-400">المدفوع</div>
                            <div class="text-emerald-400 font-black mt-1" x-text="sale_type === 'mixed' ? (Math.round(mixedTotal) + ' ر.س') : (sale_type === 'credit' ? '0 ر.س' : (Math.round(paid_amount || 0) + ' ر.س'))"></div>
                        </div>
                        <div class="bg-gray-900/70 rounded-lg px-3 py-2 border border-gray-700">
                            <div class="text-gray-400" x-text="sale_type === 'credit' ? 'المديونية' : 'المتبقي'"></div>
                            <div class="text-yellow-400 font-black mt-1" x-text="Math.round(Math.max(0, remaining)) + ' ر.س'"></div>
                        </div>
                    </div>
                    <div x-show="employee_id" class="text-[11px] text-gray-300 border-t border-gray-700 pt-3">
                        الموظف المرتبط: <span class="font-black text-white" x-text="creditPersons.find(person => String(person.id) === String(employee_id))?.name || 'غير معروف'"></span>
                    </div>
                </div>

                <form method="POST" action="{{ route('accountant.quick-sale.submit') }}" x-ref="saleForm" class="mt-6">
                    @csrf
                    <input type="hidden" name="items" x-model="items_json">
                    <input type="hidden" name="labor_total" :value="Math.round(labor_total)">
                    <input type="hidden" name="paid_amount" :value="sale_type === 'credit' ? 0 : Math.round(paid_amount)">
                    <input type="hidden" name="tax_rate" x-model="tax_rate">
                    <input type="hidden" name="sale_type" x-model="sale_type">
                    <input type="hidden" name="employee_id" x-model="employee_id">
                    <input type="hidden" name="description" x-model="description">
                    <input type="hidden" name="has_invoice" :value="has_invoice ? 1 : 0">
                    <input type="hidden" name="has_partial_credit" :value="hasPartialCredit ? 1 : 0">
                    <input type="hidden" name="debt_amount" :value="hasPartialCredit ? Math.round(partial_credit_amount || 0) : ''">
                    <input type="hidden" name="mixed_cash" :value="mixedCash">
                    <input type="hidden" name="mixed_card" :value="mixedCard">
                    <input type="hidden" name="agreed_credit_total" :value="sale_type === 'credit' ? Math.round(agreed_credit_total || 0) : ''">
                    <input type="hidden" name="operation_date" :value="use_custom_operation_date ? operation_date : ''">

                    <button type="button" @click="prepareForm($refs.saleForm)"
                            :class="!sale_type ? 'bg-gray-600 cursor-not-allowed opacity-80' : 'bg-blue-600 hover:bg-blue-500 active:scale-95'"
                            class="w-full text-white py-5 rounded-2xl font-black text-xl transition-all shadow-2xl">
                        <span x-text="sale_type ? 'تأكيد العملية ✅' : 'اختر نوع الدفع أولاً'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function quickSale() {
    return {
        search: '',
        results: [],
        cart: [],
        labor_total: 0,
        paid_amount: 0,
        agreed_credit_total: 0,
        tax_rate: 0,
        sale_type: '',
        use_custom_operation_date: false,
        operation_date: '{{ now()->toDateString() }}',
        employee_id: '',
        creditPersons: [],
        description: '',
        // خيارات وصف عمل اليد الأكثر استخداماً (تظهر كأزرار أعلى حقل الوصف).
        laborDescriptionOptions: ['تضليل', 'تجليد', 'شغل يد'],
        items_json: '',
        has_invoice: false,
        hasPartialCredit: false,
        partial_credit_amount: 0,
        mixedCash: 0,
        mixedCard: 0,
        showingFeatured: true,
        hasStoreTaxNumber: {{ auth('accountant')->user()->store->tax_number ? 'true' : 'false' }},

        init() {
            this.restoreOperationDateSetting();
            this.loadCreditPersons();
            this.loadFeaturedProducts();
            this.$nextTick(() => { this.$refs.searchInput.focus(); });
        },

        operationDateStorageKey() {
            return 'quick_sale_operation_date_{{ auth('accountant')->id() }}_{{ auth('accountant')->user()->store_id }}';
        },

        todayDate() {
            return '{{ now()->toDateString() }}';
        },

        restoreOperationDateSetting() {
            try {
                const saved = JSON.parse(localStorage.getItem(this.operationDateStorageKey()) || '{}');
                const today = this.todayDate();

                if (saved.enabled && saved.date && saved.date <= today) {
                    this.use_custom_operation_date = true;
                    this.operation_date = saved.date;
                    return;
                }

                if (saved.enabled) {
                    localStorage.removeItem(this.operationDateStorageKey());
                }
            } catch (error) {
                localStorage.removeItem(this.operationDateStorageKey());
            }

            this.use_custom_operation_date = false;
            this.operation_date = this.todayDate();
        },

        handleOperationDateToggle() {
            if (this.use_custom_operation_date && !this.operation_date) {
                this.operation_date = this.todayDate();
            }

            this.persistOperationDateSetting();
        },

        persistOperationDateSetting() {
            if (!this.use_custom_operation_date) {
                localStorage.removeItem(this.operationDateStorageKey());
                this.operation_date = this.todayDate();
                return;
            }

            localStorage.setItem(this.operationDateStorageKey(), JSON.stringify({
                enabled: true,
                date: this.operation_date || this.todayDate(),
            }));
        },

        preventWheelChange(event) {
            event.preventDefault();
            event.currentTarget.blur();
            window.scrollBy({ top: event.deltaY, left: event.deltaX, behavior: 'auto' });
        },

        async loadCreditPersons() {
            try {
                // نبني الرابط من المسار الحالي لتفادي أي تعارض في route helper داخل Blade.
                const creditPersonsEndpoint = window.location.pathname.replace(/\/quick-sale\/?$/, '/quick-sale/credit-persons');
                let res = await fetch(creditPersonsEndpoint);
                this.creditPersons = await res.json();
            } catch (e) { console.error('Error loading employees'); }
        },

        productSearchUrl(query) {
            const url = new URL("{{ route('accountant.products.search') }}", window.location.origin);
            url.searchParams.set('query', query || '');
            url.searchParams.set('_ts', Date.now().toString());
            return url.toString();
        },

        async loadFeaturedProducts() {
            this.showingFeatured = true;
            try {
                let res = await fetch(this.productSearchUrl(''), { cache: 'no-store' });
                this.results = await res.json();
            } catch (e) { console.error('Featured products error'); }
        },

        async searchProducts() {
            if (this.search.trim().length < 1) {
                await this.loadFeaturedProducts();
                return;
            }

            this.showingFeatured = false;
            try {
                let res = await fetch(this.productSearchUrl(this.search), { cache: 'no-store' });
                this.results = await res.json();
            } catch (e) { console.error('Search error'); }
        },

        displayPriceLabel(product) {
            const basePrice = Math.round(Number(product?.price ?? 0));
            const piecePrice = Math.round(Number(product?.piece_price ?? 0));

            if (product?.is_splittable == 1 && piecePrice > 0) {
                return `حبة ${piecePrice} / طقم ${basePrice} ر.س`;
            }

            return `${basePrice} ر.س`;
        },

        addToCart(product) {
            if (parseFloat(product.quantity) <= 0) {
                Swal.fire({ title: 'عذراً', text: 'هذا المنتج منتهي من المخزون', icon: 'error' });
                return;
            }

            let temp_id = Date.now() + Math.random();
            let basePrice = Math.round(parseFloat(product.price)) || 0;
            let piecePrice = Math.round(product.piece_price) || 0;
            let isSplittableUnit = product.is_splittable == 1 && product.product_type !== 'fractional';
            let preferredUnit = (product.quick_sale_default_unit === 'piece') ? 'piece' : 'unit';
            let defaultSaleUnit = isSplittableUnit ? preferredUnit : 'unit';
            let defaultPrice = isSplittableUnit
                ? (defaultSaleUnit === 'piece' ? piecePrice : basePrice)
                : basePrice;

            this.cart.push({
                temp_id: temp_id,
                product_id: product.id,
                name: product.name,
                is_fractional: product.product_type === 'fractional',
                is_splittable: product.is_splittable == 1,
                items_per_unit: product.items_per_unit || 1,
                piece_price: piecePrice,
                sale_unit: defaultSaleUnit,
                base_price: basePrice,
                price: defaultPrice,
                quantity: 1,
                total: defaultPrice,
                fraction_id: '0',
                is_custom: false,
                custom_name: '',
                custom_consumption: '',
                available_fractions: product.fractions || []
            });

            this.search = '';
            this.loadFeaturedProducts();
            this.$refs.searchInput.focus();
        },

        updateSplittablePrice(item) {
            if (item.sale_unit === 'piece') {
                item.price = item.piece_price;
            } else {
                item.price = item.base_price;
            }
            this.calculateItemTotal(item);
        },

        appendLaborDescription(option) {
            const currentValue = (this.description || '').trim();

            // إذا كان الوصف فارغاً نضع الخيار مباشرة كنص ابتدائي.
            if (!currentValue) {
                this.description = option;
                return;
            }

            // منع تكرار نفس الخيار داخل الوصف عند الضغط عليه أكثر من مرة.
            if (currentValue.includes(option)) {
                return;
            }

            // عند وجود نص سابق: نضيف الخيار الجديد في نهاية الوصف مع فاصل واضح.
            this.description = `${currentValue} - ${option}`;
        },

        updateFractionPrice(item) {
            if (item.fraction_id === 'custom') {
                item.is_custom = true;
                item.price = 0;
                item.custom_consumption = '';
            } else if (item.fraction_id && item.fraction_id !== '0') {
                item.is_custom = false;
                let selected = item.available_fractions.find(f => f.id == item.fraction_id);
                if (selected) { item.price = Math.round(selected.price); }
            } else {
                item.is_custom = false;
                item.price = item.base_price;
            }
            this.calculateItemTotal(item);
        },

        calculateItemTotal(item) {
            item.total = item.quantity * item.price;
        },

        increase(item) { if (item.is_fractional) return; item.quantity++; this.calculateItemTotal(item); },
        decrease(item) { if (item.is_fractional) return; if (item.quantity > 1) { item.quantity--; this.calculateItemTotal(item); } },
        removeItem(item) { this.cart = this.cart.filter(i => i.temp_id !== item.temp_id); },

        get items_total() { return this.cart.reduce((sum, item) => sum + (Math.round(item.total) || 0), 0); },
        get tax_value() { return (this.items_total * this.tax_rate) / 100; },
        get base_final_total() { return Math.round(this.items_total + this.tax_value + (Math.round(this.labor_total) || 0)); },
        get final_total() {
            if (this.sale_type === 'credit' && (this.agreed_credit_total || 0) > 0) {
                return Math.round(this.agreed_credit_total);
            }
            return this.base_final_total;
        },
        get remaining() {
            if (this.sale_type === 'credit') {
                return this.final_total;
            }
            return this.final_total - (Math.round(this.paid_amount) || 0);
        },

        get mixedTotal() {
            return (this.mixedCash || 0) + (this.mixedCard || 0);
        },

        setPaymentType(type) {
            this.sale_type = type;
            if (type === 'cash' || type === 'card' || type === 'credit') {

                this.mixedCash = 0;
                this.mixedCard = 0;
            }

            if (type === 'credit') {
                this.paid_amount = 0;
                this.hasPartialCredit = false;
                this.partial_credit_amount = 0;
                this.agreed_credit_total = '';
            } else {
                this.agreed_credit_total = '';
                this.partial_credit_amount = 0;
            }
        },

        async prepareForm(form) {
            if (this.cart.length === 0 && Math.round(this.labor_total) <= 0) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى إضافة منتج أو أجور يد.', icon: 'warning' });
            }

            if (this.labor_total > 0 && (!this.description || this.description.trim().length < 3)) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى كتابة وصف العمل في خانة الملاحظات.', icon: 'warning', confirmButtonText: 'حسناً' });
            }

            for (let item of this.cart) {
                if (item.is_fractional && Number(item.quantity) !== 1) {
                    return Swal.fire({ title: 'تنبيه', text: `منتج الرول ${item.name} يباع كسطر مستقل ولا يقبل تغيير الكمية.`, icon: 'warning' });
                }

                if (item.is_fractional && (item.fraction_id === '0' || !item.fraction_id)) {
                    return Swal.fire({ title: 'تنبيه', text: `يرجى اختيار نوع التجزئة لـ ${item.name}`, icon: 'warning' });
                }

                if (item.is_fractional && item.fraction_id === 'custom' && ((Number(item.custom_consumption) || 0) <= 0 || (Number(item.price) || 0) <= 0)) {
                    return Swal.fire({ title: 'تنبيه', text: `القص المخصص لـ ${item.name} يتطلب أمتاراً وسعراً أكبر من صفر.`, icon: 'warning' });
                }
            }

            if (!this.sale_type) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى اختيار نوع الدفع أولاً.', icon: 'warning', confirmButtonText: 'حسناً' });
            }

            // التحقق من الآجل الكامل
            if (this.sale_type === 'credit' && !this.employee_id) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى اختيار الموظف للبيع الآجل.', icon: 'warning', confirmButtonText: 'حسناً' });
            }

            const agreedCreditTotal = Math.round(Number(this.agreed_credit_total) || 0);

            if (this.sale_type === 'credit' && agreedCreditTotal <= 0) {
                return Swal.fire({ title: 'تنبيه', text: 'يرجى إدخال القيمة الآجلة للفاتورة.', icon: 'warning', confirmButtonText: 'حسناً' });
            }

            if (this.sale_type === 'credit' && agreedCreditTotal < this.base_final_total) {
                return Swal.fire({
                    title: 'تنبيه',
                    text: `قيمة المديونية يجب أن تكون مساوية لإجمالي الفاتورة الحالي أو أعلى منه (${Math.round(this.base_final_total)} ريال).`,
                    icon: 'warning',
                    confirmButtonText: 'حسناً'
                });
            }

            // التحقق من الآجل الجزئي
            if (this.hasPartialCredit && !this.employee_id) {
                return Swal.fire({
                    title: 'تنبيه',
                    text: 'يرجى اختيار الموظف لتسجيل المبلغ المتبقي كآجل.',
                    icon: 'warning'
                });
            }

            if (this.hasPartialCredit && (this.sale_type === 'cash' || this.sale_type === 'card')) {
                const debtAmount = Math.round(Number(this.partial_credit_amount) || 0);
                const paidAmount = Math.round(Number(this.paid_amount) || 0);

                if (debtAmount <= 0) {
                    return Swal.fire({ title: 'تنبيه', text: 'يرجى إدخال قيمة المديونية.', icon: 'warning' });
                }

                if ((paidAmount + debtAmount) < Math.round(this.final_total)) {
                    return Swal.fire({
                        title: 'خطأ محاسبي',
                        text: `مجموع المستلم (${paidAmount} ريال) + المديونية (${debtAmount} ريال) أقل من إجمالي الفاتورة (${Math.round(this.final_total)} ريال).`,
                        icon: 'error'
                    });
                }
            }

            // التحقق من المبلغ المدفوع حسب نوع البيع
            if (this.sale_type === 'mixed') {
                if (this.mixedTotal < this.final_total && !this.hasPartialCredit) {
                    return Swal.fire({
                        title: 'خطأ في الدفع',
                        text: `إجمالي المدفوع (${this.mixedTotal} ريال) أقل من قيمة الفاتورة (${this.final_total} ريال)`,
                        icon: 'error'
                    });
                }

                // إضافة تفاصيل الدفع المختلط إلى items_json
                let cartData = [...this.cart];
                cartData.push({
                    _temp: true,
                    payment_details: {
                        cash: this.mixedCash,
                        card: this.mixedCard
                    }
                });
                this.items_json = JSON.stringify(cartData);
            }
            else if ((this.sale_type === 'cash' || this.sale_type === 'card') && !this.hasPartialCredit && this.paid_amount < this.final_total) {
                return Swal.fire({
                    title: 'خطأ في الدفع',
                    text: `المبلغ المدفوع (${Math.round(this.paid_amount)} ريال) أقل من قيمة الفاتورة (${Math.round(this.final_total)} ريال).`,
                    icon: 'error',
                    confirmButtonText: 'تعديل'
                });
            } else {
                this.items_json = JSON.stringify(this.cart);
            }

            if (this.use_custom_operation_date) {
                const pinnedDateResult = await Swal.fire({
                    title: 'تنبيه: التاريخ مثبت',
                    text: `سيتم حفظ هذه العملية بتاريخ ${this.operation_date} وليس بتاريخ اليوم. هل تريد المتابعة؟`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d97706',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'نعم، احفظ بالتاريخ المثبت',
                    cancelButtonText: 'إلغاء'
                });

                if (!pinnedDateResult.isConfirmed) return;
            }

            if (this.tax_rate > 0 && !this.hasStoreTaxNumber) {
                const result = await Swal.fire({
                    title: 'إخلاء مسؤولية ضريبية',
                    text: 'أنت بصدد فرض ضريبة بينما المتجر لا يملك رقماً ضريبياً مسجلاً. هل تتحمل مسؤولية هذا الإجراء قانونياً؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، استمرار',
                    cancelButtonText: 'إلغاء'
                });

                if (!result.isConfirmed) return;
            }

            this.$nextTick(() => form.submit());
        }
    }
}
</script>
@endsection
