<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get active employees only
        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();

        $officeLocation = OfficeLocation::first();

        if ($employees->isEmpty() || !$officeLocation) {
            $this->command->warn('No active employees or office locations found. Skipping attendance seeding.');
            return;
        }

        $this->command->info('Seeding attendance data for ' . $employees->count() . ' employees...');

        // Generate attendance data for the last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            // Skip weekends for some variety
            if ($date->isWeekend() && rand(1, 3) > 1) {
                continue;
            }

            foreach ($employees as $employee) {
                // 85% chance of attendance
                if (rand(1, 100) <= 85) {
                    $checkInTime = $this->getRandomCheckInTime();
                    $checkOutTime = $this->getRandomCheckOutTime($checkInTime);
                    $workDuration = $this->calculateWorkDuration($checkInTime, $checkOutTime);

                    Attendance::updateOrCreate(
                        [
                            'user_id' => $employee->id,
                            'date' => $date->format('Y-m-d'),
                        ],
                        [
                            'office_location_id' => $officeLocation->id,
                            'check_in_time' => $checkInTime,
                            'check_out_time' => $checkOutTime,
                            'check_in_latitude' => $officeLocation->latitude + (rand(-50, 50) / 1000000), // Small variation
                            'check_in_longitude' => $officeLocation->longitude + (rand(-50, 50) / 1000000),
                            'check_out_latitude' => $officeLocation->latitude + (rand(-50, 50) / 1000000),
                            'check_out_longitude' => $officeLocation->longitude + (rand(-50, 50) / 1000000),
                            'work_duration' => $workDuration,
                            'break_duration' => rand(30, 90), // 30-90 minutes break
                            'overtime_duration' => $workDuration > 480 ? $workDuration - 480 : 0, // Overtime if > 8 hours
                            'status' => $this->getAttendanceStatus($checkInTime),
                            'notes' => $this->getRandomNotes(),
                            'created_at' => $date,
                            'updated_at' => $date,
                        ]
                    );
                }
            }
        }
    }

    private function getRandomCheckInTime(): string
    {
        // Check-in between 07:00 - 09:30
        $hour = rand(7, 9);
        $minute = $hour === 9 ? rand(0, 30) : rand(0, 59);
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getRandomCheckOutTime(string $checkInTime): string
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        // Work for 8-10 hours
        $workHours = rand(8, 10);
        $workMinutes = rand(0, 59);
        $checkOut = $checkIn->copy()->addHours($workHours)->addMinutes($workMinutes);

        // Make sure checkout is not beyond 23:59
        if ($checkOut->format('H') >= 24) {
            $checkOut = Carbon::createFromFormat('H:i:s', '23:59:00');
        }

        return $checkOut->format('H:i:s');
    }

    private function calculateWorkDuration(string $checkInTime, string $checkOutTime): int
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);
        return $checkOut->diffInMinutes($checkIn);
    }

    private function getAttendanceStatus(string $checkInTime): string
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $standardTime = Carbon::createFromFormat('H:i:s', '08:00:00');

        if ($checkIn->lessThanOrEqualTo($standardTime)) {
            return 'present';
        } elseif ($checkIn->lessThanOrEqualTo($standardTime->copy()->addMinutes(15))) {
            return 'late';
        } else {
            return 'present'; // Still present but very late
        }
    }

    private function getRandomNotes(): ?string
    {
        $notes = [
            null,
            null,
            null, // More likely to have no notes
            'Lembur untuk menyelesaikan project',
            'Meeting dengan klien',
            'Training internal',
            'Presentasi ke management',
            'Koordinasi dengan tim',
        ];

        return $notes[array_rand($notes)];
    }
}
