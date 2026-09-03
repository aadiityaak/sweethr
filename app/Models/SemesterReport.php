<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SemesterReport extends Model
{
    public const GRADE_A = 'A';
    public const GRADE_B = 'B';
    public const GRADE_C = 'C';
    public const GRADE_D = 'D';
    public const GRADE_E = 'E';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    /** Bobot penilaian raport */
    public const WEIGHT_KPI = 0.5;
    public const WEIGHT_LMS = 0.3;
    public const WEIGHT_DISCIPLINE = 0.2;

    protected $fillable = [
        'user_id',
        'year',
        'semester',
        'kpi_score',
        'lms_score',
        'discipline_score',
        'final_score',
        'grade',
        'total_violation_points',
        'attendance_rate',
        'recommendation',
        'status',
        'published_at',
        'generated_by',
        'notes',
    ];

    protected $casts = [
        'kpi_score' => 'float',
        'lms_score' => 'float',
        'discipline_score' => 'float',
        'final_score' => 'float',
        'attendance_rate' => 'integer',
        'total_violation_points' => 'integer',
        'recommendation' => 'array',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePeriod($query, int $year, int $semester)
    {
        return $query->where('year', $year)->where('semester', (string) $semester);
    }

    public static function determineGrade(float $finalScore): string
    {
        return match (true) {
            $finalScore >= 90 => self::GRADE_A,
            $finalScore >= 80 => self::GRADE_B,
            $finalScore >= 70 => self::GRADE_C,
            $finalScore >= 60 => self::GRADE_D,
            default => self::GRADE_E,
        };
    }

    public static function gradeLabel(string $grade): string
    {
        return match ($grade) {
            self::GRADE_A => 'A (Outstanding)',
            self::GRADE_B => 'B (Good / Exceeds)',
            self::GRADE_C => 'C (Meets Standard)',
            self::GRADE_D => 'D (Needs Improvement)',
            self::GRADE_E => 'E (Unsatisfactory)',
            default => $grade,
        };
    }
}
