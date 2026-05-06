<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsPerformanceAppraisal extends Model
{
    protected $fillable = [
        'user_id',
        'evaluator_id',
        'evaluated_at',
        'quality_work',
        'quantity_work',
        'task_knowledge',
        'discipline',
        'teamwork',
        'communication',
        'initiative',
        'target_realization',
        'time_management',
        'attitude',
        'adaptability',
        'leadership_delegation',
        'leadership_development',
        'feedback',
    ];

    protected $casts = [
        'evaluated_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

