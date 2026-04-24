<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'radius_meters',
        'timezone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isWithinRadius($lat, $lng)
    {
        $distance = $this->calculateDistance($lat, $lng);

        return $distance <= $this->radius_meters;
    }

    public function calculateDistance($lat, $lng)
    {
        $earthRadius = 6371000; // meters
        $lat1 = deg2rad($this->latitude);
        $lng1 = deg2rad($this->longitude);
        $lat2 = deg2rad($lat);
        $lng2 = deg2rad($lng);

        $deltaLat = $lat2 - $lat1;
        $deltaLng = $lng2 - $lng1;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1) * cos($lat2) *
             sin($deltaLng / 2) * sin($deltaLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
