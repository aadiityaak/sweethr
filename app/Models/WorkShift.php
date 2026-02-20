<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'work_hours',
        'break_duration',
        'overtime_multiplier',
        'workdays',
        'is_night_shift',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'overtime_multiplier' => 'decimal:2',
            'workdays' => 'array',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getWorkHoursFormatted()
    {
        $hours = floor($this->work_hours / 60);
        $minutes = $this->work_hours % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function isWorkday($dayOfWeek)
    {
        return in_array($dayOfWeek, $this->workdays);
    }
}
