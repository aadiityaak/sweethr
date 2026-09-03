<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryViolation extends Model
{
    public const CATEGORY_RINGAN = 'ringan';
    public const CATEGORY_SEDANG = 'sedang';
    public const CATEGORY_BERAT = 'berat';

    protected $fillable = [
        'code',
        'name',
        'category',
        'points',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'points' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function categoryLabel(string $category): string
    {
        return match ($category) {
            self::CATEGORY_RINGAN => 'Ringan (5–10 poin)',
            self::CATEGORY_SEDANG => 'Sedang (15–25 poin)',
            self::CATEGORY_BERAT => 'Berat (35+ poin)',
            default => $category,
        };
    }
}
