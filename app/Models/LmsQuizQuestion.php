<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsQuizQuestion extends Model
{
    protected $fillable = [
        'lms_quiz_id',
        'type',
        'question',
        'options',
        'correct_answer',
        'points',
        'order',
        'is_active',
    ];

    protected $casts = [
        'lms_quiz_id' => 'integer',
        'options' => 'array',
        'correct_answer' => 'array',
        'points' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(LmsQuiz::class, 'lms_quiz_id');
    }
}
