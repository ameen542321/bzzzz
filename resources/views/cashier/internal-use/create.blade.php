@extends('dashboard.app')

@section('title', 'تسجيل استهلاك داخلي')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-4 sm:py-6 space-y-6"
     x-data="internalUseForm()"
     x-init="init()"
     x-cloak>

    {{-- الهيدر - متوافق مع الجوال --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-purple-500/20">
                <i class="fa-solid fa-box-open text-white text-lg sm:text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-white">تسجيل استهلاك داخلي</h1>
                <p class="text-xs sm:text-sm text-gray-400 mt-1">خصم مواد من المخزون للاستخدام العملي</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('accountant.dashboard') }}"
               class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-200 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg text-xs sm:text-sm transition group relative">
                <svg class="w-3 h-3 sm:w-4 sm:h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="hidden sm:inline"> الرئيسية </span>
                <span class="sm:hidden">رجوع</span>
            </a>
        </div>
    </div>

    {{-- التنبيهات --}}
    @if(session('success'))
        <div class="p-3 sm:p-4 bg-green-900/20 border border-green-800/50 text-green-400 rounded-xl flex items-center gap-2 sm:gap-3 text-sm">
            <i class="fa-solid fa-circle-check flex-shrink-0"></i>
            <span class="font-medium flex-1">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 sm:p-4 bg-red-900/20 border border-red-800/50 text-red-400 rounded-xl flex items-center gap-2 sm:gap-3 text-sm">
            <i class="fa-solid fa-circle-exclamation flex-shrink-0"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- النموذج الرئيسي --}}
    <div class="bg-gray-900/50 border border-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl backdrop-blur-sm">
        <form action="{{ route('accountant.internal-use.store') }}" method="POST" class="space-y-4 sm:space-y-6">
            @csrf

            <div class="space-y-4 sm:space-y-6">
                {{-- اختيار المنتج مع البحث --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-300 flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-gray-500 text-xs"></i>
                        اختر المنتج المستهلك
                    </label>
                    
                    {{-- حقل البحث --}}
                    <div class="relative mb-2">
                        <input type="text"
                               x-model="searchQuery"
                               @input="filterProducts"
                               placeholder="🔍 ابحث عن منتج بالاسم أو الباركود..."
                               class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-3 pr-10 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition outline-none text-sm">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-500"></i>
                        <button type="button"
                                x-show="searchQuery"
                                @click="clearSearch"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>

                    <input type="hidden" name="product_id" x-model="productId" required>

                    {{-- شبكة المنتجات بدلاً من select --}}
                    <div class="border border-gray-700 rounded-xl p-2 bg-gray-800/40 max-h-72 overflow-y-auto">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="product in filteredProducts" :key="product.id">
                                <button type="button"
                                        @click="selectProduct(product.id)"
                                        class="text-right rounded-lg border px-3 py-2 transition"
                                        :class="productId == product.id
                                            ? 'bg-purple-600/20 border-purple-500 text-white'
                                            : 'bg-gray-900/60 border-gray-700 text-gray-200 hover:border-purple-500/60 hover:bg-gray-900'">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-bold text-sm truncate" x-text="product.name"></p>
                                        <span class="text-[11px] text-gray-400" x-text="product.barcode || 'بدون باركود'"></span>
                                    </div>
                                    <p class="text-xs mt-1 text-gray-400" x-text="product.stock_label"></p>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- عدد النتائج --}}
                    <div x-show="searchQuery" class="text-xs text-gray-400" x-text="filteredProducts.length + ' نتيجة'"></div>

                    {{-- رسالة عدم وجود نتائج --}}
                    <div x-show="searchQuery && filteredProducts.length === 0"
                         class="mt-2 p-3 bg-yellow-900/20 border border-yellow-800/50 text-yellow-400 rounded-lg text-sm">
                        <i class="fa-solid fa-exclamation-triangle ml-2"></i>
                        لا توجد منتجات مطابقة لبحث " <span x-text="searchQuery"></span> "
                    </div>
                </div>

                {{-- معلومات المنتج المحدد --}}
                <template x-if="productId && selectedProduct">
                    <div class="bg-gray-800/30 border border-gray-700 rounded-lg sm:rounded-xl p-3 sm:p-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-white text-lg sm:text-xl truncate"
                                    x-text="selectedProduct.name">
                                </h3>
                               
                            </div>
                            <div class="flex items-center justify-between sm:justify-end gap-4 mt-2 sm:mt-0">
                                <div class="text-left sm:text-right">
                                    {{-- عرض المخزون بشكل مفصل --}}
                                    <div class="text-base sm:text-lg font-bold text-green-400"
                                         x-html="getDetailedStockDisplay()">
                                    </div>

                                    {{-- عرض طول الرول للمنتجات الكسرية --}}
                                    <template x-if="selectedProduct.productType === 'fractional' && selectedProduct.rollLength > 0">
                                        <div class="text-xs text-gray-400 mt-1">
                                            <i class="fa-solid fa-ruler"></i>
                                            طول الرول: <span class="text-white" x-text="selectedProduct.rollLength + ' م'"></span>
                                        </div>
                                    </template>

                                    {{-- عرض عدد الحبات في الطقم للمنتجات القابلة للتجزئة --}}
                                    <template x-if="selectedProduct.isSplittable && selectedProduct.itemsPerUnit > 1">
                                        <div class="text-xs text-gray-400 mt-1">
                                            <i class="fa-solid fa-cubes"></i>
                                            الطقم: <span class="text-white" x-text="selectedProduct.itemsPerUnit + ' حبة'"></span>
                                        </div>
                                    </template>

                                    {{-- عرض نسبة الهالك إن وجدت --}}
                                    <template x-if="selectedProduct.wastePercentage > 0">
                                        <div class="text-xs text-amber-400 mt-1">
                                            <i class="fa-solid fa-flask"></i>
                                            هالك: <span x-text="selectedProduct.wastePercentage + '%'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- اختيار وحدة القياس للمنتجات القابلة للتجزئة --}}
                <template x-if="productId && selectedProduct && selectedProduct.isSplittable">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300">وحدة الخصم</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-3 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-750 transition"
                                   :class="{'ring-2 ring-purple-500 border-purple-500': unitType === 'kit'}">
                                <input type="radio" x-model="unitType" value="kit" class="sr-only">
                                <i class="fa-solid fa-cubes text-purple-400"></i>
                                <span class="text-sm text-white">طقم كامل</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-750 transition"
                                   :class="{'ring-2 ring-purple-500 border-purple-500': unitType === 'piece'}">
                                <input type="radio" x-model="unitType" value="piece" class="sr-only">
                                <i class="fa-solid fa-cube text-blue-400"></i>
                                <span class="text-sm text-white">حبة</span>
                            </label>
                        </div>
                    </div>
                </template>

                {{-- اختيار وحدة القياس للمنتجات الكسرية --}}
                <template x-if="productId && selectedProduct && selectedProduct.productType === 'fractional'">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-gray-300">وحدة القياس</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 p-3 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-750 transition"
                                   :class="{'ring-2 ring-purple-500 border-purple-500': unitType === 'roll'}">
                                <input type="radio" x-model="unitType" value="roll" class="sr-only">
                                <i class="fa-solid fa-roll-forward text-purple-400"></i>
                                <span class="text-sm text-white">رول</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 bg-gray-800 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-750 transition"
                                   :class="{'ring-2 ring-purple-500 border-purple-500': unitType === 'meters'}">
                                <input type="radio" x-model="unitType" value="meters" class="sr-only">
                                <i class="fa-solid fa-ruler text-blue-400"></i>
                                <span class="text-sm text-white">أمتار</span>
                            </label>
                        </div>
                    </div>
                </template>

                {{-- الكمية - تظهر فقط بعد اختيار نوع الوحدة --}}
                <template x-if="productId && selectedProduct && unitType !== 'default'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-300">
                                <span x-show="unitType === 'meters'">الأمتار المطلوبة</span>
                                <span x-show="unitType === 'piece'">عدد الحبات</span>
                                <span x-show="unitType === 'kit'">عدد الأطقم</span>
                                <span x-show="unitType === 'roll'">عدد الرولات</span>
                            </label>
                            <span class="text-xs text-gray-500">
                                المتوفر: <span class="font-bold" x-html="getAvailableStockDisplay()"></span>
                            </span>
                        </div>

                        {{-- أزرار الأمتار --}}
                        <div x-show="unitType === 'meters'" class="space-y-3">
                            {{-- تنبيه توضيحي للأمتار --}}
                            <div class="bg-blue-900/20 border border-blue-800/50 text-blue-400 rounded-lg p-3 text-xs flex items-start gap-2">
                                <i class="fa-solid fa-info-circle mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <span class="font-bold">تنبيه:</span> سيتم خصم الأمتار المدخلة مباشرة من إجمالي المخزون.
                                    <span class="block mt-1 text-blue-300">مثال: ربع متر = 0.25 متر من إجمالي <span x-text="selectedProduct.stock.toFixed(2) + ' متر'"></span></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2">
                                <button type="button"
                                        @click="setMeterQuantity(0.25)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-purple-500">
                                    ¼ متر
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                                        ربع متر (0.25 م)
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setMeterQuantity(0.5)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-purple-500">
                                    ½ متر
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                                        نصف متر (0.5 م)
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setMeterQuantity(1)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-purple-500">
                                    1 متر
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50">
                                        متر واحد
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setMeterQuantity(getMaxValue())"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-purple-500">
                                    الكل
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50"
                                          x-text="'كل المتوفر (' + getMaxValue().toFixed(2) + ' م)'">
                                    </span>
                                </button>
                            </div>

                            {{-- حقل إدخال الأمتار --}}
                            <div class="relative mt-3">
                                <input type="number"
                                       name="quantity"
                                       x-model="quantity"
                                       step="0.01"
                                       :min="0.01"
                                       :max="getMaxValue()"
                                       required
                                       @input="validateMeterQuantity"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-4 text-center focus:ring-2 focus:ring-purple-500 outline-none transition text-base"
                                       placeholder="أدخل الأمتار المطلوبة">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base">م</div>
                            </div>
                        </div>

                        {{-- أزرار الرولات --}}
                        <div x-show="unitType === 'roll'" class="space-y-3">
                            {{-- تنبيه توضيحي للرولات --}}
                            <div class="bg-amber-900/20 border border-amber-800/50 text-amber-400 rounded-lg p-3 text-xs flex items-start gap-2">
                                <i class="fa-solid fa-triangle-exclamation mt-0.5 flex-shrink-0"></i>
                                <div>
                                    <span class="font-bold">تنبيه:</span> عند اختيار رول، سيتم خصم <span x-text="selectedProduct.rollLength + ' متر'"></span> لكل رول كامل.
                                    <span class="block mt-1 text-amber-300">مثال: نصف رول = <span x-text="(selectedProduct.rollLength / 2).toFixed(2) + ' متر'"></span></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-4 gap-2">
                                <button type="button"
                                        @click="setRollQuantity(0.25)"
                                        class="bg-gray-800 hover:bg-amber-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-amber-500">
                                    ¼ رول
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50"
                                          x-text="'ربع رول (ما يعادل ' + (selectedProduct.rollLength * 0.25).toFixed(2) + ' م)'">
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setRollQuantity(0.5)"
                                        class="bg-gray-800 hover:bg-amber-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-amber-500">
                                    ½ رول
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50"
                                          x-text="'نصف رول (ما يعادل ' + (selectedProduct.rollLength * 0.5).toFixed(2) + ' م)'">
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setRollQuantity(1)"
                                        class="bg-gray-800 hover:bg-amber-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-amber-500">
                                    1 رول
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50"
                                          x-text="'رول كامل (' + selectedProduct.rollLength + ' م)'">
                                    </span>
                                </button>
                                <button type="button"
                                        @click="setRollQuantity(getMaxValue())"
                                        class="bg-gray-800 hover:bg-amber-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition relative group border border-gray-700 hover:border-amber-500">
                                    الكل
                                    <span class="absolute -top-8 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-800 text-white text-xs rounded py-1 px-2 whitespace-nowrap border border-gray-700 z-50"
                                          x-text="'كل الرولات (' + getMaxValue().toFixed(2) + ' رول)'">
                                    </span>
                                </button>
                            </div>

                            {{-- حقل إدخال الرولات --}}
                            <div class="relative mt-3">
                                <input type="number"
                                       name="quantity"
                                       x-model="quantity"
                                       step="0.001"
                                       :min="0.001"
                                       :max="getMaxValue()"
                                       required
                                       @input="validateRollQuantity"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-4 text-center focus:ring-2 focus:ring-amber-500 outline-none transition text-base"
                                       placeholder="أدخل عدد الرولات">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base">رول</div>
                            </div>
                        </div>

                        {{-- للأطقم --}}
                        <div x-show="unitType === 'kit'" class="space-y-3">
                            <div class="bg-green-900/20 border border-green-800/50 text-green-400 rounded-lg p-3 text-xs">
                                <i class="fa-solid fa-cubes"></i>
                                سيتم خصم الأطقم كاملة من المخزون
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button"
                                        @click="setKitQuantity(1)"
                                        class="bg-gray-800 hover:bg-green-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-green-500">
                                    1 طقم
                                </button>
                                <button type="button"
                                        @click="setKitQuantity(2)"
                                        class="bg-gray-800 hover:bg-green-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-green-500">
                                    2 طقم
                                </button>
                                <button type="button"
                                        @click="setKitQuantity(3)"
                                        class="bg-gray-800 hover:bg-green-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-green-500">
                                    3 طقم
                                </button>
                                <button type="button"
                                        @click="setKitQuantity(getMaxValue())"
                                        class="bg-gray-800 hover:bg-green-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-green-500">
                                    الكل
                                </button>
                            </div>

                            {{-- حقل إدخال الأطقم --}}
                            <div class="relative mt-3">
                                <input type="number"
                                       name="quantity"
                                       x-model="quantity"
                                       step="1"
                                       :min="1"
                                       :max="getMaxValue()"
                                       required
                                       @input="validateKitQuantity"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-4 text-center focus:ring-2 focus:ring-green-500 outline-none transition text-base"
                                       placeholder="أدخل عدد الأطقم">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base">طقم</div>
                            </div>
                        </div>

                        {{-- للحبات --}}
                        <div x-show="unitType === 'piece'" class="space-y-3">
                            <div class="bg-purple-900/20 border border-purple-800/50 text-purple-400 rounded-lg p-3 text-xs">
                                <i class="fa-solid fa-cube"></i>
                                سيتم خصم الحبات بشكل فردي (تحسب كسور من الطقم)
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <button type="button"
                                        @click="setPieceQuantity(1)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                    1 حبة
                                </button>
                                <button type="button"
                                        @click="setPieceQuantity(2)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                    2 حبة
                                </button>
                                <button type="button"
                                        @click="setPieceQuantity(3)"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                    3 حبة
                                </button>
                                <button type="button"
                                        @click="setPieceQuantity(getMaxValue())"
                                        class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                    الكل
                                </button>
                            </div>

                            {{-- حقل إدخال الحبات --}}
                            <div class="relative mt-3">
                                <input type="number"
                                       name="quantity"
                                       x-model="quantity"
                                       step="1"
                                       :min="1"
                                       :max="getMaxValue()"
                                       required
                                       @input="validatePieceQuantity"
                                       class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-4 text-center focus:ring-2 focus:ring-purple-500 outline-none transition text-base"
                                       placeholder="أدخل عدد الحبات">
                                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base">حبة</div>
                            </div>
                        </div>

                        {{-- عرض تفاصيل الخصم --}}
                        <div x-show="quantity > 0" class="mt-2 text-sm bg-gray-800/50 rounded-lg p-3">
                            <div class="flex justify-between text-gray-300">
                                <span class="font-medium">سيتم الخصم:</span>
                                <span class="font-bold text-purple-400" x-html="getDeductionDetails()"></span>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- للمنتجات العادية (قطعة) --}}
                <template x-if="productId && selectedProduct && !selectedProduct.isSplittable && selectedProduct.productType !== 'fractional'">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-300">الكمية (قطعة)</label>
                            <span class="text-xs text-gray-500">
                                المتوفر: <span class="font-bold text-white" x-text="Math.floor(selectedProduct.stock) + ' قطعة'"></span>
                            </span>
                        </div>

                        {{-- أزرار سريعة --}}
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button"
                                    @click="setNormalQuantity(1)"
                                    class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                1
                            </button>
                            <button type="button"
                                    @click="setNormalQuantity(2)"
                                    class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                2
                            </button>
                            <button type="button"
                                    @click="setNormalQuantity(3)"
                                    class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                3
                            </button>
                            <button type="button"
                                    @click="setNormalQuantity(Math.floor(selectedProduct.stock))"
                                    class="bg-gray-800 hover:bg-purple-900/50 text-gray-300 hover:text-white py-3 rounded-lg text-xs sm:text-sm transition border border-gray-700 hover:border-purple-500">
                                الكل
                            </button>
                        </div>

                        {{-- حقل إدخال الكمية --}}
                        <div class="relative mt-3">
                            <input type="number"
                                   name="quantity"
                                   x-model="quantity"
                                   step="1"
                                   :min="1"
                                   :max="Math.floor(selectedProduct.stock)"
                                   required
                                   @input="validateNormalQuantity"
                                   class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-4 py-4 text-center focus:ring-2 focus:ring-purple-500 outline-none transition text-base"
                                   placeholder="أدخل عدد القطع">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-base">قطعة</div>
                        </div>

                        {{-- تحذير الكمية الزائدة --}}
                        <template x-if="quantity > Math.floor(selectedProduct.stock)">
                            <div class="p-2 bg-red-900/20 border border-red-800/50 text-red-400 rounded-lg text-xs">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                الكمية المتوفرة: <span x-text="Math.floor(selectedProduct.stock) + ' قطعة'"></span>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- ملاحظات داخلية فقط --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-300">ملاحظات (اختياري)</label>
                    <textarea name="internal_notes"
                              x-model="internalNotes"
                              placeholder="أدخل ملاحظات عن الاستهلاك..."
                              rows="2"
                              class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg sm:rounded-xl px-3 py-3 focus:ring-2 focus:ring-purple-500 outline-none transition resize-none text-sm">
                    </textarea>
                </div>

                {{-- حقل خفي لنوع الوحدة والكمية --}}
                <input type="hidden" name="unit_type" x-model="unitType">
                <input type="hidden" name="quantity" x-model="quantity">

                {{-- زر الإرسال --}}
                <button type="submit"
                        :disabled="!canSubmit()"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 disabled:from-gray-700 disabled:to-gray-800 disabled:text-gray-500 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg shadow-purple-500/10 transition-all active:scale-[0.98] flex items-center justify-center gap-2 text-base">
                    <i class="fa-solid fa-check-circle text-lg"></i>
                    <span>تأكيد خصم المخزون</span>
                </button>

                {{-- رسائل التحذير --}}
                <template x-if="productId && selectedProduct && selectedProduct.stock <= 0">
                    <div class="p-3 bg-yellow-900/20 border border-yellow-800/50 text-yellow-400 rounded-xl flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span>المنتج غير متوفر في المخزون</span>
                    </div>
                </template>
            </div>
        </form>
    </div>
