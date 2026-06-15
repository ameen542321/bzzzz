@extends('dashboard.app')
@section('title', 'توريد المنتجات ' . $store->name)

@section('content')
<div class="min-h-screen bg-gray-950 text-white" dir="rtl">
    <!-- إزالة الهيدر نهائياً -->

    <!-- المحتوى الرئيسي -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

        <!-- شريط العنوان والبحث -->
        <div class="mb-8">
            <div class="mb-6 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="text-xl font-bold flex items-center space-x-2 space-x-reverse text-white">
                            <i class="fas fa-cubes text-yellow-500"></i>
                            <span>قائمة المنتجات</span>
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">المخزن: {{ $store->name ?? 'غير محدد' }}</p>
                        @if(request('search'))
                            <p class="text-xs text-yellow-400 mt-1">نتائج البحث عن: "{{ request('search') }}"</p>
                        @endif
                    </div>

                    {{-- زر الرجوع --}}
                    <a href="{{ route('user.stores.index') }}"
                       class="inline-flex items-center gap-2 bg-gray-800/80 hover:bg-gray-700 text-white px-4 py-2.5 rounded-xl text-sm transition border border-gray-700 hover:border-gray-600">
                        <i class="fas fa-arrow-right"></i>
                        <span>الرجوع للمتاجر</span>
                    </a>
                </div>

                <form method="GET" action="{{ route('user.stores.supply.index', $store->id) }}"
                      class="bg-gray-900/60 border border-gray-800 rounded-2xl p-4 sm:p-5 backdrop-blur-sm">
                    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.4fr)_220px_160px] gap-3 items-end">
                        <div>
                            <label for="productSearch" class="block text-xs text-gray-400 mb-2">بحث باسم المنتج</label>
                            <div class="relative">
                                <input type="text"
                                       id="productSearch"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="ابحث عن منتج..."
                                       class="w-full bg-gray-800/70 border border-gray-700 rounded-xl py-3 px-4 pr-10 text-sm placeholder-gray-500 focus:outline-none focus:border-yellow-500/50 focus:ring-1 focus:ring-yellow-500/30 transition-all">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500"></i>
                            </div>
                        </div>

                        <div>
                            <label for="typeFilter" class="block text-xs text-gray-400 mb-2">نوع المنتج</label>
                            <select name="type" id="typeFilter" class="w-full bg-gray-800/70 border border-gray-700 rounded-xl py-3 px-4 text-sm focus:outline-none focus:border-yellow-500/50 focus:ring-1 focus:ring-yellow-500/30">
                                <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>جميع الأنواع</option>
                                <option value="fractional" {{ request('type') == 'fractional' ? 'selected' : '' }}>منتجات الرول</option>
                                <option value="splittable" {{ request('type') == 'splittable' ? 'selected' : '' }}>الطقم</option>
                                <option value="normal" {{ request('type') == 'normal' ? 'selected' : '' }}>العادية</option>
                            </select>
                        </div>

                        <button type="submit" class="h-[46px] bg-yellow-600 hover:bg-yellow-500 text-white px-6 rounded-xl text-sm font-bold transition inline-flex items-center justify-center gap-2">
                            <i class="fas fa-sliders-h"></i>
                            <span>تطبيق الفلترة</span>
                        </button>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2 text-xs">
                        <span id="resultsCount" class="text-gray-400">عدد النتائج: {{ $products->count() }}</span>
                        @if(request('search') || request('type', 'all') !== 'all')
                            <a href="{{ route('user.stores.supply.index', $store->id) }}" class="text-yellow-400 hover:text-yellow-300 transition">مسح الفلاتر</a>
                        @endif
                    </div>
                </form>
            </div>

            @if($products->isEmpty())
                <div class="text-center py-16 bg-gray-900 border border-gray-800 rounded-2xl">
                    <i class="fas fa-box-open text-5xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-bold text-gray-400 mb-2">لا توجد منتجات</h3>
                    <p class="text-gray-500">ابدأ بإضافة منتجات جديدة إلى المخزن</p>
                </div>
            @else
                <div id="productsContainer" class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-right text-sm">
                            <thead class="bg-gray-800 text-gray-300">
                                <tr>
                                    <th class="px-4 py-3">المنتج</th>
                                    <th class="px-4 py-3">النوع</th>
                                    <th class="px-4 py-3">الكمية الحالية</th>
                                    <th class="px-4 py-3">تكلفة الوحدة</th>
                                    <th class="px-4 py-3">ملاحظات آخر توريد</th>
                                    <th class="px-4 py-3 text-left">الإجراء</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach($products as $supplyProduct)
                                    @php
                                        $isFrac = ($supplyProduct->product_type === 'fractional');
                                        $isSplit = ($supplyProduct->is_splittable == 1);
                                        $typeLabel = $isFrac ? 'رول' : ($isSplit ? 'طقم' : 'عادي');
                                        $quantity = (float) ($supplyProduct->quantity ?? 0);
                                        $unitCost = (float) ($supplyProduct->cost_price ?? 0);
                                        $lastSupplyNote = trim((string) ($supplyProduct->last_supply_note ?? ''));
                                    @endphp
                                    <tr class="product-card hover:bg-gray-800/60 transition"
                                        data-product-id="{{ $supplyProduct->id }}"
                                        data-type="{{ $isFrac ? 'fractional' : ($isSplit ? 'splittable' : 'normal') }}"
                                        data-name="{{ strtolower($supplyProduct->name) }}">
                                        <td class="px-4 py-3 text-white font-semibold">{{ $supplyProduct->name }}</td>
                                        <td class="px-4 py-3 text-gray-300">{{ $typeLabel }}</td>
                                        <td class="px-4 py-3 text-gray-200 js-product-quantity">{{ number_format($quantity, 2) }}</td>
                                        <td class="px-4 py-3 text-green-400 font-bold js-product-cost">{{ number_format($unitCost, 2) }} ر.س</td>
                                        <td class="px-4 py-3 text-gray-400 js-product-note">{{ $lastSupplyNote !== '' ? $lastSupplyNote : '—' }}</td>
                                        <td class="px-4 py-3 text-left">
                                            <button onclick="openSupply('{{ $supplyProduct->id }}', '{{ addslashes($supplyProduct->name) }}')"
                                                    class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-xs font-bold">
                                                توريد
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="md:hidden divide-y divide-gray-800">
                        @foreach($products as $supplyProduct)
                            @php
                                $isFrac = ($supplyProduct->product_type === 'fractional');
                                $isSplit = ($supplyProduct->is_splittable == 1);
                                $typeLabel = $isFrac ? 'رول' : ($isSplit ? 'طقم' : 'عادي');
                                $quantity = (float) ($supplyProduct->quantity ?? 0);
                                $unitCost = (float) ($supplyProduct->cost_price ?? 0);
                            @endphp
                            <div class="product-card p-4"
                                 data-product-id="{{ $supplyProduct->id }}"
                                 data-type="{{ $isFrac ? 'fractional' : ($isSplit ? 'splittable' : 'normal') }}"
                                 data-name="{{ strtolower($supplyProduct->name) }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-bold text-white">{{ $supplyProduct->name }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $typeLabel }}</p>
                                    </div>
                                    <button onclick="openSupply('{{ $supplyProduct->id }}', '{{ addslashes($supplyProduct->name) }}')"
                                            class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-xs font-bold">
                                        توريد
                                    </button>
                                </div>
                                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-gray-800 rounded-lg p-2">
                                        <p class="text-gray-400">الكمية</p>
                                        <p class="text-white font-bold js-product-quantity">{{ number_format($quantity, 2) }}</p>
                                    </div>
                                    <div class="bg-gray-800 rounded-lg p-2">
                                        <p class="text-gray-400">التكلفة</p>
                                        <p class="text-green-400 font-bold js-product-cost">{{ number_format($unitCost, 2) }} ر.س</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modals - تم إصلاح مشكلة التمرير -->
