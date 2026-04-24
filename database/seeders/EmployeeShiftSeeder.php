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
        // Get all available shifts
        $shifts = WorkShift::where('is_active', true)->get();

        if ($shifts->isEmpty()) {
            $this->command->warn('No active work shifts found. Please run WorkShiftSeeder first.');

            return;
        }

        // Get all active employees (non-admin)
        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();

        $this->command->info("Assigning shifts to {$employees->count()} employees...");

        $assignedCount = 0;
        foreach ($employees as $index => $employee) {
            // Check if employee already has an active shift assignment
            $existingAssignment = EmployeeShift::where('user_id', $employee->id)
                ->where('is_active', true)
                ->first();

            if (! $existingAssignment) {
                // Assign different shifts based on index to create variety
                $shiftIndex = $index % $shifts->count();
                $assignedShift = $shifts[$shiftIndex];

                EmployeeShift::create([
                    'user_id' => $employee->id,
                    'work_shift_id' => $assignedShift->id,
                    'effective_date' => Carbon::now()->subDays(30)->format('Y-m-d'), // Start 30 days ago
                    'end_date' => null, // No end date for permanent assignment
                    'assignment_type' => 'permanent',
                    'is_active' => true,
                ]);
                $assignedCount++;
                $this->command->line("  ✓ Assigned '{$assignedShift->name}' ({$assignedShift->start_time}-{$assignedShift->end_time}) to: {$employee->name} ({$employee->employee_id})");
            } else {
                $this->command->line("  - Already assigned: {$employee->name} ({$employee->employee_id})");
            }
        }

        $this->command->info("Successfully assigned shifts to {$assignedCount} employees.");
    }
}
