<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            WorkShiftSeeder::class,
            LeaveTypeSeeder::class,
            OfficeLocationSeeder::class,
        ]);

        // Create admin user
        User::factory()->create([
            'name' => 'HR Admin',
            'email' => 'admin@example.com',
            'employee_id' => 'EMP001',
            'is_admin' => true,
            'employment_status' => 'active',
            'hire_date' => now()->subYears(2),
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'employee_id' => 'EMP002',
            'phone' => '+62812345678',
            'gender' => 'male',
            'employment_status' => 'active',
            'hire_date' => now()->subMonths(6),
        ]);
    }
}
