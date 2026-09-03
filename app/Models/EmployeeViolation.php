<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeViolation extends Model
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AUTO_ATTENDANCE = 'auto_attendance';

    protected $fillable = [
        'user_id',
        'disciplinary_violation_id',
        'occurred_at',
        'points',
        'notes',
        'evidence_path',
        'reported_by',
        'source',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(DisciplinaryViolation::class, 'disciplinary_violation_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function scopeWithinPeriod($query, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null)
    {
        $from ??= now()->subMonths(6);

        return $query->whereBetween('occurred_at', [$from, $to ?? now()]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
