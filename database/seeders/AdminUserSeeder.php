<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'employee_id' => 'ADM001',
            'phone' => '08123456789',
            'hire_date' => now()->subMonths(12),
            'employment_status' => 'active',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample employees
        $employees = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'employee_id' => 'EMP001',
                'phone' => '08123456790',
                'hire_date' => now()->subMonths(6),
            ],
            [
                'name' => 'Siti Nurhaliza',
                'email' => 'siti@example.com',
                'employee_id' => 'EMP002',
                'phone' => '08123456791',
                'hire_date' => now()->subMonths(8),
            ],
            [
                'name' => 'Ahmad Wijaya',
                'email' => 'ahmad@example.com',
                'employee_id' => 'EMP003',
                'phone' => '08123456792',
                'hire_date' => now()->subMonths(4),
            ],
            [
                'name' => 'Dewi Kartika',
                'email' => 'dewi@example.com',
                'employee_id' => 'EMP004',
                'phone' => '08123456793',
                'hire_date' => now()->subMonths(10),
            ],
            [
                'name' => 'Roni Setiawan',
                'email' => 'roni@example.com',
                'employee_id' => 'EMP005',
                'phone' => '08123456794',
                'hire_date' => now()->subMonths(2),
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya@example.com',
                'employee_id' => 'EMP006',
                'phone' => '08123456795',
                'hire_date' => now()->subMonths(7),
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@example.com',
                'employee_id' => 'EMP007',
                'phone' => '08123456796',
                'hire_date' => now()->subMonths(5),
            ],
            [
                'name' => 'Rina Melati',
                'email' => 'rina@example.com',
                'employee_id' => 'EMP008',
                'phone' => '08123456797',
                'hire_date' => now()->subMonths(9),
            ],
            [
                'name' => 'Doni Pratama',
                'email' => 'doni@example.com',
                'employee_id' => 'EMP009',
                'phone' => '08123456798',
                'hire_date' => now()->subMonths(3),
            ],
            [
                'name' => 'Lina Sari',
                'email' => 'lina@example.com',
                'employee_id' => 'EMP010',
                'phone' => '08123456799',
                'hire_date' => now()->subMonth(),
            ],
        ];

        foreach ($employees as $index => $employee) {
            User::create([
                'name' => $employee['name'],
                'email' => $employee['email'],
                'password' => Hash::make('password'),
                'employee_id' => $employee['employee_id'],
                'phone' => $employee['phone'],
                'hire_date' => $employee['hire_date'],
                'employment_status' => $index === 9 ? 'inactive' : 'active', // Make last employee inactive
                'is_admin' => false,
                'email_verified_at' => now(),
            ]);
        }
    }
}
