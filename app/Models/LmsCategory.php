<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LmsCategory extends Model
{
    protected $fillable = [
        'name',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LmsMaterial::class, 'lms_category_id');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(LmsQuiz::class, 'lms_category_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LmsAssignment::class, 'lms_category_id');
    }
}
