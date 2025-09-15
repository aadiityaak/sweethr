<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FixAllDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Fixing ALL duration calculations...');

        // Get all attendance records with check-in and check-out times
        $allRecords = Attendance::whereNotNull('check_in_time')
            ->whereNotNull('check_out_time')
            ->get();

        $this->command->line("Found {$allRecords->count()} attendance records with both check-in and check-out times");

        $fixedCount = 0;

        foreach ($allRecords as $record) {
            // Get the Carbon datetime objects and extract time part
            $checkInCarbon = $record->check_in_time;
            $checkOutCarbon = $record->check_out_time;

            // Extract time components from Carbon objects
            $checkInHour = $checkInCarbon->hour;
            $checkInMinute = $checkInCarbon->minute;
            $checkOutHour = $checkOutCarbon->hour;
            $checkOutMinute = $checkOutCarbon->minute;

            // Convert to total minutes from midnight
            $checkInTotalMinutes = ($checkInHour * 60) + $checkInMinute;
            $checkOutTotalMinutes = ($checkOutHour * 60) + $checkOutMinute;

            // Calculate duration (handle next day checkout if needed)
            $duration = $checkOutTotalMinutes - $checkInTotalMinutes;
            if ($duration < 0) {
                $duration += (24 * 60); // Add 24 hours if checkout is next day
            }

            // Calculate overtime (if work > 8 hours = 480 minutes)
            $overtime = $duration > 480 ? $duration - 480 : 0;

            // Only update if duration is different
            if ($record->work_duration !== $duration || $record->overtime_duration !== $overtime) {
                $record->update([
                    'work_duration' => $duration,
                    'overtime_duration' => $overtime,
                ]);

                $hours = floor($duration / 60);
                $minutes = $duration % 60;
                $checkInTimeStr = sprintf('%02d:%02d', $checkInHour, $checkInMinute);
                $checkOutTimeStr = sprintf('%02d:%02d', $checkOutHour, $checkOutMinute);
                $this->command->line("✓ Fixed {$record->date}: {$checkInTimeStr} - {$checkOutTimeStr} = {$duration} minutes ({$hours}h {$minutes}m)");
                $fixedCount++;
            }
        }

        $this->command->info("✅ Fixed {$fixedCount} duration calculations!");

        // Show some updated stats
        $stats = Attendance::selectRaw('
            MIN(work_duration) as min_duration,
            MAX(work_duration) as max_duration,
            ROUND(AVG(work_duration), 1) as avg_duration,
            COUNT(*) as total_records
        ')->whereNotNull('work_duration')->first();

        if ($stats) {
            $this->command->info("📊 Updated Statistics:");
            $minHours = floor($stats->min_duration / 60);
            $minMins = $stats->min_duration % 60;
            $maxHours = floor($stats->max_duration / 60);
            $maxMins = $stats->max_duration % 60;
            $avgHours = floor($stats->avg_duration / 60);
            $avgMins = round($stats->avg_duration % 60);

            $this->command->line("   Min: {$stats->min_duration} minutes ({$minHours}h {$minMins}m)");
            $this->command->line("   Max: {$stats->max_duration} minutes ({$maxHours}h {$maxMins}m)");
            $this->command->line("   Avg: {$stats->avg_duration} minutes ({$avgHours}h {$avgMins}m)");
            $this->command->line("   Total: {$stats->total_records} records");
        }
    }
}