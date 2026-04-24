<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    protected $fillable = [
        'user_id',
        'payroll_period_id',
        'period_year',
        'period_month',
        'start_date',
        'end_date',
        'base_salary',
        'allowances',
        'gross_salary',
        'deductions',
        'total_deductions',
        'net_salary',
        'working_days',
        'actual_working_days',
        'overtime_hours',
        'late_minutes',
        'absent_days',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'late_minutes' => 'decimal:2',
            'allowances' => 'array',
            'deductions' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function allowanceDetails(): HasMany
    {
        return $this->details()->where('type', 'allowance');
    }

    public function deductionDetails(): HasMany
    {
        return $this->details()->where('type', 'deduction');
    }

    public function overtimeDetails(): HasMany
    {
        return $this->details()->where('type', 'overtime');
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('period_year', $year)->where('period_month', $month);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getPeriodNameAttribute(): string
    {
        // If using payroll period, return the period name
        if ($this->payrollPeriod) {
            return $this->payrollPeriod->payroll_period_name;
        }

        // Fallback to month-year format
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return $monthNames[$this->period_month].' '.$this->period_year;
    }

    /**
     * Get formatted period range for display
     */
    public function getFormattedPeriodAttribute(): string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('d M Y').' - '.$this->end_date->format('d M Y');
        }

        return $this->period_name;
    }
}
