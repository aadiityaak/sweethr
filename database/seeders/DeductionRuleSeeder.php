<?php

namespace Database\Seeders;

use App\Models\DeductionRule;
use Illuminate\Database\Seeder;

class DeductionRuleSeeder extends Seeder
{
    public function run(): void
    {
        $deductionRules = [
            [
                'name' => 'Potongan Telat',
                'type' => 'late',
                'calculation_method' => 'per_minute',
                'amount' => 1000,
                'description' => 'Potongan Rp 1.000 per menit keterlambatan',
                'is_active' => true,
            ],
            [
                'name' => 'Potongan Tidak Masuk',
                'type' => 'absent',
                'calculation_method' => 'per_day',
                'amount' => 100000,
                'description' => 'Potongan Rp 100.000 per hari tidak masuk kerja',
                'is_active' => true,
            ],
            [
                'name' => 'Potongan Cuti Berlebih',
                'type' => 'excess_leave',
                'calculation_method' => 'per_day',
                'amount' => 75000,
                'description' => 'Potongan Rp 75.000 per hari cuti melebihi jatah tahunan',
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Kesehatan',
                'type' => 'other',
                'calculation_method' => 'percentage',
                'amount' => 1,
                'description' => 'Potongan BPJS Kesehatan 1% dari gaji pokok',
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Ketenagakerjaan',
                'type' => 'other',
                'calculation_method' => 'percentage',
                'amount' => 2,
                'description' => 'Potongan BPJS Ketenagakerjaan 2% dari gaji pokok',
                'is_active' => true,
            ],
        ];

        foreach ($deductionRules as $rule) {
            DeductionRule::create($rule);
        }
    }
}
