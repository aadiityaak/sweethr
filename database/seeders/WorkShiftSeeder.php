<?php

namespace Database\Seeders;

use App\Models\WorkShift;
use Illuminate\Database\Seeder;

class WorkShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Regular Day Shift',
                'code' => 'DAY',
                'start_time' => '09:00',
                'end_time' => '17:00',
                'work_hours' => 480, // 8 hours in minutes
                'break_duration' => 60, // 1 hour lunch
                'overtime_multiplier' => 1.5,
                'workdays' => [1, 2, 3, 4, 5], // Monday to Friday
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Early Morning Shift',
                'code' => 'EARLY',
                'start_time' => '06:00',
                'end_time' => '14:00',
                'work_hours' => 480,
                'break_duration' => 60,
                'overtime_multiplier' => 1.5,
                'workdays' => [1, 2, 3, 4, 5],
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Afternoon Shift',
                'code' => 'AFTERNOON',
                'start_time' => '14:00',
                'end_time' => '22:00',
                'work_hours' => 480,
                'break_duration' => 60,
                'overtime_multiplier' => 1.75,
                'workdays' => [1, 2, 3, 4, 5],
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Night Shift',
                'code' => 'NIGHT',
                'start_time' => '22:00',
                'end_time' => '06:00',
                'work_hours' => 480,
                'break_duration' => 60,
                'overtime_multiplier' => 2.0,
                'workdays' => [1, 2, 3, 4, 5],
                'is_night_shift' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Part Time Morning',
                'code' => 'PT_MORNING',
                'start_time' => '09:00',
                'end_time' => '13:00',
                'work_hours' => 240, // 4 hours
                'break_duration' => 0,
                'overtime_multiplier' => 1.5,
                'workdays' => [1, 2, 3, 4, 5],
                'is_night_shift' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Weekend Shift',
                'code' => 'WEEKEND',
                'start_time' => '10:00',
                'end_time' => '18:00',
                'work_hours' => 480,
                'break_duration' => 60,
                'overtime_multiplier' => 2.0,
                'workdays' => [6, 7], // Saturday and Sunday
                'is_night_shift' => false,
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $shift) {
            WorkShift::updateOrCreate(
                ['code' => $shift['code']],
                $shift
            );
        }
    }
}