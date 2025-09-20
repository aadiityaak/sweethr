<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id',
        'type',
        'name',
        'description',
        'amount',
        'calculation_basis',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'calculation_basis' => 'array',
        ];
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
