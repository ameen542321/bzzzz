<?php

namespace App\Support;

use App\Models\Product;

class QuickSaleDeductionCalculator
{
    public static function calculate(
        Product $product,
        float $quantity,
        string $saleUnit = 'unit',
        bool $isCustom = false,
        ?float $customMeters = null,
        ?float $fractionDeductionValue = null
    ): float {
        if ($product->product_type === 'fractional') {
            if ($isCustom) {
                return $product->calculateFinalDeduction((float) ($customMeters ?? 0), 'custom');
            }

            if ($fractionDeductionValue !== null) {
                // نفس قاعدة QuickSaleController: خيارات الرول الجاهزة تُخزن كأمتار فعلية.
                // تمرير meter يمنع ضرب القيمة في roll_length داخل calculateFinalDeduction.
                return $product->calculateFinalDeduction($fractionDeductionValue, 'meter');
            }

            // نبقي هذا الفرع على السلوك الحالي للمشروع كما هو،
            // لأن quantity في بيع fractional الكامل مرتبطة حالياً بطريقة إدخال الـ POS.
            return (float) $quantity;
        }

        if ($product->is_splittable && $saleUnit === 'piece') {
            return $product->normalizeQuantityByUnit($quantity, 'piece');
        }

        return $product->normalizeQuantityByUnit($quantity, $saleUnit ?: 'default');
    }
}
