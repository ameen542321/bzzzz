<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Traits\BelongsToStore;
use App\Models\ProductFraction;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes, BelongsToStore;

    protected $fillable = [
        'store_id',
        'user_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'product_type',
    'waste_percentage',
        'quantity',
        'barcode',
        'status',
        'image',
        'piece_price',
        'min_stock',
        'roll_length',
        'is_splittable',
         'items_per_unit',
        'quick_sale_default_unit',
    ];

    protected static function boot()
    {
        parent::boot();

        // توليد الرابط المختصر مع دعم الكلمات العربية
        static::creating(function ($product) {
            $product->slug = $product->slug ?: Str::slug($product->name, '-', null);
        });

        static::updating(function ($product) {
            /*
             * يجب الحفاظ على الـ slug الحالي عند تعديل السعر أو المخزون أو أي حقل
             * لا يغيّر اسم المنتج؛ لأن بعض المنتجات تستخدم slug مرتبطاً بالمتجر
             * مثل product-name-s1، واستبداله بـ product-name قد يصطدم بمنتج قديم
             * في متجر آخر ويُظهر للمستخدم رسالة تكرار اسم غير صحيحة.
             *
             * إذا تغيّر الاسم فعلياً ولم يرسل المستدعي slug مخصصاً، نعيد توليده
             * تلقائياً للمحافظة على السلوك المعتاد للنموذج.
             */
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = Str::slug($product->name, '-', null);
            }
        });

        // 🔥 إشعار انخفاض المخزون عند التحديث
       static::updated(function ($product) {
    if ($product->wasChanged('quantity')) {
        $limit = $product->min_stock ?? 1;
        $currentStockInUnits = $product->quantity; // القيمة الافتراضية

        if ($product->product_type === 'fractional' && $product->roll_length > 0) {
            $currentStockInUnits = $product->quantity / $product->roll_length;
        } elseif ($product->is_splittable && $product->items_per_unit > 1) {
            $currentStockInUnits = $product->quantity;
        }

        // استخدم $currentStockInUnits بدلاً من إعادة تعيين $product
        if ($currentStockInUnits <= $limit) {
            NotificationService::sendTemplate('low_stock', [
                'sender_type' => 'system',
                'target_type' => 'store',
                'target_ids'  => [$product->store_id],
                'product_name' => $product->name,
                'quantity' => round($currentStockInUnits, 2),
            ]);
        }
    }
});
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (الاستعلامات المخصصة)
    |--------------------------------------------------------------------------
    */
    public function deductStock($amount)
{
    $this->decrement('quantity', $amount);
}
 public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
    /**
     * جلب المنتجات التي وصلت أو نزلت عن حد المخزون الأدنى
     */
    public function scopeLowStock($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($fractional) {
                $fractional->where('product_type', 'fractional')
                    ->where('roll_length', '>', 0)
                    ->whereRaw('(quantity / roll_length) <= min_stock');
            })->orWhere(function ($normal) {
                $normal->where(function ($inner) {
                    $inner->where('product_type', '!=', 'fractional')
                        ->orWhere('roll_length', '<=', 0)
                        ->orWhereNull('roll_length');
                })->whereRaw('quantity <= min_stock');
            });
        });
    }
/**
 * حساب الكمية النهائية المطلوب خصمها شاملة الهالك
 * @param float $deductionValue قيمة الخصم الأساسية (مثلاً 1.50)
 * @return float
 */
/**
 * حساب الخصم النهائي
 * @param float $value القيمة (إما نسبة مثل 0.25 أو أمتار مثل 3.5)
 * @param bool $isMeters هل القيمة المرسلة هي أمتار؟
 */
public function calculateFinalDeduction($value, $unitType = 'default')
{
    $deduction = 0;

    if ($this->product_type === 'fractional') {
        // في النظام الحالي المخزون الأساسي للمنتج الكسري محفوظ بالمتر.
        // لذلك:
        // - القص المخصص/الأمتار تبقى كما هي بالمتر.
        // - خيارات التجزئة الجاهزة المخزنة كنسبة من الرول تُحوَّل إلى متر.
        if ($unitType === 'meters' || $unitType === 'meter' || $unitType === 'custom') {
            $deduction = (float) $value;
        } else {
            if ($this->roll_length > 0) {
                $deduction = (float) $value * (float) $this->roll_length;
            } else {
                $deduction = (float) $value;
            }
        }
    }
    elseif ($this->is_splittable) {
        // حالة الأطقم (طيس/لمبات)
        if ($unitType === 'piece') {
            // تحويل الحبة إلى كسر من الطقم
            $deduction = (float) ($value / ($this->items_per_unit ?: 1));
        } else {
            $deduction = (float) $value; // طقم كامل
        }
    }
    else {
        // المنتج العادي
        $deduction = (float) $value;
    }

    // إضافة نسبة الهالك (Waste)
    if ($this->waste_percentage > 0) {
        $wasteAmount = $deduction * ($this->waste_percentage / 100);
        $deduction += $wasteAmount;
    }

    return $deduction;
}

