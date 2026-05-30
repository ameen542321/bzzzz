{{-- resources/views/user/stores/products/stock/batch-supply.blade.php --}}
@extends('layouts.app')

@section('title', 'توريد المنتجات الموشكة - ' . $store->name)

@section('content')
<div class="container-fluid py-4">
    <!-- رأس الصفحة -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="fas fa-truck-loading me-2"></i>
                                توريد المنتجات الموشكة على النفاذ
                            </h4>
                            <p class="text-muted mb-0">
                                متجر: <strong>{{ $store->name }}</strong>
                                | تم العثور على <span id="productsCount">{{ $products->count() }}</span> منتج
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('user.stores.products.index', $store->id) }}"
                               class="btn btn-outline-secondary me-2">
                                <i class="fas fa-arrow-right me-1"></i> العودة للمنتجات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- شريط التقدم (مخفي أولاً) -->
    <div class="row mb-3 d-none" id="progressContainer">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted" id="progressText">جاري المعالجة...</span>
                        <span class="text-primary fw-bold" id="progressPercentage">0%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="mt-2 text-muted small" id="currentProduct"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول المنتجات -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">المنتج</th>
                                    <th width="10%">المخزون</th>
                                    <th width="10%">الحد الأدنى</th>
                                    <th width="12%">سعر التكلفة الحالي</th>
                                    <th width="12%">كمية التوريد</th>
                                    <th width="12%">سعر الشراء الجديد</th>
                                    <th width="14%">الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $index => $product)
                                <tr data-product-id="{{ $product->id }}"
                                    data-current-price="{{ $product->cost_price }}">
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($product->image)
                                                <img src="{{ asset('storage/' . $product->image) }}"
                                                     class="rounded me-2" width="40" height="40"
                                                     style="object-fit: cover;">
                                            @else
                                                <div class="rounded bg-light text-center me-2"
                                                     style="width: 40px; height: 40px; line-height: 40px;">
                                                    <i class="fas fa-box text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $product->name }}</div>
                                                <small class="text-muted">
                                                    {{ $product->category->name ?? 'بدون قسم' }}
                                                    @if($product->product_type === 'fractional')
                                                        <span class="badge bg-info ms-1">رول</span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge
                                            @if($product->quantity <= 0) bg-danger
                                            @elseif($product->quantity <= $product->min_stock) bg-warning
                                            @else bg-success @endif">
                                            {{ number_format($product->quantity, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $product->min_stock }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-primary fw-bold">
                                            {{ number_format($product->cost_price, 2) }} ر.س
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number"
                                               step="{{ $product->product_type === 'fractional' ? '0.01' : '1' }}"
                                               min="0.01"
                                               class="form-control form-control-sm quantity-input"
                                               data-product-id="{{ $product->id }}"
                                               placeholder="أدخل الكمية">
                                        @if($product->product_type === 'fractional')
                                            <small class="text-muted">(طول الرول: {{ $product->roll_length }}م)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               class="form-control form-control-sm price-input"
                                               data-product-id="{{ $product->id }}"
                                               value="{{ number_format($product->cost_price, 2, '.', '') }}"
                                               placeholder="أدخل السعر">
                                    </td>
                                    <td class="status-cell">
                                        <span class="badge bg-secondary status-badge"
                                              data-status="pending">
                                            <i class="fas fa-clock me-1"></i> بانتظار المعالجة
                                        </span>
                                        <div class="mt-1 d-none result-message"></div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                                            <h5>لا توجد منتجات موشكة على النفاذ</h5>
                                            <p>جميع المنتجات لديها مخزون كافٍ</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- أزرار التحكم -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <div>
                            <button type="button" class="btn btn-outline-danger" onclick="clearAllInputs()">
                                <i class="fas fa-trash-alt me-1"></i> مسح الكل
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" onclick="fillWithCurrentPrices()">
                                <i class="fas fa-copy me-1"></i> تعبئة بالأسعار الحالية
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-lg btn-success"
                                    id="processBatchBtn"
                                    onclick="processBatch()"
                                    {{ $products->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-play-circle me-2"></i> بدء توريد الدفعة
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal للاختيار عند اختلاف السعر -->
<div class="modal fade" id="priceDifferenceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    تنبيه: اختلاف في سعر الشراء
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-money-bill-wave fa-3x text-warning mb-3"></i>
                    <h4 id="modalProductName"></h4>
                </div>

                <div class="alert alert-light border">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="text-muted small">السعر المدخل</div>
                            <div class="h4 text-primary fw-bold" id="modalNewPrice"></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small">السعر المسجل</div>
                            <div class="h4 text-secondary fw-bold" id="modalCurrentPrice"></div>
                        </div>
                    </div>

                    <div class="text-center border-top pt-3">
                        <div class="text-muted">الفرق</div>
                        <div class="h3" id="modalDifference"></div>
                        <div class="badge" id="modalPercentage"></div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="input-group mb-2">
                        <span class="input-group-text">الكمية</span>
                        <input type="text" class="form-control" id="modalQuantity" readonly>
                        <span class="input-group-text">
                            @if(isset($product) && $product->product_type === 'fractional')
                                متر
                            @else
                                وحدة
                            @endif
                        </span>
                    </div>
                    <div class="form-text text-muted">
                        سيتم تحديث سعر التكلفة إلى السعر الجديد عند الموافقة
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-lg btn-success px-5" onclick="approveProduct()">
                    <i class="fas fa-check-circle me-2"></i> موافقة
                </button>
                <button type="button" class="btn btn-lg btn-danger px-5" onclick="rejectProduct()">
                    <i class="fas fa-times-circle me-2"></i> رفض
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal النتائج النهائية -->
<div class="modal fade" id="resultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-check-circle me-2"></i>
                    تم اكتمال عملية التوريد
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="fas fa-clipboard-check fa-3x text-success mb-3"></i>
                    <h4>تقرير توريد الدفعة</h4>
                </div>

                <div class="row text-center mb-4">
                    <div class="col-4">
                        <div class="card border-success">
                            <div class="card-body">
                                <div class="h2 text-success" id="resultApproved">0</div>
                                <div class="text-muted">المنتجات المضافة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-warning">
                            <div class="card-body">
                                <div class="h2 text-warning" id="resultRejected">0</div>
                                <div class="text-muted">المنتجات المرفوضة</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <div class="h2 text-primary" id="resultQuantity">0</div>
                                <div class="text-muted">إجمالي الكمية</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>السعر الجديد</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="resultsTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    إغلاق
                </button>
                <button type="button" class="btn btn-primary" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf me-2"></i> تصدير PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- نافذة تحميل -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0">
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">جاري التحميل...</span>
                </div>
                <h5 class="mb-2">جاري معالجة المنتجات</h5>
                <p class="text-muted mb-0" id="loadingMessage">يرجى الانتظار...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .quantity-input:focus, .price-input:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }

    tr.disabled-row {
        opacity: 0.5;
        background-color: #f8f9fa;
    }

    .status-badge.approved {
        background-color: #198754 !important;
    }

    .status-badge.rejected {
        background-color: #dc3545 !important;
    }

    .status-badge.processing {
        background-color: #0d6efd !important;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.6; }
        100% { opacity: 1; }
    }
</style>
@endpush

@push('scripts')
<script>
    // المتغيرات العامة
    let currentProcessingIndex = 0;
    let productsToProcess = [];
    let currentProductData = null;
    let processingResults = {
        approved: 0,
        rejected: 0,
        totalQuantity: 0,
        details: []
    };
    let modalInstance = null;

    // تهيئة الصفحة
    document.addEventListener('DOMContentLoaded', function() {
        modalInstance = new bootstrap.Modal(document.getElementById('priceDifferenceModal'));

        // إضافة CSS للـ progress bar المتحرك
        const style = document.createElement('style');
        style.textContent = `
            .progress-bar-striped {
                background-image: linear-gradient(45deg,
                    rgba(255, 255, 255, 0.15) 25%,
                    transparent 25%,
                    transparent 50%,
                    rgba(255, 255, 255, 0.15) 50%,
                    rgba(255, 255, 255, 0.15) 75%,
                    transparent 75%,
                    transparent);
                background-size: 1rem 1rem;
            }

            @keyframes progress-bar-stripes {
                0% { background-position: 1rem 0; }
                100% { background-position: 0 0; }
            }
        `;
        document.head.appendChild(style);
    });

    // دالة جمع بيانات المنتجات من الجدول
    function gatherProductsData() {
        const rows = document.querySelectorAll('#productsTable tbody tr[data-product-id]');
        productsToProcess = [];

        rows.forEach(row => {
            const productId = row.dataset.productId;
            const currentPrice = parseFloat(row.dataset.currentPrice);
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');

            // التحقق من إدخال الكمية
            const quantity = parseFloat(quantityInput.value);
            if (!quantity || quantity <= 0) {
                updateProductStatus(productId, 'warning', 'يرجى إدخال كمية صحيحة');
                return;
            }

            const newPrice = parseFloat(priceInput.value);
            if (!newPrice || newPrice <= 0) {
                updateProductStatus(productId, 'warning', 'يرجى إدخال سعر صحيح');
                return;
            }

            productsToProcess.push({
                id: productId,
                currentPrice: currentPrice,
                newPrice: newPrice,
                quantity: quantity,
                row: row
            });

            updateProductStatus(productId, 'pending', 'جاهز للمعالجة');
        });

        return productsToProcess.length > 0;
    }

    // دالة تحديث حالة المنتج في الجدول
    function updateProductStatus(productId, status, message = '') {
        const row = document.querySelector(`tr[data-product-id="${productId}"]`);
        if (!row) return;

        const statusBadge = row.querySelector('.status-badge');
        const resultMessage = row.querySelector('.result-message');

        // إزالة جميع الفئات
        statusBadge.classList.remove('approved', 'rejected', 'processing', 'bg-secondary',
                                   'bg-success', 'bg-danger', 'bg-primary');

        // إضافة الفئة المناسبة
        switch(status) {
            case 'approved':
                statusBadge.classList.add('approved', 'bg-success');
                statusBadge.innerHTML = '<i class="fas fa-check-circle me-1"></i> تمت الإضافة';
                break;
            case 'rejected':
                statusBadge.classList.add('rejected', 'bg-danger');
                statusBadge.innerHTML = '<i class="fas fa-times-circle me-1"></i> مرفوض';
                break;
            case 'processing':
                statusBadge.classList.add('processing', 'bg-primary');
                statusBadge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> جاري المعالجة';
                break;
            case 'warning':
                statusBadge.classList.add('bg-warning');
                statusBadge.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> تحذير';
                break;
            default:
                statusBadge.classList.add('bg-secondary');
                statusBadge.innerHTML = '<i class="fas fa-clock me-1"></i> بانتظار المعالجة';
        }

        // تحديث الرسالة الإضافية
        if (message) {
            resultMessage.textContent = message;
            resultMessage.classList.remove('d-none');
        } else {
            resultMessage.classList.add('d-none');
        }
    }

    // دالة المعالجة الرئيسية
    async function processBatch() {
        // التحقق من وجود منتجات للمعالجة
        if (!gatherProductsData()) {
            Swal.fire({
                icon: 'warning',
                title: 'بيانات ناقصة',
                text: 'يرجى تعبئة الكمية والسعر لجميع المنتجات',
                confirmButtonText: 'حسناً'
            });
            return;
        }

        // تعطيل زر البدء
        document.getElementById('processBatchBtn').disabled = true;

        // إظهار شريط التقدم
        document.getElementById('progressContainer').classList.remove('d-none');

        // إعادة تعيين المتغيرات
        currentProcessingIndex = 0;
        processingResults = {
            approved: 0,
            rejected: 0,
            totalQuantity: 0,
            details: []
        };

        // بدء المعالجة
        await processNextProduct();
    }

    // دالة معالجة المنتج التالي
    async function processNextProduct() {
        // التحقق إذا انتهت جميع المنتجات
        if (currentProcessingIndex >= productsToProcess.length) {
            finishProcessing();
            return;
        }

        // جلب بيانات المنتج الحالي
        currentProductData = productsToProcess[currentProcessingIndex];

        // تحديث شريط التقدم
        updateProgress();

        // تحديث حالة المنتج في الجدول
        updateProductStatus(currentProductData.id, 'processing', 'جاري المعالجة...');

        // التحقق من اختلاف السعر
        const priceDifference = Math.abs(currentProductData.newPrice - currentProductData.currentPrice);
        const priceTolerance = 0.01; // تحمل 1 هللة

        if (priceDifference > priceTolerance) {
            // عرض نافذة الاختيار
            showPriceDifferenceModal(currentProductData);
        } else {
            // السعر مطابق - معالجة مباشرة
            await approveProduct(false);
        }
    }

    // دالة عرض نافذة اختلاف السعر
    function showPriceDifferenceModal(productData) {
        // تعبئة بيانات الـ modal
        document.getElementById('modalProductName').textContent =
            productData.row.querySelector('td:nth-child(2) .fw-bold').textContent;

        document.getElementById('modalNewPrice').textContent =
            productData.newPrice.toFixed(2) + ' ر.س';

        document.getElementById('modalCurrentPrice').textContent =
            productData.currentPrice.toFixed(2) + ' ر.س';

        document.getElementById('modalQuantity').value = productData.quantity;

        // حساب الفرق والنسبة
        const difference = productData.newPrice - productData.currentPrice;
        const percentage = (difference / productData.currentPrice) * 100;

        document.getElementById('modalDifference').textContent =
            (difference > 0 ? '+' : '') + difference.toFixed(2) + ' ر.س';
        document.getElementById('modalDifference').className =
            'h3 ' + (difference > 0 ? 'text-danger' : 'text-success');

        document.getElementById('modalPercentage').textContent =
            (percentage > 0 ? '+' : '') + percentage.toFixed(1) + '%';
        document.getElementById('modalPercentage').className =
            'badge ' + (percentage > 0 ? 'bg-danger' : 'bg-success');

        // إظهار الـ modal
        modalInstance.show();
    }

    // دالة الموافقة على المنتج
    async function approveProduct(showModal = true) {
        if (showModal) {
            modalInstance.hide();
        }

        // إرسال طلب للموافقة
        try {
            const response = await fetch('{{ route("user.stores.products.stock.batch-approve") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: currentProductData.id,
                    quantity: currentProductData.quantity,
                    new_price: currentProductData.newPrice
                })
            });

            const result = await response.json();

            if (result.success) {
                // تحديث الحالة
                updateProductStatus(currentProductData.id, 'approved',
                    `تم الإضافة: ${currentProductData.quantity} وحدة`);

                // تحديث سعر التكلفة في الجدول
                const priceCell = currentProductData.row.querySelector('.price-input');
                priceCell.value = currentProductData.newPrice.toFixed(2);
                currentProductData.row.dataset.currentPrice = currentProductData.newPrice;

                // تحديث النتائج
                processingResults.approved++;
                processingResults.totalQuantity += currentProductData.quantity;
                processingResults.details.push({
                    name: document.getElementById('modalProductName').textContent,
                    quantity: currentProductData.quantity,
                    newPrice: currentProductData.newPrice,
                    status: 'approved'
                });

            } else {
                throw new Error(result.message || 'حدث خطأ');
            }

        } catch (error) {
            updateProductStatus(currentProductData.id, 'warning', error.message);
        }

        // الانتقال للمنتج التالي
        currentProcessingIndex++;
        setTimeout(processNextProduct, 500);
    }

    // دالة رفض المنتج
    function rejectProduct() {
        modalInstance.hide();

        // تحديث الحالة
        updateProductStatus(currentProductData.id, 'rejected', 'تم رفض التوريد');

        // تحديث النتائج
        processingResults.rejected++;
        processingResults.details.push({
            name: document.getElementById('modalProductName').textContent,
            quantity: currentProductData.quantity,
            newPrice: currentProductData.newPrice,
            status: 'rejected'
        });

        // الانتقال للمنتج التالي
        currentProcessingIndex++;
        setTimeout(processNextProduct, 500);
    }

    // دالة تحديث شريط التقدم
    function updateProgress() {
        const percentage = ((currentProcessingIndex + 1) / productsToProcess.length) * 100;

        document.getElementById('progressBar').style.width = percentage + '%';
        document.getElementById('progressPercentage').textContent = Math.round(percentage) + '%';

        document.getElementById('progressText').textContent =
            `جاري معالجة المنتج ${currentProcessingIndex + 1} من ${productsToProcess.length}`;

        if (currentProductData) {
            const productName = currentProductData.row.querySelector('td:nth-child(2) .fw-bold').textContent;
            document.getElementById('currentProduct').textContent =
                `المنتج الحالي: ${productName}`;
        }
    }

    // دالة إنهاء المعالجة
    function finishProcessing() {
        // تحديث شريط التقدم
        document.getElementById('progressBar').style.width = '100%';
        document.getElementById('progressPercentage').textContent = '100%';
        document.getElementById('progressText').textContent = 'تم اكتمال المعالجة';

        // إعادة تفعيل زر البدء
        document.getElementById('processBatchBtn').disabled = false;

        // إخفاء شريط التقدم بعد 2 ثانية
        setTimeout(() => {
            document.getElementById('progressContainer').classList.add('d-none');
        }, 2000);

        // عرض النتائج النهائية
        showFinalResults();
    }

    // دالة عرض النتائج النهائية
    function showFinalResults() {
        // تحديث الأرقام
        document.getElementById('resultApproved').textContent = processingResults.approved;
        document.getElementById('resultRejected').textContent = processingResults.rejected;
        document.getElementById('resultQuantity').textContent = processingResults.totalQuantity;

        // تعبئة الجدول
        const tableBody = document.getElementById('resultsTableBody');
        tableBody.innerHTML = '';

        processingResults.details.forEach(detail => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${detail.name}</td>
                <td>${detail.quantity}</td>
                <td>${detail.newPrice ? detail.newPrice.toFixed(2) + ' ر.س' : '-'}</td>
                <td>
                    <span class="badge ${detail.status === 'approved' ? 'bg-success' : 'bg-danger'}">
                        ${detail.status === 'approved' ? 'مضاف' : 'مرفوض'}
                    </span>
                </td>
            `;
            tableBody.appendChild(row);
        });

        // إظهار الـ modal
        const resultsModal = new bootstrap.Modal(document.getElementById('resultsModal'));
        resultsModal.show();
    }

    // دالة مسح جميع الحقول
    function clearAllInputs() {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: 'سيتم مسح جميع الكميات والأسعار المدخلة',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'نعم، امسح الكل',
            cancelButtonText: 'إلغاء'
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelectorAll('.quantity-input, .price-input').forEach(input => {
                    input.value = '';
                });

                document.querySelectorAll('.status-badge').forEach(badge => {
                    badge.className = 'badge bg-secondary status-badge';
                    badge.innerHTML = '<i class="fas fa-clock me-1"></i> بانتظار المعالجة';
                });

                document.querySelectorAll('.result-message').forEach(msg => {
                    msg.textContent = '';
                    msg.classList.add('d-none');
                });

                Swal.fire('تم المسح', 'تم مسح جميع البيانات المدخلة', 'success');
            }
        });
    }

    // دالة تعبئة الأسعار الحالية
    function fillWithCurrentPrices() {
        document.querySelectorAll('.price-input').forEach(input => {
            const row = input.closest('tr');
            const currentPrice = parseFloat(row.dataset.currentPrice);
            input.value = currentPrice.toFixed(2);
        });

        Swal.fire('تم التعبئة', 'تم تعبئة جميع الحقول بالأسعار الحالية', 'success');
    }

    // دالة تصدير PDF (يمكن تطويرها لاحقاً)
    function exportToPDF() {
        Swal.fire({
            title: 'قريباً',
            text: 'ميزة تصدير PDF قيد التطوير',
            icon: 'info',
            confirmButtonText: 'حسناً'
        });
    }
</script>
@endpush