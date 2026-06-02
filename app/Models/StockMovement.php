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
        'meters',   // 🔥 جديد: القيمة بالأمتار (مثل 3.00)
    'roll_length_at_movement', // 🔥 جديد
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
