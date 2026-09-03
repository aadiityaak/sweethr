<?php

namespace App\Console\Commands;

use App\Services\SemesterReportService;
use Illuminate\Console\Command;

class GenerateSemesterReports extends Command
{
    protected $signature = 'reports:generate-semester {year? : Tahun (default tahun berjalan)} {semester? : 1 atau 2 (default semester berjalan)} {--user= : Hanya untuk user_id tertentu}';

    protected $description = 'Generate raport semester (draft) — untuk satu karyawan atau massal semua karyawan aktif';

    public function handle(SemesterReportService $service): int
    {
        $year = (int) ($this->argument('year') ?? now()->year);
        $semester = (int) ($this->argument('semester') ?? (now()->month <= 6 ? 1 : 2));
        $userId = $this->option('user');

        if (! in_array($semester, [1, 2], true)) {
            $this->error('Semester harus 1 atau 2.');

            return self::FAILURE;
        }

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (! $user) {
                $this->error("User #{$userId} tidak ditemukan.");

                return self::FAILURE;
            }
            $report = $service->generate($user, $year, $semester, auth()->user());
            $this->info("Raport {$user->name} — {$year} Sem {$semester}: skor {$report->final_score} predikat {$report->grade} (draft).");

            return self::SUCCESS;
        }

        $count = $service->generateForAll($year, $semester, auth()->user());
        $this->info("{$count} raport semester {$year} Sem {$semester} di-generate (draft). Gunakan halaman Raport untuk publish.");

        return self::SUCCESS;
    }
}
