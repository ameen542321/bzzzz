<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToStore;

/**
 * Class Absence
 *
 * يمثل غياب موظف داخل متجر معيّن.
 * يحتوي على:
 * - تاريخ الغياب
 * - قيمة الخصم
 * - حالة الغياب
 * - الشهر المحسوب عليه
 */
class Absence extends Model
{protected $table = 'employee_absences';
    use SoftDeletes, BelongsToStore;

    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'store_id',         // المتجر التابع له الغياب
        'employee_id',      // الموظف
        'date',
        'store_id',
         'person_id',
          'person_type',            // تاريخ الغياب
        'penalty_amount',   // قيمة الخصم
        'status',           // approved / pending / rejected
        'month',            // الشهر الذي وقع فيه الغياب
        'deducted_month',   // الشهر الذي تم الخصم فيه
        'added_by',         // من سجّل الغياب (user أو accountant)
        'description',      // ملاحظات
        'created_at',       // لضبط تاريخ الإنشاء حسب التاريخ المدخل
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    /**
     * علاقة الغياب مع الموظف
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
public function person()
 {
    return $this->morphTo();
 }
    /**
     * علاقة الغياب مع المستخدم الذي سجّله
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
