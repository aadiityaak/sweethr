<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'code' => 'ANNUAL',
                'description' => 'Annual vacation leave',
                'max_days_per_year' => 12,
                'max_consecutive_days' => 7,
                'requires_approval' => true,
                'is_paid' => true,
                'approval_levels' => ['manager', 'hr'],
                'is_active' => true,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SICK',
                'description' => 'Medical/health related leave',
                'max_days_per_year' => 10,
                'max_consecutive_days' => 3,
                'requires_approval' => false,
                'is_paid' => true,
                'approval_levels' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Personal Leave',
                'code' => 'PERSONAL',
                'description' => 'Personal matters leave',
                'max_days_per_year' => 5,
                'max_consecutive_days' => 2,
                'requires_approval' => true,
                'is_paid' => false,
                'approval_levels' => ['manager'],
                'is_active' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'code' => 'MATERNITY',
                'description' => 'Maternity leave for mothers',
                'max_days_per_year' => 90,
                'max_consecutive_days' => 90,
                'requires_approval' => true,
                'is_paid' => true,
                'approval_levels' => ['hr'],
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'code' => 'EMERGENCY',
                'description' => 'Emergency situations',
                'max_days_per_year' => 3,
                'max_consecutive_days' => 2,
                'requires_approval' => false,
                'is_paid' => true,
                'approval_levels' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Bereavement Leave',
                'code' => 'BEREAVEMENT',
                'description' => 'Leave for family bereavement',
                'max_days_per_year' => 5,
                'max_consecutive_days' => 3,
                'requires_approval' => true,
                'is_paid' => true,
                'approval_levels' => ['manager'],
                'is_active' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}
