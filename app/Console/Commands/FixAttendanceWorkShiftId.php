<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\EmployeeShift;
use Illuminate\Console\Command;

class FixAttendanceWorkShiftId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fix-work-shift-id
                            {--dry-run : Show what will be changed without actually changing}
                            {--date= : Fix only for specific date (YYYY-MM-DD format)}
                            {--user-id= : Fix only for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect work_shift_id values in attendance records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificDate = $this->option('date');
        $specificUserId = $this->option('user-id');

        $this->info('Starting to fix work_shift_id values in attendance records...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Build query
        $query = Attendance::with(['user', 'workShift'])
            ->whereNotNull('work_shift_id');

        if ($specificDate) {
            $query->where('date', $specificDate);
            $this->info("Filtering by date: {$specificDate}");
        }

        if ($specificUserId) {
            $query->where('user_id', $specificUserId);
            $this->info("Filtering by user ID: {$specificUserId}");
        }

        $attendances = $query->get();
        $totalRecords = $attendances->count();

        if ($totalRecords === 0) {
            $this->info('No attendance records found with work_shift_id');

            return 0;
        }

        $this->info("Found {$totalRecords} attendance records to check");

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($attendances as $attendance) {
            // Check if the work_shift_id matches an actual work_shift
            if (! $attendance->workShift) {
                // This record has an invalid work_shift_id, try to find the correct one
                $employeeShift = EmployeeShift::where('user_id', $attendance->user_id)
                    ->where('effective_date', '<=', $attendance->date)
                    ->where(function ($query) use ($attendance) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $attendance->date);
                    })
                    ->with('workShift')
                    ->first();

                if ($employeeShift && $employeeShift->workShift) {
                    $correctWorkShiftId = $employeeShift->workShift->id;

                    $this->warn("Record ID {$attendance->id} (User: {$attendance->user->name}, Date: {$attendance->date})");
                    $this->line("  Current: work_shift_id = {$attendance->work_shift_id} (invalid)");
                    $this->line("  Should be: work_shift_id = {$correctWorkShiftId} ({$employeeShift->workShift->name})");

                    if (! $dryRun) {
                        try {
                            $attendance->update(['work_shift_id' => $correctWorkShiftId]);
                            $this->info('  ✓ Fixed!');
                            $fixedCount++;
                        } catch (\Exception $e) {
                            $this->error('  ✗ Failed to fix: '.$e->getMessage());
                            $errorCount++;
                        }
                    } else {
                        $this->line('  [Would be fixed in dry run]');
                        $fixedCount++;
                    }
                } else {
                    $this->warn("Record ID {$attendance->id} (User: {$attendance->user->name}, Date: {$attendance->date})");
                    $this->error('  ✗ No valid work shift found for this date');
                    $errorCount++;
                }
            } else {
                // This record has a valid work_shift_id
                $this->line("Record ID {$attendance->id} (User: {$attendance->user->name}, Date: {$attendance->date}) - OK");
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  Total records checked: {$totalRecords}");
        $this->line("  Records to fix: {$fixedCount}");
        $this->line("  Errors: {$errorCount}");

        if ($dryRun) {
            $this->warn('Run without --dry-run to apply the changes');
        } else {
            $this->info('Fix completed!');
        }

        return 0;
    }
}
