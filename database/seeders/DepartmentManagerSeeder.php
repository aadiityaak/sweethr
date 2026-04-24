<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments and active manager-level employees
        $departments = Department::all()->keyBy('code');

        // Map managers to their respective departments
        $managerAssignments = [
            'HR' => 'EMP001',      // Budi Santoso - HR Manager
            'IT' => 'EMP007',      // Eko Prasetyo - IT Staff (temporary)
            'FINANCE' => 'EMP003', // Ahmad Wijaya - Finance Manager
            'MKT' => 'EMP004',     // Dewi Kartika - Marketing Manager
            'OPS' => 'EMP005',     // Roni Setiawan - Operations Manager
            // SALES will not have manager assigned as the employee is inactive
        ];

        foreach ($managerAssignments as $deptCode => $employeeId) {
            $manager = User::where('employee_id', $employeeId)
                ->where('employment_status', 'active')
                ->first();

            if ($manager && isset($departments[$deptCode])) {
                $department = $departments[$deptCode];
                $department->update(['manager_id' => $manager->id]);

                $this->command->line("✓ Assigned {$manager->name} as manager of {$department->name}");
            }
        }
    }
}
