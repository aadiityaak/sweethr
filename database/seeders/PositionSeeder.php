<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $departments = Department::all()->keyBy('code');

        $positions = [
            // HR Positions
            [
                'title' => 'HR Manager',
                'code' => 'HRM001',
                'description' => 'Manages HR operations and employee relations',
                'department_id' => $departments['HR']->id,
                'level' => 5,
                'base_salary' => 15000000,
                'permissions' => ['manage_employees', 'manage_leave', 'view_reports'],
                'is_active' => true,
            ],
            [
                'title' => 'HR Staff',
                'code' => 'HRS001',
                'description' => 'Handles recruitment and administrative tasks',
                'department_id' => $departments['HR']->id,
                'level' => 3,
                'base_salary' => 8000000,
                'permissions' => ['manage_employees', 'manage_leave'],
                'is_active' => true,
            ],

            // IT Positions
            [
                'title' => 'IT Manager',
                'code' => 'ITM001',
                'description' => 'Leads technology team and infrastructure',
                'department_id' => $departments['IT']->id,
                'level' => 5,
                'base_salary' => 18000000,
                'permissions' => ['manage_system', 'view_reports'],
                'is_active' => true,
            ],
            [
                'title' => 'Software Developer',
                'code' => 'DEV001',
                'description' => 'Develops and maintains software applications',
                'department_id' => $departments['IT']->id,
                'level' => 4,
                'base_salary' => 12000000,
                'permissions' => ['develop_software'],
                'is_active' => true,
            ],

            // Finance Positions
            [
                'title' => 'Finance Manager',
                'code' => 'FNM001',
                'description' => 'Oversees financial planning and accounting',
                'department_id' => $departments['FINANCE']->id,
                'level' => 5,
                'base_salary' => 16000000,
                'permissions' => ['manage_finance', 'view_reports'],
                'is_active' => true,
            ],
            [
                'title' => 'Accountant',
                'code' => 'ACC001',
                'description' => 'Handles accounting and financial records',
                'department_id' => $departments['FINANCE']->id,
                'level' => 3,
                'base_salary' => 9000000,
                'permissions' => ['manage_finance'],
                'is_active' => true,
            ],

            // Marketing Positions
            [
                'title' => 'Marketing Manager',
                'code' => 'MKM001',
                'description' => 'Develops marketing strategies and campaigns',
                'department_id' => $departments['MKT']->id,
                'level' => 5,
                'base_salary' => 14000000,
                'permissions' => ['manage_marketing', 'view_reports'],
                'is_active' => true,
            ],
            [
                'title' => 'Marketing Staff',
                'code' => 'MKS001',
                'description' => 'Executes marketing activities and campaigns',
                'department_id' => $departments['MKT']->id,
                'level' => 3,
                'base_salary' => 8500000,
                'permissions' => ['manage_marketing'],
                'is_active' => true,
            ],

            // Operations Positions
            [
                'title' => 'Operations Manager',
                'code' => 'OPM001',
                'description' => 'Manages daily operations and processes',
                'department_id' => $departments['OPS']->id,
                'level' => 5,
                'base_salary' => 15500000,
                'permissions' => ['manage_operations', 'view_reports'],
                'is_active' => true,
            ],

            // Sales Positions
            [
                'title' => 'Sales Manager',
                'code' => 'SLM001',
                'description' => 'Leads sales team and customer relations',
                'department_id' => $departments['SALES']->id,
                'level' => 5,
                'base_salary' => 17000000,
                'permissions' => ['manage_sales', 'view_reports'],
                'is_active' => true,
            ],
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}