<div id="modalContainer" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-50 p-4 text-right">
    <div class="bg-gray-900 border border-gray-700/50 rounded-2xl w-full max-w-md flex flex-col max-h-[90vh] shadow-2xl animate-modalIn">
        <!-- Header - ثابت لا يتحرك -->
        <div class="p-5 border-b border-gray-700/50 flex justify-between items-center bg-gray-900 flex-shrink-0">
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="p-2 bg-yellow-500/20 rounded-lg">
                    <i class="fas fa-truck-loading text-yellow-500"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="font-bold text-lg"></h3>
                    <p class="text-xs text-gray-400">إضافة توريد جديد</p>
                </div>
            </div>
            <button onclick="closeModal()"
                    class="p-2 hover:bg-gray-700/50 rounded-lg transition-colors text-xl"
                    aria-label="إغلاق">
                &times;
            </button>
        </div>

        <!-- Body - قابل للتمرير -->
        <div id="modalBody" class="p-6 overflow-y-auto flex-grow"></div>
    </div>
</div>

<div id="confirmModal" class="fixed inset-0 bg-black/95 hidden flex items-center justify-center z-[60] p-4 text-right">
    <div class="bg-gray-900 border-2 border-yellow-500/50 rounded-2xl w-full max-w-sm max-h-[90vh] flex flex-col shadow-2xl animate-modalIn">
        <div class="p-6 overflow-y-auto">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-yellow-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-500"></i>
                </div>
                <h3 class="font-bold text-xl mb-2 text-yellow-400">
                    تأكيد التغيير
                </h3>
                <p class="text-gray-400 text-sm">سيتم تغيير سعر التكلفة للمنتج</p>
            </div>

            <div id="detailsInfo" class="mb-6 bg-gray-900/50 p-4 rounded-xl border border-gray-700/50 space-y-3"></div>

            <div class="grid grid-cols-2 gap-3">
                <button onclick="finalSubmit('approve')"
                        class="py-3 bg-green-600 hover:bg-green-500 rounded-xl font-bold transition-all hover:shadow-lg hover:shadow-green-500/20 flex items-center justify-center space-x-2 space-x-reverse">
                    <i class="fas fa-check"></i>
                    <span>تأكيد التغيير</span>
                </button>
                <button onclick="closeModal()"
                        class="py-3 bg-gray-700 hover:bg-gray-600 rounded-xl font-bold transition-all border border-gray-600 hover:border-gray-500">
                    إلغاء
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Empty State for Search - للـ JavaScript -->
<div id="noResults" class="hidden text-center py-16">
    <i class="fas fa-search text-5xl text-gray-600 mb-4"></i>
    <h3 class="text-xl font-bold text-gray-400 mb-2">لا توجد نتائج</h3>
    <p class="text-gray-500">جرب مصطلحات بحث مختلفة</p>
