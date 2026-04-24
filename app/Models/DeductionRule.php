<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeductionRule extends Model
{
    protected $fillable = [
        'name',
        'type',
        'calculation_method',
        'amount',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function calculateDeduction(float $baseSalary, array $parameters = []): float
    {
        switch ($this->calculation_method) {
            case 'fixed':
                return $this->amount;
            case 'percentage':
                return ($baseSalary * $this->amount) / 100;
            case 'per_minute':
                return $this->amount * ($parameters['minutes'] ?? 0);
            case 'per_hour':
                return $this->amount * ($parameters['hours'] ?? 0);
            case 'per_day':
                return $this->amount * ($parameters['days'] ?? 0);
            default:
                return 0;
        }
    }
}
