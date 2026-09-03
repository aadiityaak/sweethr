<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsCurriculumMatrix extends Model
{
    protected $table = 'lms_curriculum_matrix';

    public const TYPE_MATERIAL = 'material';
    public const TYPE_QUIZ = 'quiz';
    public const TYPE_ASSIGNMENT = 'assignment';

    protected $fillable = [
        'position_id',
        'lms_category_id',
        'lms_material_id',
        'lms_quiz_id',
        'lms_assignment_id',
        'item_type',
        'is_mandatory',
        'deadline_days',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LmsCategory::class, 'lms_category_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class, 'lms_material_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LmsQuiz::class, 'lms_quiz_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    /**
     * Judul item tergantung tipe-nya.
     */
    public function getItemTitleAttribute(): string
    {
        return match ($this->item_type) {
            self::TYPE_MATERIAL => $this->material?->title ?? '-',
            self::TYPE_QUIZ => $this->quiz?->title ?? '-',
            self::TYPE_ASSIGNMENT => $this->assignment?->title ?? '-',
            default => '-',
        };
    }
}
