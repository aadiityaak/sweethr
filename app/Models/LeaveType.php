<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_days_per_year',
        'max_consecutive_days',
        'requires_approval',
        'is_paid',
        'approval_levels',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'is_paid' => 'boolean',
            'approval_levels' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
