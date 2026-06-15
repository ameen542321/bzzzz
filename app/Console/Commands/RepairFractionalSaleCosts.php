<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Support\ProductProfitCostCalculator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RepairFractionalSaleCosts extends Command
{
    protected $signature = 'sales:repair-fractional-costs
                            {--sale= : رقم عملية البيع المطلوب فحصها}
                            {--apply : تنفيذ التصحيح فعليًا؛ بدونه تكون معاينة فقط}';

    protected $description = 'فحص وتصحيح تكلفة أسطر الرول والربح المحفوظ لعملية بيع واحدة';

    public function handle(): int
    {
        $saleId = (int) $this->option('sale');
        if ($saleId <= 0) {
            $this->error('يجب تحديد رقم العملية، مثال: --sale=123');

            return self::INVALID;
        }

        $sale = Sale::query()
            ->with(['items.product'])
            ->find($saleId);

        if (! $sale) {
            $this->error("لم يتم العثور على عملية البيع رقم {$saleId}.");

            return self::FAILURE;
        }

        $updates = [];
        $rows = [];
        $recalculatedItemsCost = 0.0;

        foreach ($sale->items as $item) {
            $product = $item->product;
            $currentCost = (float) ($item->total_cost ?? 0);
            $correctCost = $currentCost;
            $status = 'لم يتغير';

            if ($product && $product->product_type === 'fractional') {
                $rollCost = (float) (($item->cost_price ?? 0) > 0
                    ? $item->cost_price
                    : ($product->cost_price ?? 0));
                $rollLength = (float) (($item->roll_length_at_sale ?? 0) > 0
                    ? $item->roll_length_at_sale
                    : ($product->roll_length ?? 0));
                $consumedMeters = (float) ($item->custom_consumption ?? 0);

                if ($rollLength <= 0 || $consumedMeters <= 0) {
                    $status = 'تعذر الحساب: الطول أو الاستهلاك مفقود';
                } else {
                    $correctCost = round(
                        (($rollCost / $rollLength) * $consumedMeters) + 1e-9,
                        2,
                        PHP_ROUND_HALF_UP
                    );
                    $status = abs($correctCost - $currentCost) > 0.009
                        ? 'يحتاج تصحيح'
                        : 'صحيح';

                    if ($status === 'يحتاج تصحيح') {
                        $updates[$item->id] = $correctCost;
                    }
                }
            } elseif ($currentCost <= 0 && $product) {
                // توافق آمن لسطر قديم غير كسري لا يحتوي total_cost.
                $correctCost = round(ProductProfitCostCalculator::calculateItemCost($product, [
                    'quantity' => (float) ($item->quantity ?? 0),
                    'unit_type' => (string) ($item->unit_type ?? 'unit'),
                ]) + 1e-9, 2, PHP_ROUND_HALF_UP);
                $status = 'تكلفة قديمة محسوبة للربح فقط';
            }

            $recalculatedItemsCost += $correctCost;
            $rows[] = [
                $item->id,
                $product->name ?? "منتج محذوف #{$item->product_id}",
                number_format((float) ($item->custom_consumption ?? 0), 3),
                number_format($currentCost, 2),
                number_format($correctCost, 2),
                $status,
            ];
        }

        $recalculatedProfit = round(
            ((float) $sale->products_total - $recalculatedItemsCost + (float) $sale->labor_total) + 1e-9,
            2,
            PHP_ROUND_HALF_UP
        );

        $this->table(
            ['سطر البيع', 'المنتج', 'الاستهلاك م', 'التكلفة الحالية', 'التكلفة الصحيحة', 'الحالة'],
            $rows
        );

        $this->table(
            ['البيان', 'القيمة الحالية', 'القيمة بعد التصحيح'],
            [
                ['إجمالي تكلفة المنتجات', number_format((float) (($sale->products_total + $sale->labor_total) - $sale->profit), 2), number_format($recalculatedItemsCost, 2)],
                ['ربح العملية', number_format((float) $sale->profit, 2), number_format($recalculatedProfit, 2)],
            ]
        );

        if (! $this->option('apply')) {
            $this->warn('معاينة فقط: لم يتم تعديل قاعدة البيانات. أضف --apply بعد مراجعة القيم.');

            return self::SUCCESS;
        }

        if (empty($updates) && abs((float) $sale->profit - $recalculatedProfit) <= 0.009) {
            $this->info('لا توجد قيم تحتاج إلى تعديل.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($sale, $updates, $recalculatedProfit) {
            foreach ($updates as $itemId => $correctCost) {
                DB::table('sale_items')
                    ->where('id', $itemId)
                    ->where('sale_id', $sale->id)
                    ->update([
                        'total_cost' => $correctCost,
                        'updated_at' => now(),
                    ]);
            }

            $sale->forceFill([
                'profit' => $recalculatedProfit,
                'updated_at' => now(),
            ])->save();
        });

        $this->info("تم تصحيح عملية البيع رقم {$sale->id}.");

        return self::SUCCESS;
    }
}
