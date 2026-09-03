<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentContract extends Model
{
    public const TYPE_PKWT = 'pkwt';
    public const TYPE_PKWTT = 'pkwtt';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_RENEWED = 'renewed';
    public const STATUS_TERMINATED = 'terminated';

    protected $fillable = [
        'user_id',
        'contract_number',
        'type',
        'start_date',
        'end_date',
        'status',
        'salary_grade',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query
            ->where('type', self::TYPE_PKWT)
            ->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->end_date->startOfDay(), false);
    }

    public function getAlertLevelAttribute(): ?string
    {
        $days = $this->days_remaining;

        if ($days === null || $this->type !== self::TYPE_PKWT || $this->status !== self::STATUS_ACTIVE) {
            return null;
        }

        if ($days < 0) {
            return 'expired';
        }

        if ($days <= 30) {
            return 'critical';
        }

        if ($days <= 60) {
            return 'warning';
        }

        return null;
    }
}
