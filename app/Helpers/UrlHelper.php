<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class UrlHelper
{
    /**
     * Generate asset URL that works in both development and production
     */
    public static function asset(string $path): string
    {
        // Get current request URL to determine correct base URL
        $currentUrl = request()->getSchemeAndHttpHost();

        // If APP_URL is set and doesn't match current URL, use current URL
        if (config('app.url') !== $currentUrl) {
            return $currentUrl.'/'.ltrim($path, '/');
        }

        // Otherwise use Laravel's asset helper
        return asset($path);
    }

    /**
     * Generate storage URL that works in both development and production
     */
    public static function storage(string $path): string
    {
        // Get current request URL to determine correct base URL
        $currentUrl = request()->getSchemeAndHttpHost();

        // If APP_URL is set and doesn't match current URL, use current URL
        if (config('app.url') !== $currentUrl) {
            return $currentUrl.'/storage/'.ltrim($path, '/');
        }

        // Otherwise use standard Storage URL
        return Storage::disk('public')->url($path);
    }

    /**
     * Generate avatar URL specifically
     */
    public static function avatar(?string $avatarPath): ?string
    {
        if (! $avatarPath) {
            return null;
        }

        return self::storage($avatarPath);
    }
}
