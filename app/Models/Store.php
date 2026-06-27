<?php

namespace App\Models;

use App\Models\Sale;
use App\Models\User;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Category;
use App\Models\Employee;
use App\Models\SaleItem;
use App\Models\Accountant;
use App\Models\Withdrawal;
use App\Models\EmployeeDebt;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'phone',
        'address',
        'logo',
        'slug',
        'status',
        'suspension_reason',
        'tax_number',
        'commercial_registration',
        'bank_accounts',
        'invoice_terms',
        'number_of_shifts',
        'shift_1_start',
        'shift_2_start',
        'shift_3_start',
        'force_shift_closure'
    ];

    /**
     * تحويل الحقول إلى أنواع بيانات محددة تلقائياً
     */
    protected $casts = [
        'bank_accounts' => 'array', // ليتعامل مع الحسابات كـ Array بدلاً من نص
        // توضيح: هذه الحقول تخص نظام الشفتات حتى تصل للواجهة والكنترولرات بأنواع ثابتة.
        'force_shift_closure' => 'boolean',
        'number_of_shifts' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | العلاقات (Relationships)
    |--------------------------------------------------------------------------
    */

    public function user() { return $this->belongsTo(User::class); }
    public function accountants() { return $this->hasMany(Accountant::class); }
    public function categories() { return $this->hasMany(Category::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function sales() { return $this->hasMany(Sale::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
    public function stockMovements() { return $this->hasMany(StockMovement::class); }
    public function withdrawals() { return $this->hasMany(Withdrawal::class); }
    public function employees() { return $this->hasMany(Employee::class); }
    public function shifts() { return $this->hasMany(StoreShift::class); }
    // داخل Model Store.php
public function saleItems()
{
    // علاقة "Has Many Through" تجلب البنود مباشرة عبر المبيعات
    return $this->hasManyThrough(SaleItem::class, Sale::class);
}

public function invoices()
{
    return $this->hasManyThrough(Invoice::class, Sale::class);
}
    // إضافة علاقة الإعدادات إذا كانت موجودة في جداولك
    // public function settings() { return $this->hasOne(StoreSetting::class); }

    /*
    |--------------------------------------------------------------------------
    | دوال الوصول (Accessors & Mutators)
    |--------------------------------------------------------------------------
    */

    /**
     * جلب رابط الشعار كاملاً، وإذا لم يوجد نضع صورة افتراضية
     */
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/default-store.png'); // صورة افتراضية للمتجر
    }

    /**
     * جلب بريد المالك بشكل مباشر
     */
    public function getOwnerEmailAttribute()
    {
        return $this->user ? $this->user->email : 'N/A';
    }

    /*
    |--------------------------------------------------------------------------
    | دوال المساعدة (Helper Functions)
    |--------------------------------------------------------------------------
    */

    /**
     * تحقق هل المتجر نشط أم لا
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * أوقات بداية الشفتات المفعلة مرتبة زمنياً.
     */
    public function shiftStartTimes(): array
    {
        $starts = [];
        // نحصر العدد بين 1 و3 لأن قاعدة البيانات تدعم ثلاثة أوقات بداية فقط حالياً.
        $shiftCount = max(1, min(3, (int) ($this->number_of_shifts ?: 1)));

        for ($i = 1; $i <= $shiftCount; $i++) {
            $value = $this->getAttribute("shift_{$i}_start");

            if ($value) {
                $starts[] = substr((string) $value, 0, 5);
            }
        }

        if (empty($starts)) {
            // في حال لم تضبط أوقات الشفتات بعد، نستخدم بداية اليوم كقيمة افتراضية آمنة.
            $starts[] = '00:00';
        }

        $starts = array_values(array_unique($starts));
        sort($starts);

        return $starts;
    }

    /**
     * يرجع حدود الشفت المجدول الحالي اعتماداً على إعدادات المتجر.
     */
    public function scheduledShiftWindow(?Carbon $reference = null): array
    {
        $reference = ($reference ?: now())->copy();
        $starts = $this->shiftStartTimes();
        $currentStart = null;
        $nextStart = null;

        foreach ($starts as $index => $time) {
            $candidate = $reference->copy()->setTimeFromTimeString($time);

            if ($candidate->lte($reference)) {
                $currentStart = $candidate;
                $currentIndex = $index;
                $nextTime = $starts[$index + 1] ?? $starts[0];
                $nextStart = $reference->copy()->setTimeFromTimeString($nextTime);

                if ($nextStart->lte($currentStart)) {
                    // إذا كان الشفت التالي في اليوم التالي (مثلاً شفت يبدأ 11 مساءً وينتهي 7 صباحاً).
                    $nextStart->addDay();
                }
            }
        }

        if (!$currentStart) {
            $currentStart = $reference->copy()->subDay()->setTimeFromTimeString(end($starts));
            $nextStart = $reference->copy()->setTimeFromTimeString($starts[0]);
        }

        return [
            'start' => $currentStart,
            'end' => $nextStart,
            'label' => 'من ' . $currentStart->format('h:i A') . ' إلى ' . $nextStart->format('h:i A'),
            'number' => ($currentIndex ?? count($starts) - 1) + 1,
            'total' => count($starts),
            'has_next_shift_today' => count($starts) > 1 && (($currentIndex ?? count($starts) - 1) + 1) < count($starts),
            'is_overdue' => $reference->gt($nextStart),
        ];
    }

    /**
     * حساب إجمالي المبيعات للمتجر (مثال لاستخدامه في التقارير)
     */
    public function totalSales()
    {
        return $this->sales()->sum('total_amount');
    }


    protected static function booted()
{
    static::deleting(function ($store) {
        // نتحقق إذا كان الحذف نهائياً (Force Delete) وليس مؤقتاً
        if ($store->isForceDeleting()) {

            // 1. حذف المبيعات وتوابعها (Invoices & SaleItems)
            $store->sales()->each(function($sale) {
                $sale->items()->delete();
                $sale->invoice()->delete(); // تأكد من وجود العلاقة في موديل Sale
                $sale->delete();
            });

            // 2. حذف الموظفين وسجلاتهم (Absences, Withdrawals, Debts)
            $store->employees()->each(function($employee) {
                // حذف السجلات المرتبطة بالـ person_id بناءً على جداولك
               Absence::where('person_id', $employee->id)->delete();
                Withdrawal::where('person_id', $employee->id)->delete();
                Debt::where('person_id', $employee->id)->delete();
                $employee->forceDelete();
            });

            // 3. حذف باقي التوابع مباشرة
            $store->accountants()->forceDelete();
            $store->products()->forceDelete();
            $store->categories()->forceDelete();
            $store->expenses()->forceDelete();
            $store->stockMovements()->delete();

            // 4. حذف اللوجو من السيرفر
            if ($store->logo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($store->logo);
            }
        }
    });
}
}
