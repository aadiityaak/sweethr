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

    protected $appends = [
        'total_allowances',
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

        // Handle both array and object formats
        if (is_array($this->allowances)) {
            // If it's an array of objects like [{name: "meal", amount: 300000}]
            if (isset($this->allowances[0]['amount'])) {
                return collect($this->allowances)->sum('amount');
            }

            // If it's an associative array like {"meal": 300000, "transport": 500000}
            return array_sum(array_values($this->allowances));
        }

        return 0;
    }
}
