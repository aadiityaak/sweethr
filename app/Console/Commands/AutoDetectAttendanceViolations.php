<?php

namespace App\Console\Commands;

use App\Services\DisciplinaryPointService;
use Illuminate\Console\Command;

class AutoDetectAttendanceViolations extends Command
{
    protected $signature = 'discipline:auto-detect-attendance {--date= : Tanggal YYYY-MM-DD (default hari ini)}';

    protected $description = 'Deteksi keterlambatan dari absensi dan buat pelanggaran disiplin otomatis (idempoten per tanggal)';

    public function handle(DisciplinaryPointService $service): int
    {
        $date = $this->option('date') ?: now()->toDateString();

        try {
            $parsed = \Carbon\Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            $this->error("Format tanggal tidak valid: {$date}. Gunakan YYYY-MM-DD.");

            return self::FAILURE;
        }

        $created = $service->autoDetectFromAttendance($parsed);

        $this->info("Tanggal {$parsed}: {$created} pelanggaran keterlambatan dibuat (idempoten).");

        return self::SUCCESS;
    }
}
