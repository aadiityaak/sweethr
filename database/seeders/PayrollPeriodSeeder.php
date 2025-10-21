<?php

namespace Database\Seeders;

use App\Models\PayrollPeriod;
use Illuminate\Database\Seeder;

class PayrollPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create current payroll period with cut-off day 26
        $cutOffDay = 26;
        $now = now();

        $startDate = $now->day >= $cutOffDay
            ? $now->copy()->startOfMonth()->day($cutOffDay)
            : $now->copy()->subMonth()->startOfMonth()->day($cutOffDay);

        $endDate = $startDate->copy()->addMonth()->subDay();

        PayrollPeriod::create([
            'name' => 'Periode Gaji '.$startDate->format('F Y').' - '.$endDate->format('F Y'),
            'cut_off_day' => $cutOffDay,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'description' => "Periode gaji dengan cut-off tanggal {$cutOffDay}: ".$startDate->format('d M Y').' - '.$endDate->format('d M Y'),
        ]);

        // Create few previous periods for testing
        for ($i = 1; $i <= 3; $i++) {
            $prevStartDate = $startDate->copy()->subMonths($i);
            $prevEndDate = $prevStartDate->copy()->addMonth()->subDay();

            PayrollPeriod::create([
                'name' => 'Periode Gaji '.$prevStartDate->format('F Y').' - '.$prevEndDate->format('F Y'),
                'cut_off_day' => $cutOffDay,
                'start_date' => $prevStartDate,
                'end_date' => $prevEndDate,
                'is_active' => false,
                'description' => "Periode gaji dengan cut-off tanggal {$cutOffDay}: ".$prevStartDate->format('d M Y').' - '.$prevEndDate->format('d M Y'),
            ]);
        }

        $this->command->info('✅ Default payroll periods with cut-off day 26 created successfully!');
        $this->command->info("📅 Current period: {$startDate->format('d M Y')} - {$endDate->format('d M Y')}");
    }
}
