<?php

namespace Database\Seeders;

use App\Models\SalarySetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalarySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Skip if salary setting already exists
            if ($user->salarySettings()->exists()) {
                continue;
            }

            // Base salary based on position level or default
            $baseSalary = match ($user->position?->level ?? 1) {
                1 => 5000000,  // Staff level
                2 => 7500000,  // Senior level
                3 => 10000000, // Supervisor level
                4 => 15000000, // Manager level
                5 => 25000000, // Director level
                default => 5000000,
            };

            // Create salary setting
            SalarySetting::create([
                'user_id' => $user->id,
                'base_salary' => $baseSalary,
                'allowances' => [
                    'transport' => 500000,
                    'meal' => 300000,
                    'communication' => 200000,
                ],
                'overtime_rate' => 1.5,
                'effective_date' => $user->hire_date ?? now()->subYear(),
                'is_active' => true,
            ]);
        }

        $this->command->info('Salary settings seeded successfully!');
    }
}
