<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments and positions
        $departments = Department::all()->keyBy('code');
        $positions = Position::all()->keyBy('code');

        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'employee_id' => 'ADM001',
                'phone' => '08123456789',
                'date_of_birth' => '1985-05-15',
                'gender' => 'male',
                'address' => 'Jl. Admin No. 1, Jakarta',
                'hire_date' => now()->subMonths(24),
                'employment_status' => 'active',
                'is_admin' => true,
                'email_verified_at' => now(),
                'emergency_contact' => [
                    'name' => 'Admin Emergency',
                    'phone' => '08111111111',
                    'relationship' => 'spouse',
                ],
            ]
        );

        // Create sample employees with departments and positions
        $employees = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'employee_id' => 'EMP001',
                'phone' => '08123456790',
                'date_of_birth' => '1990-03-12',
                'gender' => 'male',
                'address' => 'Jl. Merdeka No. 10, Jakarta',
                'hire_date' => now()->subMonths(18),
                'department_code' => 'HR',
                'position_code' => 'HRM001',
                'employment_status' => 'active',
                'emergency_contact' => [
                    'name' => 'Sari Santoso',
                    'phone' => '08111111112',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@example.com',
                'employee_id' => 'EMP003',
                'phone' => '08123456792',
                'date_of_birth' => '1988-12-05',
                'gender' => 'male',
                'address' => 'Jl. Thamrin No. 15, Jakarta',
                'hire_date' => now()->subMonths(20),
                'department_code' => 'FINANCE',
                'position_code' => 'FNM001',
                'employment_status' => 'active',
                'emergency_contact' => [
                    'name' => 'Rina Wijaya',
                    'phone' => '08111111114',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'dewi@example.com',
                'employee_id' => 'EMP004',
                'phone' => '08123456793',
                'date_of_birth' => '1991-09-18',
                'gender' => 'female',
                'address' => 'Jl. Gatot Subroto No. 30, Jakarta',
                'hire_date' => now()->subMonths(12),
                'department_code' => 'MKT',
                'position_code' => 'MKM001',
                'employment_status' => 'active',
                'emergency_contact' => [
                    'name' => 'Budi Kartika',
                    'phone' => '08111111115',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Roni Setiawan',
                'email' => 'roni@example.com',
                'employee_id' => 'EMP005',
                'phone' => '08123456794',
                'date_of_birth' => '1987-04-22',
                'gender' => 'male',
                'address' => 'Jl. Kuningan No. 8, Jakarta',
                'hire_date' => now()->subMonths(8),
                'department_code' => 'OPS',
                'position_code' => 'OPM001',
                'employment_status' => 'active',
                'emergency_contact' => [
                    'name' => 'Maya Setiawan',
                    'phone' => '08111111116',
                    'relationship' => 'spouse',
                ],
            ],
            // Inactive employees (5 out of 10)
            [
                'name' => 'Maya Sari',
                'email' => 'maya@example.com',
                'employee_id' => 'EMP006',
                'phone' => '08123456795',
                'date_of_birth' => '1993-11-30',
                'gender' => 'female',
                'address' => 'Jl. Senayan No. 12, Jakarta',
                'hire_date' => now()->subMonths(15),
                'department_code' => 'SALES',
                'position_code' => 'SLM001',
                'employment_status' => 'inactive',
                'emergency_contact' => [
                    'name' => 'Eko Sari',
                    'phone' => '08111111117',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@example.com',
                'employee_id' => 'EMP007',
                'phone' => '08123456796',
                'date_of_birth' => '1989-06-14',
                'gender' => 'male',
                'address' => 'Jl. Menteng No. 20, Jakarta',
                'hire_date' => now()->subMonths(10),
                'department_code' => 'IT',
                'position_code' => 'DEV001',
                'employment_status' => 'inactive',
                'emergency_contact' => [
                    'name' => 'Rina Prasetyo',
                    'phone' => '08111111118',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Rina Melati',
                'email' => 'rina@example.com',
                'employee_id' => 'EMP008',
                'phone' => '08123456797',
                'date_of_birth' => '1994-02-28',
                'gender' => 'female',
                'address' => 'Jl. Kemang No. 5, Jakarta',
                'hire_date' => now()->subMonths(6),
                'department_code' => 'HR',
                'position_code' => 'HRS001',
                'employment_status' => 'inactive',
                'emergency_contact' => [
                    'name' => 'Doni Melati',
                    'phone' => '08111111119',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Doni Pratama',
                'email' => 'doni@example.com',
                'employee_id' => 'EMP009',
                'phone' => '08123456798',
                'date_of_birth' => '1986-10-11',
                'gender' => 'male',
                'address' => 'Jl. Cikini No. 18, Jakarta',
                'hire_date' => now()->subMonths(22),
                'department_code' => 'FINANCE',
                'position_code' => 'ACC001',
                'employment_status' => 'inactive',
                'emergency_contact' => [
                    'name' => 'Lina Pratama',
                    'phone' => '08111111120',
                    'relationship' => 'spouse',
                ],
            ],
            [
                'name' => 'Lina Sari',
                'email' => 'lina@example.com',
                'employee_id' => 'EMP010',
                'phone' => '08123456799',
                'date_of_birth' => '1995-08-03',
                'gender' => 'female',
                'address' => 'Jl. Pondok Indah No. 7, Jakarta',
                'hire_date' => now()->subMonths(4),
                'department_code' => 'MKT',
                'position_code' => 'MKS001',
                'employment_status' => 'inactive',
                'emergency_contact' => [
                    'name' => 'Andi Sari',
                    'phone' => '08111111121',
                    'relationship' => 'brother',
                ],
            ],
        ];

        foreach ($employees as $employee) {
            User::updateOrCreate(
                ['email' => $employee['email']],
                [
                    'name' => $employee['name'],
                    'password' => Hash::make('password'),
                    'employee_id' => $employee['employee_id'],
                    'phone' => $employee['phone'],
                    'date_of_birth' => $employee['date_of_birth'],
                    'gender' => $employee['gender'],
                    'address' => $employee['address'],
                    'hire_date' => $employee['hire_date'],
                    'department_id' => $departments[$employee['department_code']]->id,
                    'position_id' => $positions[$employee['position_code']]->id,
                    'employment_status' => $employee['employment_status'],
                    'emergency_contact' => $employee['emergency_contact'],
                    'is_admin' => false,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
