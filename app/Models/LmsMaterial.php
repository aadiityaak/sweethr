<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsMaterial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'type',
        'file_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