</div>
<div id="supplyToast" class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-[120] bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-lg"></div>

<style>
@keyframes modalIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-modalIn {
    animation: modalIn 0.3s ease-out forwards;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #d97706;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #f59e0b;
}

/* Smooth transitions */
.product-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ========== تحسينات التجاوب للموبايل ========== */
@media (max-width: 768px) {
    .max-w-7xl.mx-auto.px-4 {
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    /* تحسين شريط البحث والتصفية */
    .flex-col.sm\\:flex-row.gap-3 {
        flex-direction: column !important;
        width: 100% !important;
        gap: 0.75rem !important;
    }

    .flex-col.sm\\:flex-row.gap-3 > * {
        width: 100% !important;
    }

    /* تحسين عرض المنتجات */
    #productsContainer {
        grid-template-columns: 1fr !important;
        gap: 0.75rem !important;
    }

    .product-card {
        padding: 1rem !important;
        margin: 0 0.25rem;
    }

    /* تحسين النص */
    .product-card .text-xl {
        font-size: 1.125rem !important;
    }

    /* تحسين المودال على الموبايل */
    #modalContainer {
        align-items: flex-end !important;
        padding: 0 !important;
    }

    #modalContainer > div {
        width: 100% !important;
        max-width: 100% !important;
        border-radius: 20px 20px 0 0 !important;
        margin: 0 !important;
        max-height: 85vh;
    }

    #modalBody {
        max-height: calc(85vh - 80px);
        overflow-y: auto;
        padding: 1rem !important;
        -webkit-overflow-scrolling: touch;
    }

    /* تحسين الأزرار داخل المودال */
    .grid-cols-2.gap-3 {
        display: flex !important;
        flex-direction: column-reverse !important;
        gap: 0.75rem !important;
    }

    .grid-cols-2.gap-3 button {
        width: 100% !important;
        height: 52px !important;
        font-size: 16px !important;
    }
}

