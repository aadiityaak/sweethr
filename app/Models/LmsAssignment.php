<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsAssignment extends Model
{
    protected $fillable = [
        'lms_category_id',
        'title',
        'description',
        'instructions',
        'due_at',
        'max_score',
        'max_attempts',
        'is_active',
    ];

    protected $casts = [
        'lms_category_id' => 'integer',
        'due_at' => 'datetime',
        'max_score' => 'integer',
        'max_attempts' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LmsCategory::class, 'lms_category_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LmsAssignmentSubmission::class, 'lms_assignment_id')->latest('submitted_at');
    }
}
