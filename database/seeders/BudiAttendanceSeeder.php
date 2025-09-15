<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BudiAttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Budi Santoso
        $budiUser = User::where('name', 'Budi Santoso')->first();
        $officeLocation = OfficeLocation::first();

        if (!$budiUser || !$officeLocation) {
            $this->command->error('Budi Santoso user or office location not found!');
            return;
        }

        // Clear existing attendance records for Budi in the last 60 days
        Attendance::where('user_id', $budiUser->id)
            ->where('date', '>=', Carbon::now()->subDays(60))
            ->delete();

        $this->command->info('Generating 60 days of attendance data for Budi Santoso...');

        // Generate attendance data for the last 60 days
        for ($i = 59; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            // Skip some Sundays (holiday pattern)
            if ($date->isSunday() && rand(1, 3) === 1) {
                continue;
            }

            // Create realistic attendance patterns
            $attendanceRecord = $this->generateAttendanceRecord($date, $budiUser->id, $officeLocation->id);

            if ($attendanceRecord) {
                Attendance::create($attendanceRecord);
                $this->command->line("✓ Created attendance for {$date->format('Y-m-d')} - Status: {$attendanceRecord['status']}");
            }
        }

        $this->command->info('✅ Successfully created 60 days of attendance data for Budi Santoso!');
    }

    private function generateAttendanceRecord($date, $userId, $officeLocationId): ?array
    {
        // Weekend pattern: Saturday 50% chance, Sunday 20% chance
        if ($date->isSaturday() && rand(1, 2) === 1) {
            return null; // Skip this Saturday
        }
        if ($date->isSunday() && rand(1, 5) > 1) {
            return null; // Skip most Sundays
        }

        // Determine status with realistic probabilities
        $statusProbability = rand(1, 100);

        if ($statusProbability <= 75) {
            // 75% Present (on time)
            $status = 'present';
            $checkInTime = $this->getRandomCheckInTime(7, 30, 8, 15); // 07:30 - 08:15
        } elseif ($statusProbability <= 90) {
            // 15% Late
            $status = 'late';
            $checkInTime = $this->getRandomCheckInTime(8, 16, 9, 30); // 08:16 - 09:30
        } else {
            // 10% Absent/Leave
            return null; // Skip this day
        }

        $checkOutTime = $this->getRandomCheckOutTime($checkInTime);
        $workDuration = $this->calculateWorkDuration($checkInTime, $checkOutTime);

        return [
            'user_id' => $userId,
            'office_location_id' => $officeLocationId,
            'date' => $date->format('Y-m-d'),
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'check_in_latitude' => -6.2088 + (rand(-50, 50) / 1000000), // Jakarta area with variation
            'check_in_longitude' => 106.8456 + (rand(-50, 50) / 1000000),
            'check_out_latitude' => -6.2088 + (rand(-50, 50) / 1000000),
            'check_out_longitude' => 106.8456 + (rand(-50, 50) / 1000000),
            'work_duration' => $workDuration,
            'break_duration' => rand(30, 90), // 30-90 minutes break
            'overtime_duration' => $workDuration > 480 ? $workDuration - 480 : 0, // Overtime if > 8 hours
            'status' => $status,
            'notes' => $this->getRandomNotes(),
            'created_at' => $date,
            'updated_at' => $date,
        ];
    }

    private function getRandomCheckInTime($startHour, $startMin, $endHour, $endMin): string
    {
        $startMinutes = ($startHour * 60) + $startMin;
        $endMinutes = ($endHour * 60) + $endMin;
        $randomMinutes = rand($startMinutes, $endMinutes);

        $hour = intval($randomMinutes / 60);
        $minute = $randomMinutes % 60;

        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getRandomCheckOutTime(string $checkInTime): string
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        // Work for 8-10 hours
        $workHours = rand(8, 10);
        $workMinutes = rand(0, 59);
        $checkOut = $checkIn->copy()->addHours($workHours)->addMinutes($workMinutes);

        return $checkOut->format('H:i:s');
    }

    private function calculateWorkDuration(string $checkInTime, string $checkOutTime): int
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);
        return $checkOut->diffInMinutes($checkIn);
    }

    private function getRandomNotes(): ?string
    {
        $notes = [
            null,
            null,
            null,
            null, // Higher chance of no notes
            'Meeting dengan klien penting',
            'Presentasi project ke tim management',
            'Training sistem baru',
            'Koordinasi dengan department lain',
            'Menyelesaikan deadline project',
            'Audit internal departemen',
            'Workshop pengembangan skill',
            'Meeting evaluasi kinerja',
            'Persiapan laporan bulanan',
            'Troubleshooting sistem urgent',
        ];

        return $notes[array_rand($notes)];
    }
}
