<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Payroll;
use App\Models\PayrollDetail;

class PayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereHas('salarySettings')->get();

        foreach ($users as $user) {
            $salarySettings = $user->salarySettings()->where('is_active', true)->first();

            if (!$salarySettings) {
                continue;
            }

            // Create payroll for the last 3 months
            for ($i = 1; $i <= 3; $i++) {
                $date = now()->subMonths($i);

                // Skip if payroll already exists for this period
                if (Payroll::where('user_id', $user->id)
                    ->where('period_year', $date->year)
                    ->where('period_month', $date->month)
                    ->exists()) {
                    continue;
                }

                $baseSalary = $salarySettings->base_salary;
                $allowances = $salarySettings->allowances ?? [];

                // Calculate totals
                $grossSalary = $baseSalary + collect($allowances)->sum();
                $totalDeductions = $grossSalary * 0.1; // 10% deductions
                $netSalary = $grossSalary - $totalDeductions;

                // Create payroll record
                $payroll = Payroll::create([
                    'user_id' => $user->id,
                    'period_year' => $date->year,
                    'period_month' => $date->month,
                    'base_salary' => $baseSalary,
                    'allowances' => $allowances,
                    'gross_salary' => $grossSalary,
                    'deductions' => [
                        'tax' => $grossSalary * 0.05,
                        'insurance' => $grossSalary * 0.03,
                        'pension' => $grossSalary * 0.02,
                    ],
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'working_days' => 22,
                    'actual_working_days' => rand(20, 22),
                    'overtime_hours' => rand(0, 10),
                    'late_minutes' => rand(0, 120),
                    'absent_days' => rand(0, 2),
                ]);

                // Create payroll details
                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'earning',
                    'name' => 'Gaji Pokok',
                    'amount' => $baseSalary,
                    'calculation_basis' => ['type' => 'base_salary'],
                ]);

                foreach ($allowances as $key => $amount) {
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'earning',
                        'name' => ucfirst($key) . ' Allowance',
                        'amount' => $amount,
                        'calculation_basis' => ['type' => 'allowance', 'key' => $key],
                    ]);
                }

                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'name' => 'Pajak PPh 21',
                    'amount' => $grossSalary * 0.05,
                    'calculation_basis' => ['type' => 'tax', 'rate' => 0.05],
                ]);

                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'name' => 'BPJS Kesehatan',
                    'amount' => $grossSalary * 0.03,
                    'calculation_basis' => ['type' => 'insurance', 'rate' => 0.03],
                ]);
            }
        }

        $this->command->info('Payroll data seeded successfully!');
    }
}