<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsMaterial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'lms_category_id',
        'file_path',
        'thumbnail_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(LmsCategory::class, 'lms_category_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(LmsMaterialRead::class, 'lms_material_id');
    }
}
