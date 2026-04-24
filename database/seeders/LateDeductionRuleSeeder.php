<?php

namespace Database\Seeders;

use App\Models\DeductionRule;
use Illuminate\Database\Seeder;

class LateDeductionRuleSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create deduction rule for late attendance
        DeductionRule::updateOrCreate(
            ['type' => 'late'],
            [
                'name' => 'Potongan Keterlambatan',
                'type' => 'late',
                'calculation_method' => 'per_minute',
                'amount' => 1000, // Rp 1.000 per menit
                'description' => 'Potongan gaji untuk keterlambatan check-in. Dihitung Rp 1.000 per menit keterlambatan.',
                'is_active' => true,
            ]
        );

        $this->command->info('✅ Late deduction rule created successfully!');
        $this->command->info('   Type: late');
        $this->command->info('   Method: per_minute');
        $this->command->info('   Amount: Rp 1.000 per menit');
    }
}
