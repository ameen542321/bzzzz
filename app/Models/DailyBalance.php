<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyBalance extends Model
{
    protected $fillable = [
        'store_id', 'accountant_id', 'system_sales_total',
        'system_cash_expected', 'actual_cash_submitted',
        'difference', 'start_time', 'end_time', 'notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function accountant() {
        return $this->belongsTo(Accountant::class);
    }
}
