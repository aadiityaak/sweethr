<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class LmsCategory extends Model
{
    public const ROLE_EMPLOYEE = 'employee';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'parent_id',
        'is_active',
        'visible_roles',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_roles' => 'array',
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

    public static function allowedRoles(): array
    {
        return [
            self::ROLE_EMPLOYEE,
            self::ROLE_MANAGER,
            self::ROLE_ADMIN,
        ];
    }

    public static function rolesForUser(User $user): array
    {
        if ($user->is_admin) {
            return [self::ROLE_ADMIN];
        }

        $roles = [self::ROLE_EMPLOYEE];

        if ($user->subordinates()->exists()) {
            $roles[] = self::ROLE_MANAGER;
        }

        return $roles;
    }

    public function isVisibleToUser(User $user): bool
    {
        $visibleRoles = $this->visible_roles ?? [];
        if (! is_array($visibleRoles) || count($visibleRoles) === 0) {
            return true;
        }

        $userRoles = self::rolesForUser($user);
        foreach ($userRoles as $role) {
            if (in_array($role, $visibleRoles, true)) {
                return true;
            }
        }

        return false;
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        $roles = self::rolesForUser($user);

        return $query->where(function (Builder $q) use ($roles) {
            $q->whereNull('visible_roles')
                ->orWhereJsonLength('visible_roles', 0);

            foreach ($roles as $role) {
                $q->orWhereJsonContains('visible_roles', $role);
            }
        });
    }
}
