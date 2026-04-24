<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;

class FixWorkDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fixing negative work durations...');

        // Get all attendance records with negative work_duration
        $negativeRecords = Attendance::where('work_duration', '<', 0)->get();

        $this->command->line("Found {$negativeRecords->count()} records with negative work_duration");

        foreach ($negativeRecords as $record) {
            // Parse check-in and check-out times
            $checkInParts = explode(':', $record->check_in_time);
            $checkOutParts = explode(':', $record->check_out_time);

            // Convert to minutes since midnight
            $checkInMinutes = (intval($checkInParts[0]) * 60) + intval($checkInParts[1]);
            $checkOutMinutes = (intval($checkOutParts[0]) * 60) + intval($checkOutParts[1]);

            // Calculate correct work duration
            $correctDuration = $checkOutMinutes - $checkInMinutes;

            // Handle case where checkout is next day (rare but possible)
            if ($correctDuration < 0) {
                $correctDuration += (24 * 60); // Add 24 hours in minutes
            }

            // Calculate overtime
            $overtimeDuration = $correctDuration > 480 ? $correctDuration - 480 : 0;

            // Update the record
            $record->update([
                'work_duration' => $correctDuration,
                'overtime_duration' => $overtimeDuration,
            ]);

            $this->command->line("✓ Fixed {$record->date}: {$record->check_in_time} - {$record->check_out_time} = {$correctDuration} minutes");
        }

        $this->command->info('✅ All work durations have been corrected!');
    }
}
