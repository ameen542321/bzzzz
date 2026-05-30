<?php

use App\Http\Controllers\AccountantController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DailySalesController;
use App\Http\Controllers\EmployeeActionsController;
use App\Http\Controllers\Employees\EmployeeController;
use App\Http\Controllers\InternalUseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\Store\EmployeeFinanceController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\Store\StoreMembersController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SupplyController;
use App\Http\Controllers\Users\UserDashboardController;
use App\Http\Controllers\UserNotificationSendController;
use App\Http\Controllers\Users\UserSettingController;
use App\Http\Controllers\Cashier\InvoiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public & Auth Routes
|--------------------------------------------------------------------------
*/
Route::post('/user/logout', [LoginController::class, 'logout'])->name('logout');
Route::post('/employees/check-email', [EmployeeController::class, 'checkEmail'])->name('user.employees.checkEmail');

/*
|--------------------------------------------------------------------------
| Unified Owner Routes (الملاك)
|--------------------------------------------------------------------------
*/
Route::middleware(['owner.unified'])->prefix('user')->name('user.')->group(function () {

    // --- 1. لوحة التحكم والتقارير العامة ---
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/send-all-reports', [AdminReportController::class, 'sendAllReports']);
    Route::post('/welcome/continue', function () {
        auth('web')->user()->update(['welcome_shown' => true]);
        return redirect()->route('user.dashboard');
    })->name('welcome.continue');
    // ========== ✅ مسارات الاشتراكات (Subscriptions) ==========
    Route::prefix('subscription')->name('subscription.')->group(function () {
        // صفحة انتهاء الاشتراك
        Route::get('/expired', [SubscriptionController::class, 'expired'])->name('expired');

        // صفحة تجديد الاشتراك (اختيار الخطة)
        Route::get('/renew', [SubscriptionController::class, 'renew'])->name('renew');

        // معالجة طلب التجديد
        Route::post('/renew/process', [SubscriptionController::class, 'processRenew'])->name('processRenew');

        // سجل الاشتراكات السابقة
        Route::get('/history', [SubscriptionController::class, 'history'])->name('history');

        // إلغاء اشتراك (soft delete)
        Route::delete('/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');

        // (اختياري) عرض فاتورة الاشتراك
        Route::get('/{id}/invoice', [SubscriptionController::class, 'invoice'])->name('invoice');

        // (اختياري) تحميل فاتورة PDF
        Route::get('/{id}/download-pdf', [SubscriptionController::class, 'downloadPdf'])->name('download-pdf');
});
    // --- 2. إدارة المتاجر (Stores) ---
    Route::prefix('stores')->name('stores.')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/create', [StoreController::class, 'create'])->name('create');
        Route::get('/trash', [StoreController::class, 'trash'])->name('trash');
        Route::post('/store', [StoreController::class, 'store'])->name('store');
        Route::get('/sales-chart', [StoreController::class, 'salesChart'])->name('sales.chart');

        // رابط التبديل (متاح دائماً للمالك)
        Route::patch('/{store}/toggle-status', [StoreController::class, 'toggleStatus'])->name('toggle-status');

        // تطبيق الحارس على العمليات "داخل" المتجر فقط
        Route::middleware(['store.check'])->group(function () {
            Route::get('/{store}', [StoreController::class, 'show'])->name('show');
            Route::get('/{store}/edit', [StoreController::class, 'edit'])->name('edit');
            Route::get('/{store}/details', [StoreController::class, 'details'])->name('details');
            Route::put('/{store}', [StoreController::class, 'update'])->name('update');
            Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/restore', [StoreController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force-delete', [StoreController::class, 'forceDelete'])->name('forceDelete');
            Route::get('/{store}/catalog', [StoreController::class, 'catalog'])->name('catalog');
            Route::get('/{store}/reports', [StoreController::class, 'reportsIndex'])->name('reports.index');
            Route::get('/{store}/reports/search', [StoreController::class, 'reportsComprehensiveSearch'])->name('reports.search');
            Route::get('/{store}/reports/last-10-days', [StoreController::class, 'reportsLastTenDays'])->name('reports.last-ten-days');
            Route::get('/{store}/reports/monthly', [StoreController::class, 'reportsMonthly'])->name('reports.monthly');
            Route::get('/{store}/reports/monthly/pdf', [StoreController::class, 'reportsMonthlyPdf'])->name('reports.monthly.pdf');

            // ========== ✅ مبيعات اليوم ==========
            Route::get('/{store}/daily-sales', [DailySalesController::class, 'index'])->name('daily');
            // هذا المسار مخصص لتعديل تاريخ الشفت المغلق من شاشة مبيعات اليوم ويطابق @method('PUT') في الـ Blade.
            Route::put('/{store}/daily-sales/shift-date', [DailySalesController::class, 'updateShiftDate'])->name('daily.shift-date');
            Route::put('/{store}/daily-sales/{sale}', [DailySalesController::class, 'update'])->name('daily.update');
            Route::delete('/{store}/daily-sales/{sale}', [DailySalesController::class, 'destroy'])->name('daily.destroy');

            // ========== ✅ فواتير المتجر (للمالك) ==========
            Route::prefix('/{store}/invoices')->name('invoices.')->group(function () {
                Route::get('/', [InvoiceController::class, 'ownerIndex'])->name('index');
                Route::get('/create', [InvoiceController::class, 'ownerCreate'])->name('create');
                Route::post('/store', [InvoiceController::class, 'ownerStore'])->name('store');
                Route::get('/{invoice}', [InvoiceController::class, 'ownerShow'])->name('show');
                Route::get('/{invoice}/edit', [InvoiceController::class, 'ownerEdit'])->name('edit');
                Route::get('/{invoice}/print', [InvoiceController::class, 'ownerPrint'])->name('print');
                Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])->name('pdf');
                Route::put('/{invoice}', [InvoiceController::class, 'ownerUpdate'])->name('update');
                Route::delete('/{invoice}', [InvoiceController::class, 'ownerDestroy'])->name('destroy');
            });

            // ========== نظام التوريد ==========
            Route::prefix('{store}/supply')->name('supply.')->group(function () {
                Route::get('/', [SupplyController::class, 'index'])->name('index');
                Route::get('/product/{product}/modal', [SupplyController::class, 'showModal'])->name('modal');
                Route::post('/product/{product}', [SupplyController::class, 'store'])->name('store');
                Route::post('/product/{product}/confirm', [SupplyController::class, 'confirmSupply'])->name('confirm');
                Route::post('/processbatch', [SupplyController::class, 'processBatch'])->name('processbatch');
                Route::post('/product/{product}/update-price', [SupplyController::class, 'updatePriceOnly'])->name('update-price');
            });

            // ========== ✅ نظام الاستهلاك الداخلي (معدل) ==========
            Route::prefix('{store}/internal-use')->name('internal-use.')->group(function () {
                // عرض صفحة التقرير (HTML)
                Route::get('/report-view', [InternalUseController::class, 'reportView'])->name('report.view');

                // API لجلب بيانات التقرير (AJAX)
                Route::get('/report', [InternalUseController::class, 'report'])->name('report');

                // API لجلب تفاصيل منتج معين
                Route::get('/product-details', [InternalUseController::class, 'productDetails'])->name('product-details');

                // تصدير PDF (قيد التطوير)
                Route::get('/export-pdf', [InternalUseController::class, 'exportPDF'])->name('export-pdf');

                // إضافة استهلاك من المخزون للمالك (بدون دمج مع المصاريف)
                Route::get('/add-consumption', [InternalUseController::class, 'reportView'])->name('add-consumption');
                Route::post('/add-consumption', [InternalUseController::class, 'storeByOwner'])->name('add-consumption.store');
                Route::put('/add-consumption/{purchase}', [InternalUseController::class, 'updateOwnerPurchase'])->name('add-consumption.update');
                Route::delete('/add-consumption/{purchase}', [InternalUseController::class, 'destroyOwnerPurchase'])->name('add-consumption.destroy');
                Route::put('/accountant-consumption/{sale}', [InternalUseController::class, 'updateAccountantConsumption'])->name('accountant-consumption.update');
                Route::delete('/accountant-consumption/{sale}', [InternalUseController::class, 'destroyAccountantConsumption'])->name('accountant-consumption.destroy');
                Route::get('/add-consumption/export-pdf', [InternalUseController::class, 'exportPDF'])->name('add-consumption.export-pdf');

                // البحث عن المنتجات (للمحاسب)
                Route::get('/products/search', [InternalUseController::class, 'searchProducts'])->name('products.search');
            });

            Route::prefix('{store}')->group(function () {
                // التصنيفات
                Route::prefix('categories')->name('categories.')->group(function () {
                    Route::get('/', [CategoryController::class, 'index'])->name('index');
                    Route::get('/create', [CategoryController::class, 'create'])->name('create');
                    Route::post('/', [CategoryController::class, 'store'])->name('store');
                    Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
                    Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
                    Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
                    Route::put('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
                    Route::get('/trash', [CategoryController::class, 'trash'])->name('trash');
                    Route::put('/{category}/restore', [CategoryController::class, 'restore'])->name('restore');
                    Route::delete('/{category}/force-delete', [CategoryController::class, 'forcedelete'])->name('force-delete');
                });

                // المنتجات
                Route::prefix('products')->name('products.')->group(function () {
                    Route::get('/', [ProductController::class, 'index'])->name('index');
                    Route::get('/create', [ProductController::class, 'create'])->name('create');
                    Route::post('/', [ProductController::class, 'store'])->name('store');
                    Route::get('/export/csv', [ProductController::class, 'exportCsv'])->name('export.csv');
                    Route::post('/import/csv', [ProductController::class, 'importCsv'])->name('import.csv');
                    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
                    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
                    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
                    Route::put('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
                    Route::get('/trash', [ProductController::class, 'trash'])->name('trash');
                    Route::put('/{id}/restore', [ProductController::class, 'restore'])->name('restore');
                    Route::delete('/{id}/force-delete', [ProductController::class, 'forcedelete'])->name('force-delete');

                    // إدارة المخزون الفردي
                    Route::get('/{product}/stock', [ProductStockController::class, 'index'])->name('stock');
                    Route::post('/{product}/stock/increase', [ProductStockController::class, 'increase'])->name('stock.increase');
                    Route::post('/{product}/stock/decrease', [ProductStockController::class, 'decrease'])->name('stock.decrease');
                });

                Route::get('accountants', [StoreMembersController::class, 'accountants'])->name('accountants.index');
                Route::get('employees', [StoreMembersController::class, 'employees'])->name('employees.index');
            });
        });
    }); // نهاية Stores

    // --- 3. إدارة المحاسبين ---
    Route::prefix('accountants')->name('accountants.')->group(function () {
        Route::get('/', [AccountantController::class, 'index'])->name('index');
        Route::get('/create', [AccountantController::class, 'create'])->name('create');
        Route::post('/store', [AccountantController::class, 'store'])->middleware('plan.limit:accountant')->name('store');
        Route::get('/trash', [AccountantController::class, 'trash'])->name('trash');
        Route::get('/{id}/edit', [AccountantController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AccountantController::class, 'update'])->name('update');
        Route::get('/{id}', [AccountantController::class, 'show'])->name('show');
        Route::get('/{id}/suspend', [AccountantController::class, 'suspend'])->name('suspend');
        Route::get('/{id}/activate', [AccountantController::class, 'activate'])->name('activate');
        Route::delete('/{id}/delete', [AccountantController::class, 'delete'])->name('delete');
        Route::get('/{id}/restore', [AccountantController::class, 'restore'])->middleware('plan.limit:accountant-restore')->name('restore');
        Route::delete('/{id}/force-delete', [AccountantController::class, 'forceDelete'])->name('forceDelete');
    });

    // --- 4. إدارة الموظفين ---
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('create');
        Route::post('/', [EmployeeController::class, 'store'])->name('store');
        Route::get('/trash', [EmployeeController::class, 'trash'])->name('trash');
        Route::post('/{id}/restore', [EmployeeController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force-delete', [EmployeeController::class, 'forceDelete'])->name('forceDelete');
        Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
        Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('edit');
        Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{employee}/promote', [EmployeeController::class, 'promote'])->name('promote');
        Route::post('/{employee}/demote', [EmployeeController::class, 'demote'])->name('demote');
        Route::get('/{employee}/actions', [EmployeeActionsController::class, 'index'])->name('actions');
        Route::get('/{employee}/logs', [EmployeeActionsController::class, 'logs'])->name('logs');
        Route::get('/{employee}/export-log', [EmployeeController::class, 'exportSnappy'])->name('exportLog');
        Route::post('/{employee}/absence', [EmployeeActionsController::class, 'storeAbsence'])->name('absence.store');
        Route::post('/{employee}/debt', [EmployeeActionsController::class, 'storeDebt'])->name('debt.store');
        Route::post('/{employee}/credit-sale', [EmployeeActionsController::class, 'storeCreditSale'])->name('credit-sale.store');
        Route::get('/{employee}/credit-sale/{sale}/collect-full', [EmployeeActionsController::class, 'collectCreditSale'])->name('credit-sale.collect.full');
        Route::get('/{person}/credit-sale/{sale}/collect-partial/{amount}', [EmployeeActionsController::class, 'collectPartialCreditSale'])->name('credit-sale.collect.partial');
        Route::post('/{person}/withdrawal', [EmployeeActionsController::class, 'storeWithdrawal'])->name('withdrawal.store');
        Route::get('/{person}/withdrawal', [EmployeeActionsController::class, 'showWithdrawalForm'])->name('withdrawal.form');
        Route::get('/debts/{id}', [EmployeeActionsController::class, 'getDebts'])->name('debts.list');
        Route::get('/debt/collect/full/{debt}', [EmployeeActionsController::class, 'collectFull'])->name('debt.collect.full');
        Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeActionsController::class, 'collectPartial'])->name('debt.collect.partial');
    });

    // --- 5. القسم المالي ---
    Route::prefix('employees')->group(function () {
        Route::get('/expense', [ExpenseController::class, 'index'])->name('expense.page');
        Route::post('/expense/store', [ExpenseController::class, 'store'])->name('expense.store');
        Route::get('/expense/export-pdf', [ExpenseController::class, 'exportPdf'])->name('expense.export-pdf');
        Route::delete('/expense/{id}', [ExpenseController::class, 'destroy'])->name('expense.destroy');
        Route::get('/expense/edit/{id}', [ExpenseController::class, 'edit'])->name('expense.edit');
        Route::put('/expense/update/{id}', [ExpenseController::class, 'update'])->name('expense.update');
    });

    // --- 6. الإشعارات ---
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/send', [UserNotificationSendController::class, 'create'])->name('send');
        Route::post('/send', [UserNotificationSendController::class, 'store'])->name('send.store');
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
        Route::post('/{id}/toggle', [NotificationController::class, 'toggle'])->name('toggle');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all', [NotificationController::class, 'markAll'])->name('markAll');
        Route::post('/mark-selected', [NotificationController::class, 'markSelected'])->name('markSelected');
        Route::delete('/{id}', [NotificationController::class, 'delete'])->name('delete');
    });

    // --- 7. الإعدادات ---
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [UserSettingController::class, 'index'])->name('index');
        Route::post('/update', [UserSettingController::class, 'update'])->name('update');
        Route::post('/settings/update', [UserSettingController::class, 'update'])->name('main.update');
    });

}); // نهاية مجموعة المالك
