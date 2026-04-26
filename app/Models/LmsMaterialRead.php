<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsMaterialRead extends Model
{
    protected $fillable = [
        'lms_material_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'lms_material_id' => 'integer',
        'user_id' => 'integer',
        'read_at' => 'datetime',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(LmsMaterial::class, 'lms_material_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

