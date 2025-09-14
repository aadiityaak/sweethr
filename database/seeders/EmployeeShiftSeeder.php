<?php

namespace Database\Seeders;

use App\Models\EmployeeShift;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class EmployeeShiftSeeder extends Seeder
{
    public function run(): void
    {
        // Get the default Regular Day Shift (09:00-17:00 WIB)
        $defaultShift = WorkShift::where('code', 'DAY')->first();

        if (! $defaultShift) {
            $this->command->warn('Default work shift (DAY) not found. Please run WorkShiftSeeder first.');

            return;
        }

        // Get all active employees (non-admin)
        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();

        $this->command->info("Assigning default shift '{$defaultShift->name}' to {$employees->count()} employees...");

        $assignedCount = 0;
        foreach ($employees as $employee) {
            // Check if employee already has an active shift assignment
            $existingAssignment = EmployeeShift::where('user_id', $employee->id)
                ->where('is_active', true)
                ->first();

            if (! $existingAssignment) {
                EmployeeShift::create([
                    'user_id' => $employee->id,
                    'work_shift_id' => $defaultShift->id,
                    'effective_date' => Carbon::now()->toDateString(),
                    'assignment_type' => 'permanent',
                    'is_active' => true,
                ]);
                $assignedCount++;
                $this->command->line("  ✓ Assigned to: {$employee->name} ({$employee->employee_id})");
            } else {
                $this->command->line("  - Already assigned: {$employee->name} ({$employee->employee_id})");
            }
        }

        $this->command->info("Successfully assigned default shift to {$assignedCount} employees.");
    }
}
