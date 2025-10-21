<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    /** @use HasFactory<\Database\Factories\PayrollPeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cut_off_day',
        'start_date',
        'end_date',
        'is_active',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the currently active payroll period
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get period for a specific date
     */
    public static function getPeriodForDate(Carbon $date): ?self
    {
        return static::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Generate periods for a year based on cut-off day
     */
    public static function generateYearlyPeriods(int $year, int $cutOffDay = 26): array
    {
        $periods = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::create($year, $month, $cutOffDay);

            // If start date is after month end, move to next month
            if ($startDate->day > $startDate->daysInMonth) {
                $startDate = Carbon::create($year, $month, 1)->addMonth();
            }

            $endDate = $startDate->copy()->addMonth()->subDay();

            // Adjust year crossing
            if ($startDate->month == 12 && $startDate->day > $cutOffDay) {
                $endDate = Carbon::create($year + 1, 1, $cutOffDay - 1);
            }

            $periodName = $startDate->format('F Y').' - '.$endDate->format('F Y');

            $periods[] = [
                'name' => $periodName,
                'cut_off_day' => $cutOffDay,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'is_active' => false,
                'description' => "Payroll period: {$periodName}",
            ];
        }

        return $periods;
    }

    /**
     * Get formatted period name
     */
    public function getFormattedPeriodAttribute(): string
    {
        return $this->start_date->format('d M Y').' - '.$this->end_date->format('d M Y');
    }

    /**
     * Get period name for payroll display
     */
    public function getPayrollPeriodNameAttribute(): string
    {
        return $this->name ?? $this->formatted_period;
    }
}
