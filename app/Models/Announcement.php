<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'category_id',
        'image_path',
        'priority',
        'is_active',
        'created_by',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($announcement) {
            // Auto-generate excerpt if not provided
            if (empty($announcement->excerpt)) {
                $announcement->excerpt = Str::limit(strip_tags($announcement->content), 200);
            }

            // Set published_at if not set and is_active is true
            if ($announcement->is_active && !$announcement->published_at) {
                $announcement->published_at = now();
            }
        });

        static::updating(function ($announcement) {
            // Update excerpt if content changed
            if ($announcement->isDirty('content') && empty($announcement->excerpt)) {
                $announcement->excerpt = Str::limit(strip_tags($announcement->content), 200);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AnnouncementCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopeVisible($query)
    {
        return $query->active()->published()->notExpired();
    }

    public function scopeByPriority($query, $direction = 'desc')
    {
        $priorities = ['urgent' => 4, 'high' => 3, 'normal' => 2, 'low' => 1];

        return $query->orderByRaw(
            'CASE priority ' .
            'WHEN "urgent" THEN 4 ' .
            'WHEN "high" THEN 3 ' .
            'WHEN "normal" THEN 2 ' .
            'WHEN "low" THEN 1 ' .
            'END ' . $direction
        );
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isVisible(): bool
    {
        return $this->is_active &&
               $this->published_at &&
               $this->published_at->isPast() &&
               !$this->isExpired();
    }
}
