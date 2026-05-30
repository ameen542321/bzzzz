<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Log extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'user_id',
        'actor_type',
        'actor_id',
        'action',
        'description',
        'model_type',
        'model_id',
        'details',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function actor()
    {
        return $this->morphTo(__FUNCTION__, 'actor_type', 'actor_id');
    }

    public function model()
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }

    public function getSnippetAttribute(): string
    {
        return Str::limit((string) ($this->description ?? ''), 60);
    }

    public function getActorDisplayNameAttribute(): string
    {
        if ($this->relationLoaded('actor') && $this->actor && isset($this->actor->name)) {
            return (string) $this->actor->name;
        }

        if ($this->relationLoaded('user') && $this->user) {
            return (string) $this->user->name;
        }

        if (is_array($this->details) && !empty($this->details['person_name'])) {
            return (string) $this->details['person_name'];
        }

        return 'نظام';
    }

    public function getActionLabelAttribute(): string
    {
        $labels = [
            'create' => 'إنشاء',
            'update' => 'تعديل',
            'delete' => 'حذف',
            'set_current' => 'تعيين متجر',
            'status_change' => 'تغيير حالة',
            'balance_done' => 'إقفال شفت',
        ];

        return $labels[$this->action] ?? str_replace('_', ' ', (string) $this->action);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($log) {
            if ($log->action === 'balance_done') {
                throw new \Exception('لا يمكن حذف سجلات إقفال الموازنة نهائياً لأسباب أمنية.');
            }
        });
    }
}
