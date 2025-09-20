<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySetting extends Model
{
    protected $fillable = [
        'user_id',
        'base_salary',
        'allowances',
        'overtime_rate',
        'effective_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime_rate' => 'decimal:2',
            'allowances' => 'array',
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getTotalAllowancesAttribute(): float
    {
        if (! $this->allowances) {
            return 0;
        }

        return collect($this->allowances)->sum('amount');
    }
}
