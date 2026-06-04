<?php

namespace App\Console\Commands;

use App\Models\SaleItem;
use App\Support\ProductProfitCostCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SalesBackfillItemCosts extends Command
{
    protected $signature = 'sales:backfill-item-costs
        {--month= : الشهر بصيغة YYYY-MM مثل 2026-06}
        {--store= : رقم المتجر المطلوب تحديثه}
        {--apply : نفّذ التحديث فعلياً. بدون هذا الخيار ستكون العملية معاينة فقط}';

    protected $description = 'Backfill missing sale_items cost_price and total_cost values for a selected store/month.';

    public function handle(): int
    {
        $month = (string) $this->option('month');
        $storeId = (int) $this->option('store');
        $apply = (bool) $this->option('apply');

        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $this->error('استخدم الشهر بصيغة صحيحة مثل: --month=2026-06');

            return self::FAILURE;
        }

        if ($storeId <= 0) {
            $this->error('يجب تحديد رقم المتجر مثل: --store=1');

            return self::FAILURE;
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $summary = [
            'checked' => 0,
            'fillable' => 0,
            'updated' => 0,
            'skipped' => 0,
            'current_total_cost' => 0.0,
            'new_total_cost' => 0.0,
        ];

        $query = SaleItem::query()
            ->with(['sale:id,store_id,created_at,sale_type,description', 'product'])
            ->whereHas('sale', function ($saleQuery) use ($storeId) {
                $saleQuery->where('store_id', $storeId);
            })
            ->where(function ($dateQuery) use ($start, $end) {
                // المحاسب قد يسجل عملية بتاريخ عمل 31-05 أثناء يوم 01-06؛ لذلك نفحص تاريخ البيع وتاريخ سطر البيع معاً.
                $dateQuery->whereBetween('created_at', [$start, $end])
                    ->orWhereHas('sale', function ($saleQuery) use ($start, $end) {
                        $saleQuery->whereBetween('created_at', [$start, $end]);
                    });
            })
            ->where(function ($query) {
                $query->whereNull('cost_price')
                    ->orWhere('cost_price', '<=', 0)
                    ->orWhereNull('total_cost')
                    ->orWhere('total_cost', '<=', 0);
            })
            ->orderBy('id');

        $runner = function () use ($query, &$summary, $apply) {
            $query->chunkById(200, function ($items) use (&$summary, $apply) {
                foreach ($items as $item) {
                    $summary['checked']++;

                    $product = $item->product;
                    if (! $product) {
                        $summary['skipped']++;

                        continue;
                    }

                    $costPrice = (float) (($item->cost_price ?? 0) > 0 ? $item->cost_price : ($product->cost_price ?? 0));
                    if ($costPrice <= 0) {
                        $summary['skipped']++;

                        continue;
                    }

                    $stockQuantity = (float) ($item->custom_consumption ?? $item->quantity ?? 0);
                    $unitType = (string) ($item->unit_type ?? 'unit');

                    $calculatedTotalCost = ProductProfitCostCalculator::calculateItemCost([
                        'cost_price' => $costPrice,
                        'product_type' => $product->product_type,
                        'roll_length' => $product->roll_length,
                        'is_splittable' => $product->is_splittable,
                        'items_per_unit' => $product->items_per_unit,
                    ], [
                        'quantity' => (float) ($item->quantity ?? 0),
                        'custom_consumption' => $stockQuantity,
                        'unit_type' => $unitType,
                    ]);

                    if ($calculatedTotalCost <= 0) {
                        $summary['skipped']++;

                        continue;
                    }

                    $summary['fillable']++;
                    $summary['current_total_cost'] += (float) ($item->total_cost ?? 0);
                    $summary['new_total_cost'] += $calculatedTotalCost;

                    if ($apply) {
                        $item->forceFill([
                            'cost_price' => $costPrice,
                            'total_cost' => round($calculatedTotalCost, 2),
                        ])->save();

                        $summary['updated']++;
                    }
                }
            });
        };

        if ($apply) {
            DB::transaction($runner);
        } else {
            $runner();
        }

        $this->info($apply ? 'تم تنفيذ التعبئة.' : 'معاينة فقط، لم يتم تعديل قاعدة البيانات. أضف --apply للتنفيذ.');
        $this->table(['البند', 'القيمة'], [
            ['الشهر', $month],
            ['المتجر', $storeId],
            ['أسطر تمت مراجعتها', $summary['checked']],
            ['أسطر قابلة للتعبئة', $summary['fillable']],
            ['أسطر تم تحديثها', $summary['updated']],
            ['أسطر تم تجاوزها', $summary['skipped']],
            ['إجمالي التكلفة الحالي للأسطر اليتيمة', number_format($summary['current_total_cost'], 2)],
            ['إجمالي التكلفة بعد التعبئة لهذه الأسطر', number_format($summary['new_total_cost'], 2)],
            ['الفرق المتوقع', number_format($summary['new_total_cost'] - $summary['current_total_cost'], 2)],
        ]);

        return self::SUCCESS;
    }
}
