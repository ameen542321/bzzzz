<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesPurgeSoftDeleted extends Command
{
    protected $signature = 'sales:purge-soft-deleted
        {--store= : رقم المتجر المطلوب تنظيف عملياته المحذوفة مؤقتاً}
        {--apply : نفّذ الحذف النهائي. بدون هذا الخيار ستكون العملية معاينة فقط}';

    protected $description = 'Permanently purge soft-deleted sales for a selected store.';

    public function handle(): int
    {
        $storeId = (int) $this->option('store');
        $apply = (bool) $this->option('apply');

        if ($storeId <= 0) {
            $this->error('يجب تحديد رقم المتجر مثل: --store=1');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('sales', 'deleted_at')) {
            $this->info('جدول sales لا يحتوي deleted_at؛ لا توجد عمليات مبيعات محذوفة مؤقتاً لتنظيفها.');
            $this->table(['البند', 'القيمة'], [
                ['المتجر', $storeId],
                ['عمليات محذوفة مؤقتاً قابلة للحذف النهائي', 0],
            ]);

            return self::SUCCESS;
        }

        $query = DB::table('sales')
            ->where('store_id', $storeId)
            ->whereNotNull('deleted_at')
            ->orderBy('id');

        $count = (clone $query)->count();

        if (! $apply) {
            $this->info('معاينة فقط، لم يتم حذف أي عملية نهائياً. أضف --apply للتنفيذ.');
            $this->table(['البند', 'القيمة'], [
                ['المتجر', $storeId],
                ['عمليات محذوفة مؤقتاً قابلة للحذف النهائي', $count],
            ]);

            return self::SUCCESS;
        }

        $deleted = 0;

        DB::transaction(function () use ($query, $storeId, &$deleted) {
            $query->chunkById(100, function ($sales) use ($storeId, &$deleted) {
                $saleIds = $sales->pluck('id')->map(fn ($id) => (int) $id)->all();

                if (empty($saleIds)) {
                    return;
                }

                DB::table('sale_items')->whereIn('sale_id', $saleIds)->delete();
                DB::table('invoices')->whereIn('sale_id', $saleIds)->delete();

                foreach ($saleIds as $saleId) {
                    DB::table('employee_credit_sales')
                        ->where('store_id', $storeId)
                        ->where('description', 'like', '%#' . $saleId . '%')
                        ->delete();
                }

                $deleted += DB::table('sales')->whereIn('id', $saleIds)->delete();
            });
        });

        $this->info('تم حذف العمليات المحذوفة مؤقتاً نهائياً.');
        $this->table(['البند', 'القيمة'], [
            ['المتجر', $storeId],
            ['عمليات حذفت نهائياً', $deleted],
        ]);

        return self::SUCCESS;
    }
}
