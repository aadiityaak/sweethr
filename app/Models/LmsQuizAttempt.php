<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsQuizAttempt extends Model
{
    protected $fillable = [
        'lms_quiz_id',
        'user_id',
        'started_at',
        'submitted_at',
        'score',
        'max_score',
        'is_passed',
        'answers',
    ];

    protected $casts = [
        'lms_quiz_id' => 'integer',
        'user_id' => 'integer',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'max_score' => 'integer',
        'is_passed' => 'boolean',
        'answers' => 'array',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LmsQuiz::class, 'lms_quiz_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

