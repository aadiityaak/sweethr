<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsAssignmentSubmission extends Model
{
    protected $fillable = [
        'lms_assignment_id',
        'user_id',
        'submitted_at',
        'content',
        'attachment_path',
        'score',
        'feedback',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'lms_assignment_id' => 'integer',
        'user_id' => 'integer',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        'graded_at' => 'datetime',
        'graded_by' => 'integer',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LmsAssignment::class, 'lms_assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}

