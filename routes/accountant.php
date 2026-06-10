<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InternalUseController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Store\ExpenseController;
use App\Http\Controllers\Cashier\InvoiceController;
use App\Http\Controllers\Cashier\QuickSaleController;
use App\Http\Controllers\Accountant\DashboardController;
use App\Http\Controllers\Store\EmployeeFinanceController;
use App\Http\Controllers\Accountant\ProductSearchController;

/*
|--------------------------------------------------------------------------
| مسارات عامة (غير محمية)
|--------------------------------------------------------------------------
*/

// رابط عرض التقرير العام
Route::get('/view-report/{filename}', function ($filename) {
    $path = storage_path('app/public/reports/' . $filename);
    if (!file_exists($path)) abort(404, 'الملف غير موجود');
    return response()->file($path);
})->name('pdf.report.view');

// صفحة الإيقاف
Route::get('/suspended', fn() => view('accountant.suspended'))
    ->name('accountant.suspended');

// تسجيل الخروج (يجب أن يكون عامًا)
Route::post('/accountant/logout', [LoginController::class, 'logout'])
    ->name('accountant.logout');


/*
|--------------------------------------------------------------------------
| مسارات المحاسب المحمية
|--------------------------------------------------------------------------
*/
Route::middleware(['accountant.unified'])->group(function () {
    Route::prefix('accountant')->name('accountant.')->group(function () {

Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::get('/invoices/create', [InvoiceController::class, 'createInvoice'])->name('invoices.invoice.create');
Route::post('/invoices/store', [InvoiceController::class, 'storeInvoice'])->name('invoices.store');
Route::get('/invoices/{invoice}', [InvoiceController::class, 'edit'])->name('invoices.show'); // للتعديل والعرض
Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

 Route::prefix('internal-use')->name('internal-use.')->group(function () {
       Route::get('/report', [InternalUseController::class, 'report'])->name('report');

        // 2. تصدير التقرير الحالي إلى PDF
        Route::get('/internal-use', [InternalUseController::class, 'create'])->name('create');
        Route::post('/internal-use', [InternalUseController::class, 'store'])->name('store');

        // 4. البحث عن المنتجات في التقرير
        Route::get('/products/search', [InternalUseController::class, 'searchProducts'])->name('products.search');

    });




 Route::get('/debts/{id}', [EmployeeFinanceController::class, 'getDebts'])
    ->name('debts.list');
         Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
         Route::get('/invoices.index', [InvoiceController::class, 'index'])
         ->name('invoices.index');
             Route::get('/invoices/{invoice}', [InvoiceController::class, 'edit'])
             ->name('invoices.show');
          Route::get('/invoices/invoice/create', [InvoiceController::class, 'createInvoice'])
          ->name('invoices.invoice.create');
        Route::post('/invoices/store', [InvoiceController::class, 'storeInvoice'])
        ->name('invoices.store');





         Route::get('/debts/{id}', [EmployeeFinanceController::class, 'getDebts'])
            ->name('debts.list');

         Route::get('/debt/collect/full/{debt}', [EmployeeFinanceController::class, 'collectFull'])
            ->name('debt.collect.full');

         Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeFinanceController::class, 'collectPartial'])
            ->name('debt.collect.partial');
         Route::get('/quick-sale', [QuickSaleController::class, 'index'])
            ->name('quick-sale.index');
         // مدخل محمي للمحاسب إلى صفحة معاينة التضليل المستقلة.
         Route::get('/quick-sale/tint-preview', function () {
             // نمرر رابط البيانات المولّد من Laravel بدل أن تخمّن صفحة HTML مسار التطبيق.
             // هذا يحافظ على المسار الصحيح حتى عند تشغيل المشروع داخل مجلد فرعي مثل /public.
             $previewUrl = asset('tint-sale-preview.html');
             $productsUrl = route('accountant.quick-sale.tint-preview-products', [], false);

             return redirect()->to($previewUrl . '?' . http_build_query([
                 'products_url' => $productsUrl,
             ]));
         })->name('quick-sale.tint-preview');
         Route::get('/quick-sale/tint-preview-products', [QuickSaleController::class, 'tintPreviewProducts'])
            ->name('quick-sale.tint-preview-products');
         Route::post('/balance/store', [DashboardController::class, 'storeBalance'])
         ->name('balance.store');

        Route::get('/quick-sale/credit-persons', [QuickSaleController::class, 'creditPersons'])
            ->name('quick-sale.credit-persons');

        // تنفيذ عملية البيع (POST)
        Route::post('/quick-sale/submit', [QuickSaleController::class, 'submit'])
            ->name('quick-sale.submit');

        // صفحة إنشاء الفاتورة
        Route::get('/quick-sale/invoice/{sale}', [InvoiceController::class, 'create'])
            ->name('quick-sale.invoice.create');

        // حفظ الفاتورة
        Route::post('/quick-sale/invoice/{sale}/store', [InvoiceController::class, 'store'])
            ->name('quick-sale.invoice.store');

        // طباعة الفاتورة
        Route::get('/quick-sale/invoice/{invoice}/print', [InvoiceController::class, 'print'])
            ->name('quick-sale.invoice.print');

    // مسار تحميل الفاتورة كـ PDF
Route::get('quick-sale/invoice/{invoice}/pdf', [InvoiceController::class, 'downloadPDF'])
     ->name('quick-sale.invoice.pdf');

    Route::get('/products/search', [App\Http\Controllers\Cashier\ProductSearchController::class, 'search'])
->name('products.search');


        Route::prefix('pos')->name('pos.')->group(function () {

            Route::get('/withdrawal', [EmployeeFinanceController::class, 'withdrawalPage'])
                ->name('withdrawal.page');
            Route::post('/withdrawal/store/{employee}',
                [EmployeeFinanceController::class, 'storeWithdrawal'])
                ->name('withdrawal.store');
            Route::get('/expense', [ExpenseController::class, 'index'])
               ->name('expense.page');
            Route::post('/expense/store', [ExpenseController::class, 'store'])
               ->name('expense.store');
            Route::get('/expense/export-pdf', [ExpenseController::class, 'exportPdf'])
               ->name('expense.export-pdf');
            Route::delete('/expense/{id}', [ExpenseController::class, 'destroy'])
               ->name('expense.destroy');
            Route::get('/credit-sale', [EmployeeFinanceController::class, 'creditSalePage'])
                ->name('credit-sale.page');
            Route::post('/credit-sale/store/{employee}',[EmployeeFinanceController::class, 'storeCreditSale'])
                ->name('credit-sale.store');
            Route::get('/collection', [EmployeeFinanceController::class, 'collectionPage'])
                ->name('collection.page');
            Route::post('/collection/store/{sale}',
                [EmployeeFinanceController::class, 'storeCollection'])
                ->name('collection.store');
            Route::get('/debt', [EmployeeFinanceController::class, 'debtPage'])
                ->name('debt.page');
             Route::post('/debt/store/{employee}', [EmployeeFinanceController::class, 'storeDebt'])
                ->name('debt.store');
                 Route::get('/searchProduct', [ProductController::class, 'indexpos'])
            ->name('searchProduct');



Route::get('/debt/collect/full/{debt}', [EmployeeFinanceController::class, 'collectFull'])
    ->name('debt.collect.full');

Route::get('/debt/collect/partial/{debt}/{amount}', [EmployeeFinanceController::class, 'collectPartial'])
    ->name('debt.collect.partial');



            Route::get('/absence', [EmployeeFinanceController::class, 'absencePage'])
                ->name('absence.page');
            Route::post('/absence/store/{employee}',
                [EmployeeFinanceController::class, 'storeAbsence'])
                ->name('absence.store');

        });

        Route::prefix('employees')->name('employees.')->group(function () {


            Route::get('/{employee}/actions', [App\Http\Controllers\EmployeeActionsController::class, 'index'])
                ->name('actions');
            Route::post('/{employee}/absence', [App\Http\Controllers\EmployeeActionsController::class, 'storeAbsence'])
                ->name('absence.store');
            Route::post('/{employee}/debt', [App\Http\Controllers\EmployeeActionsController::class, 'storeDebt'])
                ->name('debt.store');
        });

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])
            ->name('index');
            Route::get('/{id}', [NotificationController::class, 'show'])
            ->name('show');
            Route::post('/{id}/toggle', [NotificationController::class, 'toggle'])
            ->name('toggle');
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])
            ->name('read');
            Route::post('/mark-all', [NotificationController::class, 'markAll'])
            ->name('markAll');
            Route::post('/mark-selected', [NotificationController::class, 'markSelected'])
            ->name('markSelected');
            Route::delete('/{id}', [NotificationController::class, 'delete'])
            ->name('delete');
            Route::delete('/{id}', [NotificationController::class, 'remov'])
            ->name('remov');
        });
    });

    // مسارات خارج البادئة


});



// مجموعة راوتات المحاسب / الكاشير
Route::prefix('accountant')->name('accountant.')->group(function () {

    // 1. عرض قائمة الفواتير (البحث والفلترة)
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');

    // 2. إنشاء فاتورة جديدة مرتبطة بعملية بيع (Sale)

    // 3. عرض تفاصيل الفاتورة
    // ملاحظة: يمكنك استخدام نفس صفحة الـ edit للعرض أو إنشاء صفحة show منفصلة
    // Route::get('/invoices/{invoice}', [InvoiceController::class, 'edit'])->name('invoices.show');

    // 4. تعديل الفاتورة
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');

    // 5. حذف الفاتورة
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // 6. الطباعة والتحميل (PDF)

});








