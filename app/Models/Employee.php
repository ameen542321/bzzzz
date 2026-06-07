<?php

namespace App\Models;

use App\Models\CreditSale;
use App\Traits\BelongsToStore;
use App\Traits\HasEmployeeOperations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Employee
 *
 * يمثل موظفًا داخل متجر معيّن.
 * الموظف يمكن أن يكون له:
 * - سحبيات
 * - مديونيات
 * - غيابات
 * - تقارير رواتب
 * - عمليات بيع آجل
 */
class Employee extends Model
{
    use SoftDeletes, BelongsToStore, HasEmployeeOperations;

    /**
     * الحقول القابلة للتعبئة
     */
    protected $fillable = [
        'user_id',      // مالك المتجر
        'store_id',     // المتجر التابع له الموظف
        'name',
        'phone',
        'salary',
        'status',       // active / suspended
        'suspended_at', // تاريخ بدء الإيقاف؛ يوم الإيقاف نفسه مستحق الراتب
        'notes',
        'added_by',     // من قام بإضافة الموظف (user أو accountant)
    ];

    protected $casts = [
        'suspended_at' => 'datetime',
        'salary' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */

    /**
     * علاقة الموظف مع مالك المتجر
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * علاقة الموظف مع حساب المحاسب (صلاحية دخول)
     * Employee hasOne Accountant
     */
   public function accountant()
{
    return $this->hasOne(Accountant::class)->withTrashed();
}


    /**
     * سجلات الموظف (Logs)
     */
public function logs()
{
    return $this->morphMany(EmployeeLog::class, 'person');
}



    /**
     * علاقة polymorphic غير مستخدمة هنا (person)
     * تُترك كما هي
     */
    public function person()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | سكوبات
    |--------------------------------------------------------------------------
    */
public function creditSales()
{
    return $this->hasMany(CreditSale::class, 'person_id')
                ->where('person_type', self::class);
}
public function sales()
{
    return $this->hasMany(Sale::class);
}

    /**
     * سكوب لجلب الموظفين النشطين فقط
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * سكوب لجلب الموظفين للمتجر الحالي
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canReceiveFinancialOperationOn($date): bool
    {
        $operationDate = \Illuminate\Support\Carbon::parse($date)->toDateString();

        if ($this->status === 'suspended' && $this->suspended_at
            && $operationDate >= $this->suspended_at->toDateString()) {
            return false;
        }

        return !$this->absences()
            ->whereDate('date', $operationDate)
            ->where('description', \App\Services\EmployeeEmploymentService::SUSPENSION_ABSENCE_DESCRIPTION)
            ->exists();
    }

    public function scopeForCurrentStore($query)
    {
        $storeId = auth()->check()
            ? auth()->user()->current_store_id
            : (auth('accountant')->check() ? auth('accountant')->user()->store_id : null);

        if (!$storeId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('store_id', $storeId);
    }
}
