<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class PopulateAttendanceWorkShiftId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:populate-shift-id {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate work_shift_id for existing attendance records based on employee shift assignments';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('Running in DRY RUN mode - no changes will be made');
        }

        // Get all attendance records without work_shift_id
        $attendances = Attendance::whereNull('work_shift_id')
            ->with('user.employeeShifts.workShift')
            ->get();

        if ($attendances->isEmpty()) {
            $this->info('No attendance records need updating.');

            return Command::SUCCESS;
        }

        $this->info("Found {$attendances->count()} attendance records without work_shift_id");

        $updated = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($attendances->count());
        $progressBar->start();

        foreach ($attendances as $attendance) {
            // Find the user's shift assignment for the attendance date
            $shift = $attendance->user->employeeShifts()
                ->where('effective_date', '<=', $attendance->date)
                ->where(function ($query) use ($attendance) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $attendance->date);
                })
                ->with('workShift')
                ->first();

            if ($shift && $shift->workShift) {
                if (! $isDryRun) {
                    $attendance->update(['work_shift_id' => $shift->work_shift_id]);
                }
                $updated++;

                if ($isDryRun && $updated <= 5) {
                    $this->newLine();
                    $this->line("  Would update: Attendance #{$attendance->id} - {$attendance->user->name} - {$attendance->date} -> {$shift->workShift->name}");
                }
            } else {
                $skipped++;

                if ($isDryRun && $skipped <= 5) {
                    $this->newLine();
                    $this->warn("  Would skip: Attendance #{$attendance->id} - {$attendance->user->name} - {$attendance->date} (no shift found)");
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($isDryRun) {
            $this->info("DRY RUN completed:");
            $this->info("  - {$updated} records would be updated");
            $this->info("  - {$skipped} records would be skipped (no shift assignment found)");
            $this->newLine();
            $this->info('Run without --dry-run to apply changes');
        } else {
            $this->info("Successfully updated {$updated} attendance records");
            if ($skipped > 0) {
                $this->warn("Skipped {$skipped} records (no shift assignment found)");
            }
        }

        return Command::SUCCESS;
    }
}
