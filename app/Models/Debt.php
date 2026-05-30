<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStore;

/**
 * Class Debt
 *
 * يمثل مديونية على موظف داخل متجر معيّن.
 * تحتوي على:
 * - قيمة المديونية
 * - نوعها (خصم، سلفة، إلخ)
 * - الشهر المحسوب عليه
 * - الشهر الذي سيتم الخصم فيه
 */
class Debt extends Model
{
    protected $table = 'employee_debts';
    use SoftDeletes, BelongsToStore;
public function person()
 {
     return $this->morphTo();
}
    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'store_id',
        'person_id',        // الموظف
        'person_type',      // المتجر الذي حدثت فيه المديونية
        'employee_id',      // الموظف
        'amount',           // قيمة المديونية
        'description',      // وصف المديونية
        'type',             // loan, deduction, etc.
        'status',           // pending / approved / rejected
        'month',            // الشهر الذي حدثت فيه المديونية
        'deducted_month',   // الشهر الذي سيتم الخصم فيه
        'added_by',         // من سجّل المديونية
        'date',             // تاريخ العملية
        'created_at',       // لضبط تاريخ إنشاء العملية حسب التاريخ المدخل
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    /**
     * علاقة المديونية مع الموظف
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * علاقة المديونية مع المستخدم الذي سجّلها
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
