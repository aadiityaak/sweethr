<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsPerformanceAppraisalParameter extends Model
{
    protected $fillable = [
        'key',
        'group',
        'label',
        'is_active',
        'managerial_only',
        'visible_position_ids',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'managerial_only' => 'boolean',
        'visible_position_ids' => 'array',
    ];
}
