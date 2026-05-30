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
