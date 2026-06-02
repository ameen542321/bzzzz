<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'store_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'meters',
        'roll_length_at_movement',
        'note',
    ];

    protected $casts = [
        'quantity' => 'float',
        'meters' => 'float',
        'roll_length_at_movement' => 'float',
    ];

    protected $appends = [
        'previous_balance',
        'current_balance',
    ];

    protected static function booted(): void
    {
        static::creating(function (StockMovement $movement): void {
            if (!is_null($movement->meters) && !is_null($movement->roll_length_at_movement)) {
                return;
            }

            $product = $movement->relationLoaded('product')
                ? $movement->product
                : Product::query()->select('id', 'quantity')->find($movement->product_id);

            if (!$product) {
                return;
            }

            // إصلاح سجل الحركة من مكان واحد: عند إنشاء حركة مباشرة بعد تحديث المخزون
            // نحسب الرصيد قبل/بعد تلقائياً حتى تظهر صفحة إدارة المخزون بأرقام صحيحة.
            $afterQuantity = (float) $product->getRawOriginal('quantity');
            $movementQuantity = abs((float) $movement->quantity);

            if ($movement->type === 'increase') {
                $beforeQuantity = $afterQuantity - $movementQuantity;
            } elseif ($movement->type === 'decrease') {
                $beforeQuantity = $afterQuantity + $movementQuantity;
            } else {
                return;
            }

            $movement->meters ??= $afterQuantity;
            $movement->roll_length_at_movement ??= $beforeQuantity;
        });
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPreviousBalanceAttribute(): float
    {
        return (float) ($this->roll_length_at_movement ?? 0);
    }

    public function getCurrentBalanceAttribute(): float
    {
        return (float) ($this->meters ?? 0);
    }

    public static function recordForProduct(
        Product $product,
        string $type,
        float $quantity,
        float $before,
        float $after,
        ?int $userId = null,
        ?string $note = null
    ): self {
        return static::create([
            'store_id' => $product->store_id,
            'product_id' => $product->id,
            'user_id' => $userId,
            'type' => $type,
            'quantity' => $quantity,
            'roll_length_at_movement' => $before,
            'meters' => $after,
            'note' => $note,
        ]);
    }
}