/**
 * تحويل الكمية المدخلة من وحدة العرض/الإدخال إلى وحدة المخزون الأساسية.
 *
 * وحدة المخزون الأساسية في النظام هي:
 * - المتر للمنتجات الكَسرية (fractional)
 * - الطقم للمنتجات القابلة للتجزئة (splittable)
 * - نفس الكمية للمنتجات العادية
 */
public function normalizeQuantityByUnit($quantity, $unitType = 'unit'): float
{
    $q = abs((float) $quantity);
    if ($q <= 0) {
        return 0.0;
    }

    // بعض المسارات التاريخية تمرر الكمية بعد تحويلها مسبقاً إلى وحدة المخزون الأساسية،
    // لذلك نحافظ على هذا النمط عند تمرير default/normalized بدون أي إعادة تحويل.
    if (in_array($unitType, ['default', 'normalized'], true)) {
        return $q;
    }

    if ($this->product_type === 'fractional') {
        if (in_array($unitType, ['unit', 'roll'], true) && (float) $this->roll_length > 0) {
            return $q * (float) $this->roll_length;
        }

        if (in_array($unitType, ['meter', 'meters', 'custom'], true)) {
            return $q;
        }
    }

    if ($this->is_splittable && (int) $this->items_per_unit > 1) {
        if ($unitType === 'piece') {
            return $q / (float) $this->items_per_unit;
        }

        if (in_array($unitType, ['kit', 'unit', 'default'], true)) {
            return $q;
        }
    }

    return $q;
}
// 4. دالة لجلب سعر الحبة التلقائي (إذا لم يتم إدخاله)
public function getPiecePriceAttribute($value)
{
    // إذا كان هناك سعر يدوي للحبة استخدمه، وإلا اقسم سعر الطقم على عدد الحبات
    if ($value > 0) return $value;

    if ($this->is_splittable && $this->items_per_unit > 0) {
        return $this->price / $this->items_per_unit;
    }

    return $this->price;
}
    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */
public function fractions()
{
    // المنتج الواحد له عدة خيارات تجزئة (مثل: سيارة كبيرة، صغيرة، إلخ)
    return $this->hasMany(ProductFraction::class);
}
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /*
    |--------------------------------------------------------------------------
    | دوال إدارة المخزون (تستخدم في الكنترولر)
    |--------------------------------------------------------------------------
    */

// نقوم بإضافة براميتر ثالث لتحديد هل الإضافة بالوحدة أم بالحبة
public function increaseStock($quantity, ?string $note = null, ?int $userId = null, $unitType = 'default'): void
{
    $actualAmount = $this->normalizeQuantityByUnit($quantity, $unitType);
    if ($actualAmount <= 0) return;

    $before = (float) $this->getRawOriginal('quantity');
    $this->increment('quantity', $actualAmount);
    $after = $before + $actualAmount;

    StockMovement::recordForProduct($this, 'increase', $actualAmount, $before, $after, $userId, $note);
}

public function decreaseStock($quantity, ?string $note = null, ?int $userId = null, $unitType = 'default'): void
{
    $actualAmount = $this->normalizeQuantityByUnit($quantity, $unitType);
    if ($actualAmount <= 0) return;

    $before = (float) $this->getRawOriginal('quantity');
    $this->decrement('quantity', $actualAmount);
    $after = $before - $actualAmount;

    StockMovement::recordForProduct($this, 'decrease', $actualAmount, $before, $after, $userId, $note);
}
    /**
 * تحويل الأمتار إلى نسبة خصم من الرول
 * @param float $meters الأمتار المستهلكة
 * @return float النسبة المئوية (مثلاً 0.10)
 */
public function convertMetersToDeduction($meters)
{
    if ($this->roll_length <= 0) return 0;

    // المعادلة: الأمتار المطلوبة ÷ طول الرول الكلي
    // مثال: 3 متر ÷ 30 متر = 0.10 (أي 10% من الرول)
    return (float) ($meters / $this->roll_length);
}
}
