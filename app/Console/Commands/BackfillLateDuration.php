<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class BackfillLateDuration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:backfill-late-duration {--dry-run : Run without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill late_duration for existing attendance records based on check-in time vs shift start time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }

        $this->info('🚀 Starting late duration backfill...');

        // Get all attendance records that might need late_duration calculated
        // (records with check_in_time but late_duration not set or negative)
        $attendances = Attendance::with(['user.employeeShifts' => function ($q) {
            $q->with('workShift')->active();
        }])
            ->where(function ($query) {
                $query->where('late_duration', '<=', 0)
                    ->orWhereNull('late_duration');
            })
            ->whereNotNull('check_in_time')
            ->get();

        $this->info("Found {$attendances->count()} late attendance records to process");

        $bar = $this->output->createProgressBar($attendances->count());
        $bar->start();

        $updated = 0;
        $skipped = 0;

        foreach ($attendances as $attendance) {
            // Get user's current shift assignment for that date
            $shift = $attendance->user->employeeShifts()
                ->with('workShift')
                ->where('effective_date', '<=', $attendance->date)
                ->where(function ($query) use ($attendance) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $attendance->date);
                })
                ->first();

            if (! $shift || ! $shift->workShift) {
                $skipped++;
                $bar->advance();

                continue;
            }

            // Calculate late duration
            $checkInTime = Carbon::parse($attendance->check_in_time);
            $shiftStartTime = Carbon::parse($shift->workShift->start_time);

            if ($checkInTime->format('H:i:s') > $shiftStartTime->format('H:i:s')) {
                // Calculate late duration (absolute value to ensure positive)
                $lateDuration = abs($checkInTime->diffInMinutes($shiftStartTime));

                if (! $dryRun) {
                    $attendance->late_duration = $lateDuration;
                    $saved = $attendance->save();

                    if ($this->getOutput()->isVerbose()) {
                        $this->newLine();
                        $this->line("  ID {$attendance->id}: {$attendance->user->name} - {$lateDuration} minutes late - Saved: ".($saved ? 'YES' : 'NO'));
                    }
                }

                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Backfill completed!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped', $skipped],
                ['Total', $attendances->count()],
            ]
        );

        if ($dryRun) {
            $this->warn('⚠️  This was a DRY RUN. Run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