</div>

<script>
function internalUseForm() {
    return {
        // المتغيرات الأساسية
        productId: '',
        selectedProduct: null,
        quantity: 0,
        unitType: 'default',
        internalNotes: '',
        totalCost: 0,

        // متغيرات البحث
        searchQuery: '',
        products: @json($products),

        get filteredProducts() {
            const q = (this.searchQuery || '').toLowerCase().trim();
            const mapped = (this.products || []).map((p) => {
                const stock = parseFloat(p.quantity) || 0;
                let stockLabel = `المخزون: ${Math.floor(stock)} قطعة`;

                if (p.product_type === 'fractional') {
                    stockLabel = `المخزون: ${stock.toFixed(2)} متر`;
                } else if (p.is_splittable) {
                    const fullKits = Math.floor(stock);
                    const partialKit = stock - fullKits;
                    const pieces = Math.round(partialKit * (parseInt(p.items_per_unit) || 1));
                    stockLabel = pieces > 0
                        ? `المخزون: ${fullKits} طقم + ${pieces} حبة`
                        : `المخزون: ${fullKits} طقم`;
                }

                return {
                    id: String(p.id),
                    name: p.name || '',
                    barcode: p.barcode || '',
                    stock,
                    productType: p.product_type || 'normal',
                    isSplittable: !!p.is_splittable,
                    itemsPerUnit: parseInt(p.items_per_unit) || 1,
                    rollLength: parseFloat(p.roll_length) || 0,
                    wastePercentage: parseFloat(p.waste_percentage) || 0,
                    stock_label: stockLabel,
                };
            });

            if (!q) return mapped;

            return mapped.filter((p) =>
                p.name.toLowerCase().includes(q) || p.barcode.toLowerCase().includes(q)
            );
        },

        // دالة التهيئة
        init() {
            // تحميل الحالة الافتراضية فقط
        },

        // مسح البحث
        clearSearch() {
            this.searchQuery = '';
        },

        // فلترة المنتجات (تحديث العرض فقط)
        filterProducts() {
            // getter filteredProducts يتولى ذلك تلقائيًا
        },

        selectProduct(id) {
            this.productId = String(id);
            this.updateProduct();
        },

        // تحديث المنتج المحدد
        updateProduct() {
            const product = this.filteredProducts.find((p) => String(p.id) === String(this.productId))
                || (this.products || []).map((p) => ({
                    id: String(p.id),
                    name: p.name || '',
                    stock: parseFloat(p.quantity) || 0,
                    barcode: p.barcode || '',
                    productType: p.product_type || 'normal',
                    isSplittable: !!p.is_splittable,
                    itemsPerUnit: parseInt(p.items_per_unit) || 1,
                    rollLength: parseFloat(p.roll_length) || 0,
                    wastePercentage: parseFloat(p.waste_percentage) || 0,
                })).find((p) => String(p.id) === String(this.productId));

            if (this.productId && product) {
                this.selectedProduct = product;

                // تعيين نوع الوحدة الافتراضي
                if (this.selectedProduct.productType === 'fractional') {
                    this.unitType = 'roll';
                } else if (this.selectedProduct.isSplittable) {
                    this.unitType = 'kit';
                } else {
                    this.unitType = 'default';
                }

                this.quantity = 0;
            } else {
                this.selectedProduct = null;
                this.unitType = 'default';
                this.quantity = 0;
            }
        },

        // باقي الدوال كما هي من الكود السابق...
        getDetailedStockDisplay() {
            if (!this.selectedProduct) return '';

            if (this.selectedProduct.productType === 'fractional') {
                let meters = this.selectedProduct.stock;
                let rolls = meters / this.selectedProduct.rollLength;
                let fullRolls = Math.floor(rolls);
                let remainingMeters = meters - (fullRolls * this.selectedProduct.rollLength);

                return `
                    <div>${meters.toFixed(2)} متر</div>
                    <div class="text-xs text-gray-400">( ${fullRolls} رول كامل + ${remainingMeters.toFixed(2)} متر )</div>
                `;
            }
            else if (this.selectedProduct.isSplittable) {
                let fullKits = Math.floor(this.selectedProduct.stock);
                let remainingPart = this.selectedProduct.stock - fullKits;
                let remainingPieces = Math.round(remainingPart * this.selectedProduct.itemsPerUnit * 100) / 100;

                let display = `<div>${fullKits} طقم`;
                if (remainingPieces > 0) {
                    display += ` <span class="text-amber-400">+ ${remainingPieces} حبة</span>`;
                }
                display += `</div>`;
                display += `<div class="text-xs text-gray-400">(إجمالي: ${(fullKits + remainingPart).toFixed(3)} طقم)</div>`;

                return display;
            }
            else {
                return `<div>${Math.floor(this.selectedProduct.stock)} قطعة</div>`;
            }
        },

        setMeterQuantity(value) {
            this.quantity = value;
            this.validateMeterQuantity();
        },

        setRollQuantity(value) {
            this.quantity = value;
            this.validateRollQuantity();
        },

        setKitQuantity(value) {
            this.quantity = value;
            this.validateKitQuantity();
        },

        setPieceQuantity(value) {
            this.quantity = value;
            this.validatePieceQuantity();
        },

        setNormalQuantity(value) {
            this.quantity = value;
            this.validateNormalQuantity();
        },

        validateMeterQuantity() {
            let value = parseFloat(this.quantity);
            if (isNaN(value) || value < 0.01) value = 0.01;
            if (value > this.getMaxValue()) value = this.getMaxValue();
            this.quantity = Math.round(value * 100) / 100;
        },

        validateRollQuantity() {
            let value = parseFloat(this.quantity);
            if (isNaN(value) || value < 0.001) value = 0.001;
            if (value > this.getMaxValue()) value = this.getMaxValue();
            this.quantity = Math.round(value * 1000) / 1000;
        },

        validateKitQuantity() {
            let value = Math.floor(parseFloat(this.quantity));
            if (isNaN(value) || value < 1) value = 1;
            if (value > this.getMaxValue()) value = Math.floor(this.getMaxValue());
            this.quantity = value;
        },

        validatePieceQuantity() {
            let value = Math.floor(parseFloat(this.quantity));
            if (isNaN(value) || value < 1) value = 1;
            if (value > this.getMaxValue()) value = Math.floor(this.getMaxValue());
            this.quantity = value;
        },

        validateNormalQuantity() {
            let value = Math.floor(parseFloat(this.quantity));
            if (isNaN(value) || value < 1) value = 1;
            let maxValue = Math.floor(this.selectedProduct.stock);
            if (value > maxValue) value = maxValue;
            this.quantity = value;
        },

        getDeductionDetails() {
            if (!this.selectedProduct || this.quantity <= 0) return '';

            if (this.unitType === 'meters') {
                return `${this.quantity.toFixed(2)} متر من إجمالي ${this.selectedProduct.stock.toFixed(2)} متر`;
            }
            else if (this.unitType === 'roll') {
                let meters = this.quantity * this.selectedProduct.rollLength;
                return `${this.quantity.toFixed(3)} رول (ما يعادل ${meters.toFixed(2)} متر)`;
            }
            else if (this.unitType === 'piece') {
                let kits = this.quantity / this.selectedProduct.itemsPerUnit;
                return `${this.quantity} حبة (ما يعادل ${kits.toFixed(3)} طقم)`;
            }
            else if (this.unitType === 'kit') {
                return `${this.quantity} طقم`;
            }
            else if (!this.selectedProduct.isSplittable && this.selectedProduct.productType !== 'fractional') {
                return `${this.quantity} قطعة`;
            }
            return '';
        },

        getMaxValue() {
            if (!this.selectedProduct || !this.productId) return 0;

            if (this.unitType === 'meters') {
                return this.selectedProduct.stock;
            } else if (this.unitType === 'roll') {
                return this.selectedProduct.stock / this.selectedProduct.rollLength;
            } else if (this.unitType === 'piece') {
                return Math.floor(this.selectedProduct.stock * this.selectedProduct.itemsPerUnit);
            } else if (this.unitType === 'kit') {
                return Math.floor(this.selectedProduct.stock);
            } else if (!this.selectedProduct.isSplittable && this.selectedProduct.productType !== 'fractional') {
                return Math.floor(this.selectedProduct.stock);
            }
            return 0;
        },

        getAvailableStockDisplay() {
            if (!this.selectedProduct || !this.productId) return '0';

            if (this.unitType === 'meters') {
                return `<span class="text-white">${this.selectedProduct.stock.toFixed(2)} م</span>`;
            } else if (this.unitType === 'roll') {
                let rolls = this.selectedProduct.stock / this.selectedProduct.rollLength;
                return `<span class="text-white">${rolls.toFixed(2)} رول</span> <span class="text-gray-400">(${this.selectedProduct.stock.toFixed(2)} م)</span>`;
            } else if (this.unitType === 'piece') {
                let pieces = Math.floor(this.selectedProduct.stock * this.selectedProduct.itemsPerUnit);
                return `<span class="text-white">${pieces} حبة</span> <span class="text-gray-400">(${this.selectedProduct.stock.toFixed(2)} طقم)</span>`;
            } else if (this.unitType === 'kit') {
                return `<span class="text-white">${Math.floor(this.selectedProduct.stock)} طقم</span> <span class="text-gray-400">(+ ${(this.selectedProduct.stock % 1).toFixed(2)} طقم كسور)</span>`;
            } else if (!this.selectedProduct.isSplittable && this.selectedProduct.productType !== 'fractional') {
                return `<span class="text-white">${Math.floor(this.selectedProduct.stock)} قطعة</span>`;
            }
            return '0';
        },

        canSubmit() {
            return this.productId &&
                   this.selectedProduct &&
                   this.selectedProduct.stock > 0 &&
                   this.quantity > 0 &&
                   this.quantity <= this.getMaxValue();
        }
    }
}
</script>

<style>
/* تحسينات للجوال */
@media (max-width: 640px) {
    input, select, textarea {
        font-size: 16px !important;
    }
}

/* تحسين لمس العناصر في الجوال */
button, input, select, textarea {
    -webkit-tap-highlight-color: transparent;
}

button:active {
    transform: scale(0.98);
}

/* إخفاء عناصر Alpine.js أثناء التحميل */
[x-cloak] {
    display: none !important;
}
</style>
@endsection