/* إصلاحات خاصة للأجهزة التي تعمل باللمس */
@media (hover: none) and (pointer: coarse) {
    button,
    [role="button"],
    input[type="submit"],
    input[type="button"] {
        min-height: 44px !important;
        padding: 12px 16px !important;
    }

    .product-card:hover {
        transform: none !important;
    }

    .hover\\:scale-\\[1\\.02\\]:hover {
        transform: none !important;
    }

    .group:hover .group-hover\\:text-yellow-300 {
        color: inherit !important;
    }

    input[type="number"],
    input[type="text"],
    textarea {
        font-size: 16px !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        padding: 14px 12px !important;
    }
}

/* منع التكبير التلقائي في iOS */
input[type="number"],
input[type="text"],
textarea {
    font-size: 16px !important;
    line-height: 1.5 !important;
}

/* تحسين Quick Add على الموبايل */
@media (max-width: 640px) {
    .flex.space-x-2 {
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .flex.space-x-2 button {
        flex: 0 0 calc(50% - 0.25rem);
        font-size: 14px;
        padding: 0.5rem;
        min-height: 40px;
    }
}

/* تحسين الأداء على الموبايل */
@media (max-width: 768px) {
    * {
        -webkit-tap-highlight-color: transparent;
    }

    .product-card {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
    }
}
</style>

<script>
// متغيرات عامة
let currentProductId = null;
let supplyData = null;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
const baseRoute = "{{ route('user.stores.supply.index', $store->id) }}";

// تهيئة البحث والتصفية (للفلترة المحلية)
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('productSearch');
    const typeFilter = document.getElementById('typeFilter');
    const productsContainer = document.getElementById('productsContainer');
    const noResults = document.getElementById('noResults');
    const resultsCount = document.getElementById('resultsCount');

    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            filterProducts();
        }, 300));
    }

    if (typeFilter) {
        typeFilter.addEventListener('change', filterProducts);
    }

    function filterProducts() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const selectedType = typeFilter ? typeFilter.value : 'all';
        const productCards = productsContainer ? productsContainer.querySelectorAll('.product-card') : [];
        let visibleCount = 0;

        productCards.forEach(card => {
            const productName = card.getAttribute('data-name') || '';
            const productType = card.getAttribute('data-type') || '';
            const tagName = (card.tagName || '').toLowerCase();
            const visibleDisplay = tagName === 'tr' ? 'table-row' : 'block';

            const matchesSearch = searchTerm === '' || productName.includes(searchTerm);
            const matchesType = selectedType === 'all' || productType === selectedType;

            if (matchesSearch && matchesType) {
                card.style.display = visibleDisplay;
                visibleCount++;

                // Add animation
                if (tagName !== 'tr') {
                    card.style.animation = 'none';
                    setTimeout(() => {
                        card.style.animation = 'modalIn 0.3s ease-out';
                    }, 10);
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (noResults) {
            if (visibleCount === 0 && (searchTerm || selectedType !== 'all')) {
                noResults.classList.remove('hidden');
                if (productsContainer) {
                    productsContainer.classList.add('hidden');
                }
            } else {
                noResults.classList.add('hidden');
                if (productsContainer) {
                    productsContainer.classList.remove('hidden');
                }
            }
        }

        if (resultsCount) {
            resultsCount.textContent = `عدد النتائج: ${visibleCount}`;
        }
    }

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});

// دالة للحصول على العناصر بشكل آمن
function getElementSafe(id) {
    const element = document.getElementById(id);
    if (!element) {
        console.warn(`Element with id '${id}' not found`);
    }
    return element;
}

async function parseJsonSafe(response) {
    try {
        return await response.json();
    } catch (_) {
        return {};
    }
}

function extractErrorMessage(data, fallback = 'حدث خطأ أثناء الإرسال. حاول مرة أخرى.') {
    if (data?.message) {
        return data.message;
    }

    if (data?.errors && typeof data.errors === 'object') {
        const firstErrorGroup = Object.values(data.errors)[0];
        if (Array.isArray(firstErrorGroup) && firstErrorGroup.length > 0) {
            return firstErrorGroup[0];
        }
    }

    return fallback;
}

function removeExistingError(container) {
    if (!container) return;

    container.querySelectorAll('[data-supply-error]').forEach((node) => node.remove());
}

function showToast(message) {
    const toast = getElementSafe('supplyToast');
    if (!toast) return;
    toast.textContent = message;
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 2200);
}

