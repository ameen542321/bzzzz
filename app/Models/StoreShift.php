<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreShift extends Model
{
    protected $fillable = [
        'store_id',
        'accountant_id',
        'daily_balance_id',
        'business_date',
        'shift_number',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'closure_action',
    ];

    protected $casts = [
        'business_date' => 'date',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function accountant()
    {
        return $this->belongsTo(Accountant::class);
    }

    public function dailyBalance()
    {
        return $this->belongsTo(DailyBalance::class);
    }
}
