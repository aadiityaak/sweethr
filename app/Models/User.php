<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'hire_date',
        'department_id',
        'position_id',
        'manager_id',
        'employment_status',
        'emergency_contact',
        'is_admin',
        'face_descriptors',
        'face_recognition_enabled',
        'face_setup_at',
        'face_recognition_attempts',
        'face_attempts_date',
        'face_recognition_mandatory',
        'allow_outside_radius',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'emergency_contact' => 'array',
            'is_admin' => 'boolean',
            'face_descriptors' => 'encrypted:array',
            'face_recognition_enabled' => 'boolean',
            'face_setup_at' => 'datetime',
            'face_attempts_date' => 'date',
            'face_recognition_mandatory' => 'boolean',
            'allow_outside_radius' => 'boolean',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function employeeShifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function shiftSwapsAsRequester()
    {
        return $this->hasMany(ShiftSwap::class, 'requester_id');
    }

    public function shiftSwapsAsTarget()
    {
        return $this->hasMany(ShiftSwap::class, 'target_user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function getIsActiveAttribute()
    {
        return $this->employment_status === 'active';
    }

    public function salarySettings()
    {
        return $this->hasMany(SalarySetting::class);
    }

    public function currentSalarySetting()
    {
        return $this->hasOne(SalarySetting::class)
            ->where('is_active', true)
            ->orderBy('effective_date', 'desc');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
