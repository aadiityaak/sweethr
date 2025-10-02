<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'office_location_id',
        'work_shift_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'work_duration',
        'break_duration',
        'overtime_duration',
        'status',
        'notes',
        'face_match_confidence',
        'face_verification_passed',
        'face_verification_skipped',
        'face_verification_notes',
        'face_photo_path',
        'face_confidence_score',
        'face_detected',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in_time' => 'datetime:H:i:s',
            'check_out_time' => 'datetime:H:i:s',
            'check_in_latitude' => 'decimal:8',
            'check_in_longitude' => 'decimal:8',
            'check_out_latitude' => 'decimal:8',
            'check_out_longitude' => 'decimal:8',
            'face_match_confidence' => 'decimal:2',
            'face_verification_passed' => 'boolean',
            'face_verification_skipped' => 'boolean',
            'face_confidence_score' => 'decimal:4',
            'face_detected' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function officeLocation()
    {
        return $this->belongsTo(OfficeLocation::class);
    }

    public function workShift()
    {
        return $this->belongsTo(WorkShift::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function getWorkDurationFormatted()
    {
        if (! $this->work_duration) {
            return null;
        }

        // Always use absolute value for duration
        $absDuration = abs($this->work_duration);
        $hours = floor($absDuration / 60);
        $minutes = $absDuration % 60;

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function isLate($shiftStartTime)
    {
        if (! $this->check_in_time) {
            return false;
        }

        // No tolerance - any check-in after shift start is considered late
        return Carbon::parse($this->check_in_time)->format('H:i:s') > Carbon::parse($shiftStartTime)->format('H:i:s');
    }

    public function getLateDuration($shiftStartTime)
    {
        if (! $this->check_in_time) {
            return null;
        }

        $checkIn = Carbon::parse($this->check_in_time);
        $shiftStart = Carbon::parse($shiftStartTime);

        // No tolerance - calculate late duration from exact shift start time
        if ($checkIn <= $shiftStart) {
            return null; // Not late
        }

        return $checkIn->diffInMinutes($shiftStart);
    }
}
