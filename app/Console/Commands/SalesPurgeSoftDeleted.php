<?php

namespace App\Console\Commands;

use App\Models\CreditSale;
use App\Models\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

        $query = Sale::onlyTrashed()
            ->where('store_id', $storeId)
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
                foreach ($sales as $sale) {
                    // هذه العمليات سبق حذفها مؤقتاً؛ هنا ننظف السجلات التابعة ثم نحذف سجل البيع نهائياً.
                    $sale->items()->delete();
                    $sale->invoice()->delete();

                    CreditSale::withTrashed()
                        ->where('store_id', $storeId)
                        ->where('description', 'like', '%#' . $sale->id . '%')
                        ->forceDelete();

                    $sale->forceDelete();
                    $deleted++;
                }
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
