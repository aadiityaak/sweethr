<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAction extends Model
{
    public const TYPE_TEGURAN_LISAN = 'teguran_lisan';
    public const TYPE_SP1 = 'sp1';
    public const TYPE_SP2 = 'sp2';
    public const TYPE_SP3 = 'sp3';
    public const TYPE_PHK_EVAL = 'phk_eval';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'action_type',
        'triggered_points',
        'status',
        'issued_at',
        'freeze_until',
        'required_remediation',
        'suspend_incentive',
        'document_path',
        'notes',
        'issued_by',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'freeze_until' => 'date',
        'required_remediation' => 'boolean',
        'suspend_incentive' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            self::TYPE_TEGURAN_LISAN => 'Teguran Lisan',
            self::TYPE_SP1 => 'Surat Peringatan I (SP 1)',
            self::TYPE_SP2 => 'Surat Peringatan II (SP 2)',
            self::TYPE_SP3 => 'Surat Peringatan III (SP 3)',
            self::TYPE_PHK_EVAL => 'Evaluasi PHK',
            default => $type,
        };
    }

    public static function isPromotionFrozen(int $userId): bool
    {
        return self::forUser($userId)
            ->active()
            ->where('action_type', self::TYPE_SP1)
            ->where('freeze_until', '>=', now()->toDateString())
            ->exists();
    }
}
