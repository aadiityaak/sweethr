<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsQuiz extends Model
{
    protected $fillable = [
        'lms_category_id',
        'title',
        'description',
        'time_limit_minutes',
        'passing_score',
        'max_attempts',
        'is_active',
    ];

    protected $casts = [
        'lms_category_id' => 'integer',
        'time_limit_minutes' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LmsCategory::class, 'lms_category_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(LmsQuizQuestion::class, 'lms_quiz_id')->orderBy('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(LmsQuizAttempt::class, 'lms_quiz_id')->latest('submitted_at');
    }
}