function updateProductCard(product) {
    if (!product || !product.id) return;
    const cards = document.querySelectorAll(`.product-card[data-product-id="${product.id}"]`);
    cards.forEach((card) => {
        const quantityNode = card.querySelector('.js-product-quantity');
        const costNode = card.querySelector('.js-product-cost');
        const noteNode = card.querySelector('.js-product-note');

        if (quantityNode) quantityNode.textContent = Number(product.quantity || 0).toFixed(2);
        if (costNode) costNode.textContent = `${Number(product.cost_price || 0).toFixed(2)} ر.س`;
        if (noteNode) noteNode.textContent = product.last_supply_note || '—';
    });
}

function buildErrorAlert(message) {
    const wrapper = document.createElement('div');
    wrapper.setAttribute('data-supply-error', 'true');
    wrapper.className = 'mt-4 p-4 bg-red-500/10 border border-red-500/30 rounded-lg';
    wrapper.innerHTML = `
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start space-x-2 space-x-reverse text-red-300">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    <p class="font-bold text-red-400">تعذر إكمال العملية</p>
                    <p class="text-sm leading-6">${message || 'حدث خطأ أثناء الإرسال. حاول مرة أخرى.'}</p>
                </div>
            </div>
            <button type="button" class="text-red-300 hover:text-white transition-colors" aria-label="إخفاء الخطأ">&times;</button>
        </div>
    `;

    const closeBtn = wrapper.querySelector('button');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => wrapper.remove());
    }

    return wrapper;
}

function showPersistentError(container, message, position = 'prepend', anchor = null) {
    if (!container) return;

    removeExistingError(container);

    const errorNode = buildErrorAlert(message);

    if (position === 'after' && anchor) {
        anchor.after(errorNode);
        return;
    }

    if (position === 'append') {
        container.appendChild(errorNode);
        return;
    }

    container.prepend(errorNode);
}

function showLoader() {
    // تم إلغاء طبقة التحميل العامة لتخفيف الواجهة.
}

function hideLoader() {
    // تم إلغاء طبقة التحميل العامة لتخفيف الواجهة.
}

// دالة التعامل مع مفتاح Escape
function handleEscapeKey(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
}

