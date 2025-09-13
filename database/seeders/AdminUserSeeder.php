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
            'email' => 'admin@sweethr.com',
            'password' => Hash::make('password'),
            'employee_id' => 'ADM001',
            'phone' => '08123456789',
            'hire_date' => now(),
            'employment_status' => 'active',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample employee
        User::create([
            'name' => 'John Doe',
            'email' => 'employee@sweethr.com',
            'password' => Hash::make('password'),
            'employee_id' => 'EMP001',
            'phone' => '08123456790',
            'hire_date' => now(),
            'employment_status' => 'active',
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);
    }
}
