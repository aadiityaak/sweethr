<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    // Get setting value by key
    public static function get(string $key, $default = null)
    {
        return Cache::remember("company_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Set setting value
    public static function set(string $key, $value, string $type = 'text', string $group = 'general', ?string $description = null, bool $isPublic = false)
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
                'description' => $description,
                'is_public' => $isPublic,
            ]
        );

        // Clear cache
        Cache::forget("company_setting_{$key}");

        return $setting;
    }

    // Get all settings by group
    public static function getByGroup(string $group): array
    {
        return Cache::remember("company_settings_group_{$group}", 3600, function () use ($group) {
            return static::where('group', $group)->pluck('value', 'key')->toArray();
        });
    }

    // Get public settings (for frontend)
    public static function getPublicSettings(): array
    {
        return Cache::remember('company_settings_public', 3600, function () {
            $settings = static::where('is_public', true)->get();
            $result = [];

            foreach ($settings as $setting) {
                // Use the accessor to get the processed value (with Storage::url for images)
                $result[$setting->key] = $setting->value;
            }

            return $result;
        });
    }

    // Handle file uploads for image/file type settings
    public function getValueAttribute($value)
    {
        // Get the original value to check if it needs processing
        $originalValue = $this->attributes['value'] ?? $value;

        if (in_array($this->attributes['type'] ?? $this->type, ['image', 'file']) && $originalValue) {
            return Storage::url($originalValue);
        }

        if (($this->attributes['type'] ?? $this->type) === 'boolean') {
            return (bool) $originalValue;
        }

        return $originalValue;
    }

    // Clear all settings cache
    public static function clearCache()
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("company_setting_{$key}");
        }

        $groups = static::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("company_settings_group_{$group}");
        }

        Cache::forget('company_settings_public');
    }

    protected static function booted()
    {
        static::saved(function ($setting) {
            Cache::forget("company_setting_{$setting->key}");
            Cache::forget("company_settings_group_{$setting->group}");
            Cache::forget('company_settings_public');
        });

        static::deleted(function ($setting) {
            Cache::forget("company_setting_{$setting->key}");
            Cache::forget("company_settings_group_{$setting->group}");
            Cache::forget('company_settings_public');
        });
    }
}