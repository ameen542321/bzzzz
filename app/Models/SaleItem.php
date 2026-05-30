<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'total',
        'fraction_id',
        'is_custom',
        'custom_name',
        'custom_consumption',
        'custom_meters',
        'roll_length_at_sale',
        'unit_type',
        // الحقول القديمة التالية أبقيناها للتوافق مع البيانات أو المسارات التاريخية.
        'cost_price',
        'total_price',
        'total_cost',
    ];

    // عملية البيع
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // المنتج
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
