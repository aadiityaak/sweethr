<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsPerformanceAppraisalParameter extends Model
{
    protected $fillable = [
        'key',
        'group',
        'label',
        'sort_order',
        'is_active',
        'managerial_only',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'managerial_only' => 'boolean',
        'sort_order' => 'integer',
    ];
}

