<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStore;

/**
 * ClassCreditSale
 *
 * يمثل عملية بيع آجل قام بها موظف داخل متجر معيّن.
 * تحتوي على:
 * - قيمة البيع
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 * - حالة العملية
 */
class CreditSale extends Model
{
    protected $table = 'employee_credit_sales';
    use SoftDeletes, BelongsToStore;

    /**
     * الحقول القابلة للتعبئة
     */
  protected $fillable = [
    'person_id',
    'person_type',
    'store_id',
    'amount',
    'remaining_amount',
    'description',
    'date',
    'status',
    'month',
    'deducted_month',
    'added_by',
    'partial_payments',
];

public function person() { return $this->morphTo(); }
    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */
protected $casts = [
    'partial_payments' => 'array',
];

    /**
     * علاقة العملية مع الموظف
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * علاقة العملية مع المتجر (موروثة من BelongsToStore)
     * store()
     */

    /**
     * علاقة العملية مع المستخدم الذي سجّلها
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function resolveLinkedSaleId(): ?int
    {
        if (!empty($this->sale_id)) {
            return (int) $this->sale_id;
        }

        if (preg_match('/#(\d+)/', (string) $this->description, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    public function resolveLinkedSale(): ?Sale
    {
        $saleId = $this->resolveLinkedSaleId();

        if (!$saleId) {
            return null;
        }

        return Sale::query()
            ->where('id', $saleId)
            ->where('store_id', $this->store_id)
            ->first();
    }

    public function syncLinkedSaleCollectionState(): void
    {
        $sale = $this->resolveLinkedSale();

        if (!$sale) {
            return;
        }

        $basePaidAmount = max(0, (float) ($sale->cash_amount ?? 0) + (float) ($sale->card_amount ?? 0));
        $remainingAmount = max(0, (float) ($this->remaining_amount ?? 0));
        $collectedCreditAmount = max(0, (float) ($this->amount ?? 0) - $remainingAmount);

        $sale->remaining_amount = $remainingAmount;
        $sale->paid_amount = min((float) ($sale->final_total ?? 0), $basePaidAmount + $collectedCreditAmount);
        $sale->has_partial_credit = $remainingAmount > 0 && $sale->sale_type !== 'credit';
        $sale->save();
    }
}
