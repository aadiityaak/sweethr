<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'department_id',
        'level',
        'base_salary',
        'permissions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'permissions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }
}