// دالة فتح نافذة التوريد
function openSupply(productId, productName) {
    const modalTitle = getElementSafe('modalTitle');
    const modalBody = getElementSafe('modalBody');
    const modalContainer = getElementSafe('modalContainer');

    if (!modalTitle || !modalBody || !modalContainer) return;

    currentProductId = productId;
    modalTitle.innerText = productName;
    modalBody.innerHTML = `
        <div class="text-center py-10">
            <div class="w-16 h-16 border-4 border-gray-700 border-t-yellow-500 rounded-full animate-spin mx-auto mb-6"></div>
            <p class="text-gray-400">جاري تحميل نموذج التوريد...</p>
        </div>
    `;
    modalContainer.classList.remove('hidden');

    // إضافة مستمع لمفتاح Escape
    document.addEventListener('keydown', handleEscapeKey);
    document.body.style.overflow = 'hidden';

    showLoader();

    // تحميل محتوى المودال
    fetch(`${baseRoute}/product/${productId}/modal`, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
        .then(data => {
            hideLoader();
            if (data.success && data.html) {
                modalBody.innerHTML = data.html;
                initSupplyModalUI();

                // Add animation to form elements
                setTimeout(() => {
                const formElements = modalBody.querySelectorAll('input, select, button');
                formElements.forEach((el, index) => {
                    el.style.animationDelay = `${index * 50}ms`;
                    el.classList.add('animate-modalIn');
                });
            }, 100);
        } else {
            modalBody.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exclamation-circle text-2xl text-red-500"></i>
                    </div>
                    <h4 class="text-red-400 font-bold mb-2">حدث خطأ</h4>
                    <p class="text-gray-400">تعذر تحميل نموذج التوريد</p>
                </div>
            `;
        }
    })
    .catch(error => {
        hideLoader();
        console.error('Error loading modal:', error);
        modalBody.innerHTML = `
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-wifi-slash text-2xl text-red-500"></i>
                </div>
                <h4 class="text-red-400 font-bold mb-2">خطأ في الاتصال</h4>
                <p class="text-gray-400">تحقق من اتصال الإنترنت وحاول مرة أخرى</p>
            </div>
        `;
    });
}

// دالة إغلاق جميع النوافذ المنبثقة
function closeModal() {
    const modalContainer = getElementSafe('modalContainer');
    const confirmModal = getElementSafe('confirmModal');

    if (modalContainer) {
        modalContainer.classList.add('hidden');
        modalContainer.style.animation = 'modalIn 0.3s ease-out reverse';
    }

    if (confirmModal) confirmModal.classList.add('hidden');

    // إزالة مستمع مفتاح Escape
    document.removeEventListener('keydown', handleEscapeKey);
    document.body.style.overflow = '';

    // تنظيف البيانات
    currentProductId = null;
    supplyData = null;
}

// معالجة إرسال النموذج
document.addEventListener('submit', function(e) {
    if (e.target.id === 'supplyForm') {
        e.preventDefault();

        const submitBtn = e.target.querySelector('button[type="submit"]');
        removeExistingError(getElementSafe('modalBody'));
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-1"></i> جاري الإرسال...';
            submitBtn.disabled = true;
        }

        const formData = new FormData(e.target);

        showLoader();

        fetch(`${baseRoute}/product/${currentProductId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(async response => {
            const data = await parseJsonSafe(response);
            if (!response.ok) {
                throw new Error(extractErrorMessage(data));
            }
            return data;
        })
        .then(data => {
            hideLoader();

            if (submitBtn) {
                submitBtn.innerHTML = 'إرسال التوريد';
                submitBtn.disabled = false;
            }

            if (data.needs_confirmation) {
                supplyData = data.data;
                const detailsInfo = getElementSafe('detailsInfo');
                if (detailsInfo) {
                    detailsInfo.innerHTML = `
                        <div class="flex justify-between items-center p-3 bg-gray-800/30 rounded-lg">
                            <span class="text-gray-400">السعر الحالي:</span>
                            <span class="text-xl font-bold text-white">${data.current_price} ر.س</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg">
                            <span class="text-yellow-400">السعر الجديد:</span>
                            <span class="text-xl font-bold text-yellow-400">${data.new_price} ر.س</span>
                        </div>
                        <div class="text-center text-sm text-gray-400 mt-2">
                            <i class="fas fa-info-circle ml-1"></i>
                            سيتم تحديث سعر التكلفة لجميع الكميات المستقبلية
                        </div>
                    `;
                }

                const modalContainer = getElementSafe('modalContainer');
                const confirmModal = getElementSafe('confirmModal');

                if (modalContainer) modalContainer.classList.add('hidden');
                if (confirmModal) confirmModal.classList.remove('hidden');
            } else {
                // Show success message
                updateProductCard(data.product);
                showToast('تم التوريد بنجاح');
                closeModal();
            }
        })
        .catch(error => {
            hideLoader();
            console.error('Error submitting form:', error);

            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle ml-1"></i> حاول مرة أخرى';
                submitBtn.disabled = false;
            }

            // Show error message
            const modalBody = getElementSafe('modalBody');
            if (modalBody) {
                showPersistentError(modalBody, error.message || 'حدث خطأ أثناء الإرسال. حاول مرة أخرى.');
            }
        });
    }
});

// دالة الإرسال النهائي للتأكيد
function finalSubmit(action) {
    if (!supplyData) return;

    const confirmModal = getElementSafe('confirmModal');
    removeExistingError(confirmModal);

    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('action', action);

    // إضافة جميع بيانات التوريد
    for (let key in supplyData) {
        if (supplyData.hasOwnProperty(key)) {
            formData.append(key, supplyData[key]);
        }
    }

    showLoader();

    fetch(`${baseRoute}/product/${currentProductId}/confirm`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await parseJsonSafe(response);
        if (!response.ok) {
            throw new Error(extractErrorMessage(data, 'حدث خطأ أثناء تأكيد التوريد.'));
        }
        return data;
    })
    .then((data) => {
        hideLoader();
        updateProductCard(data.product);
        showToast('تم تحديث السعر والتوريد بنجاح');
        closeModal();

    })
    .catch(error => {
        hideLoader();
        console.error('Error confirming supply:', error);

        // Show error in confirm modal
        const confirmModal = getElementSafe('confirmModal');
        if (confirmModal) {
            const detailsInfo = confirmModal.querySelector('#detailsInfo');
            showPersistentError(
                confirmModal,
                error.message || 'حدث خطأ أثناء التأكيد. حاول مرة أخرى.',
                detailsInfo ? 'after' : 'append',
                detailsInfo
            );
        }
    });
}

// دالة لتحريك تغيير الأرقام
function animateNumber(element, start, end, duration = 1000) {
    const startTime = performance.now();
    const numberFormat = new Intl.NumberFormat('ar-EG', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Easing function
        const eased = progress < 0.5
            ? 4 * progress * progress * progress
            : 1 - Math.pow(-2 * progress + 2, 3) / 2;

        const currentValue = start + (end - start) * eased;
        element.textContent = numberFormat.format(currentValue);

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

// إضافة مستمع للنقر خارج المودال للإغلاق
document.addEventListener('DOMContentLoaded', function() {
    const modalContainer = getElementSafe('modalContainer');
    const confirmModal = getElementSafe('confirmModal');

    if (modalContainer) {
        modalContainer.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    if (confirmModal) {
        confirmModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }

    // Add hover effects to all buttons
    document.querySelectorAll('button').forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
<script>
// هذه الدوال يجب أن تكون في النافذة الرئيسية (index.blade.php)
// أو يتم تحميلها بشكل منفصل

// دالة الإضافة السريعة
function quickAdd(amount) {
    const quantityInput = document.getElementById('quantityInput');
    if (!quantityInput) return;

    const currentValue = parseFloat(quantityInput.value) || 0;
    const newValue = currentValue + amount;

    quantityInput.value = newValue;

    // إطلاق حدث input لتحديث القيمة
    quantityInput.dispatchEvent(new Event('input', { bubbles: true }));

    // تأثير بصري
    quantityInput.classList.add('bg-blue-500/10', 'border-blue-500');
    setTimeout(() => {
        quantityInput.classList.remove('bg-blue-500/10', 'border-blue-500');
    }, 300);

    updateSupplyModalUI();
}

// دالة حساب القيمة الإجمالية
function updateTotalValue() {
    updateSupplyModalUI();
}

function initSupplyModalUI() {
    const quantityInput = document.getElementById('quantityInput');
    const priceInput = document.getElementById('priceInput');
    const unitTypeInput = document.getElementById('unitTypeInput');

    if (quantityInput) {
        quantityInput.addEventListener('input', updateSupplyModalUI);
    }

    if (priceInput) {
        priceInput.addEventListener('input', updateSupplyModalUI);
    }

    if (unitTypeInput) {
        unitTypeInput.addEventListener('change', updateSupplyModalUI);
    }

    updateSupplyModalUI();
}

function updateSupplyModalUI() {
    const form = document.getElementById('supplyForm');
    const quantityInput = document.getElementById('quantityInput');
    const priceInput = document.getElementById('priceInput');
    const unitTypeInput = document.getElementById('unitTypeInput');
    const calculatedValues = document.getElementById('calculatedValues');
    const totalValueElement = document.getElementById('totalValue');
    const quantityLabelText = document.getElementById('quantityLabelText');
    const quantityUnitBadge = document.getElementById('quantityUnitBadge');
    const priceLabelText = document.getElementById('priceLabelText');
    const currentPriceLabel = document.getElementById('currentPriceLabel');
    const currentPriceValue = document.getElementById('currentPriceValue');
    const helperMeaning = document.getElementById('helperMeaning');
    const priceFieldHint = document.getElementById('priceFieldHint');

    if (!form || !quantityInput || !priceInput || !calculatedValues || !totalValueElement) return;

    const productKind = form.dataset.productKind || 'normal';
    const rollLength = parseFloat(form.dataset.rollLength || '0') || 0;
    const currentRollCost = parseFloat(form.dataset.currentRollCost || '0') || 0;
    const currentMeterCost = parseFloat(form.dataset.currentMeterCost || '0') || 0;
    const itemsPerUnit = parseFloat(form.dataset.itemsPerUnit || '0') || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;
    const unitType = unitTypeInput ? unitTypeInput.value : 'unit';

    let quantityLabel = 'الكمية الموردة';
    let quantityBadge = 'حبة';
    let priceLabel = 'سعر تكلفة الحبة';
    let helperText = 'القيمة التقديرية محسوبة مباشرة من الكمية × سعر التكلفة.';
    let priceHintText = 'إن لم تغيّر السعر اترك الحقل فارغاً.';
    let normalizedQuantity = quantity;

    if (productKind === 'fractional') {
        if (unitType === 'meter') {
            quantityLabel = 'الأمتار الموردة';
            quantityBadge = 'متر';
            helperText = 'السعر المدخل هنا هو سعر المتر، وسيتم تحويله داخلياً إلى سعر الرول الكامل لحساب الربح لاحقاً.';
            priceHintText = 'عند اختيار متر: أدخل سعر تكلفة المتر. سيُحفظ سعر الرول = سعر المتر × طول الرول.';
            priceLabel = 'سعر تكلفة المتر';
        } else {
            quantityLabel = 'عدد الرولات الموردة';
            quantityBadge = 'رول';
            helperText = 'القيمة محسوبة على أساس عدد الرولات × سعر تكلفة الرول.';
            priceLabel = 'سعر تكلفة الرول';
        }
    } else if (productKind === 'splittable') {
        if (unitType === 'piece') {
            quantityLabel = 'عدد الحبات الموردة';
            quantityBadge = 'حبة';
            normalizedQuantity = itemsPerUnit > 0 ? (quantity / itemsPerUnit) : quantity;
            helperText = 'تم تحويل الحبات إلى ما يعادلها من الأطقم لحساب قيمة التوريد وفق سعر تكلفة الطقم.';
            priceHintText = 'عند التوريد بالحبة من طقم: إذا غيّرت السعر فأدخل سعر الطقم الكامل. إذا لا يوجد تغيير اترك الحقل فارغاً.';
        } else {
            quantityLabel = 'عدد الأطقم الموردة';
            quantityBadge = 'طقم';
            helperText = 'القيمة محسوبة على أساس سعر تكلفة الطقم.';
        }

        priceLabel = 'سعر تكلفة الطقم';
    }

    if (quantityLabelText) quantityLabelText.textContent = quantityLabel;
    if (quantityUnitBadge) quantityUnitBadge.textContent = quantityBadge;
    if (priceLabelText) priceLabelText.textContent = priceLabel;
    if (currentPriceLabel) {
        currentPriceLabel.textContent = priceLabel.replace('سعر ', '') + ' الحالية:';
    }
    if (currentPriceValue) {
        const currentPrice = productKind === 'fractional' && unitType === 'meter' ? currentMeterCost : currentRollCost;
        currentPriceValue.textContent = currentPrice.toFixed(2) + ' ر.س';
    }
    if (helperMeaning) helperMeaning.textContent = helperText;
    if (priceFieldHint) priceFieldHint.textContent = priceHintText;

    if (quantity > 0 && price > 0) {
        const totalValue = normalizedQuantity * price;
        totalValueElement.textContent = totalValue.toFixed(2) + ' ر.س';
        calculatedValues.classList.remove('hidden');
        totalValueElement.style.transform = 'scale(1.1)';
        setTimeout(() => {
            totalValueElement.style.transform = 'scale(1)';
        }, 200);
    } else {
        calculatedValues.classList.add('hidden');
    }
}

// تهيئة عند تحميل النموذج
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initSupplyModalUI, 100);
});

// إضافة مستمع للنموذج بعد تحميله
setTimeout(function() {
    const form = document.getElementById('supplyForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>جاري الإرسال...</span>
                `;
            }
        });
    }
}, 500);
</script>
@endsection
