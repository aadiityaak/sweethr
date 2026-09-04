<?php

namespace App\Services;

use App\Models\EmploymentContract;
use App\Models\EmployeeViolation;
use App\Models\LmsAssignment;
use App\Models\LmsCategory;
use App\Models\LmsPerformanceAppraisal;
use App\Models\LmsQuizAttempt;
use App\Models\SalarySetting;
use App\Models\SemesterReport;
use App\Models\User;

class SemesterReportService
{
    public function __construct(
        private RewardRecommendationService $rewardService,
        private DisciplinaryPointService $disciplinaryService,
        private ContractAlertService $contractService,
    ) {}

    /**
     * Generate raport semester untuk satu karyawan.
     * Bobot: KPI 50% + LMS 30% + Disiplin 20%.
     */
    public function generate(User $user, int $year, int $semester, ?User $generator = null, ?float $kpiOverride = null): SemesterReport
    {
        [$periodStart, $periodEnd] = $this->getPeriodBounds($year, $semester);

        $kpiScore = $kpiOverride ?? $this->calculateKpiScore($user, $periodStart, $periodEnd);
        $lmsScore = $this->calculateLmsScore($user, $periodStart, $periodEnd);
        $disciplineData = $this->calculateDisciplineScore($user, $periodStart, $periodEnd);

        $finalScore = round(
            ($kpiScore * SemesterReport::WEIGHT_KPI)
            + ($lmsScore * SemesterReport::WEIGHT_LMS)
            + ($disciplineData['score'] * SemesterReport::WEIGHT_DISCIPLINE),
            2
        );

        $grade = SemesterReport::determineGrade($finalScore);

        $report = SemesterReport::updateOrCreate(
            [
                'user_id' => $user->id,
                'year' => $year,
                'semester' => (string) $semester,
            ],
            [
                'kpi_score' => $kpiScore,
                'lms_score' => $lmsScore,
                'discipline_score' => $disciplineData['score'],
                'final_score' => $finalScore,
                'grade' => $grade,
                'total_violation_points' => $disciplineData['violation_points'],
                'attendance_rate' => $disciplineData['attendance_rate'],
                'recommendation' => null,
                'status' => SemesterReport::STATUS_DRAFT,
                'published_at' => null,
                'generated_by' => $generator?->id ?? auth()->id(),
            ]
        );

        $recommendation = $this->rewardService->buildRecommendation($user, $report, $year, $semester);

        $report->update(['recommendation' => $recommendation]);

        $this->ensurePipOrPrePromotionAssignment($user, $report);

        return $report->fresh();
    }

    /**
     * Generate massal untuk semua karyawan aktif.
     */
    public function generateForAll(int $year, int $semester, ?User $generator = null): int
    {
        $count = 0;

        User::query()->active()->chunkById(100, function ($users) use ($year, $semester, $generator, &$count) {
            foreach ($users as $user) {
                $this->generate($user, $year, $semester, $generator);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Terbitkan raport (draft → published, terlihat karyawan).
     */
    public function publish(SemesterReport $report): SemesterReport
    {
        $report->update([
            'status' => SemesterReport::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return $report;
    }

    /**
     * KPI 50% — rata-rata appraisal parameter inti dalam periode.
     */
    public function calculateKpiScore(User $user, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        $appraisals = LmsPerformanceAppraisal::query()
            ->where('user_id', $user->id)
            ->whereBetween('evaluated_at', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        if ($appraisals->isEmpty()) {
            return 0.0;
        }

        $coreParams = [
            'quality_work', 'quantity_work', 'task_knowledge', 'discipline', 'teamwork',
            'communication', 'initiative', 'target_realization', 'time_management', 'attitude', 'adaptability',
        ];

        $sum = 0;
        $count = 0;

        foreach ($appraisals as $appraisal) {
            $values = [];
            foreach ($coreParams as $param) {
                if ($appraisal->$param !== null) {
                    $values[] = (float) $appraisal->$param;
                }
            }
            if ($values !== []) {
                $sum += array_sum($values) / count($values);
                $count++;
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        return round(($sum / $count) * 10, 2); // skala 1-10 → 0-100
    }

    /**
     * LMS 30% — rata-rata kuis (70%) + penyelesaian assignment (30%).
     */
    public function calculateLmsScore(User $user, \DateTimeInterface $start, \DateTimeInterface $end): float
    {
        // Kuis: attempt terbaik per kuis dalam periode
        $bestScores = LmsQuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$start, $end])
            ->whereNotNull('score')
            ->whereNotNull('max_score')
            ->where('max_score', '>', 0)
            ->selectRaw('lms_quiz_id, MAX(score / max_score * 100) as best_pct')
            ->groupBy('lms_quiz_id')
            ->pluck('best_pct');

        $quizAvg = $bestScores->isNotEmpty() ? $bestScores->avg() : 0.0;

        // Assignment: rata-rata skor submission (skala asumsi 0-100)
        $assignmentAvg = \App\Models\LmsAssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->whereBetween('submitted_at', [$start, $end])
            ->whereNotNull('score')
            ->avg('score');

        $assignmentAvg = $assignmentAvg !== null ? (float) $assignmentAvg : 0.0;

        if ($quizAvg === 0.0 && $assignmentAvg === 0.0) {
            return 0.0;
        }

        if ($assignmentAvg === 0.0) {
            return round($quizAvg, 2);
        }

        if ($quizAvg === 0.0) {
            return round($assignmentAvg, 2);
        }

        return round(($quizAvg * 0.7) + ($assignmentAvg * 0.3), 2);
    }

    /**
     * Disiplin 20% — kehadiran (60%) + poin pelanggaran (40%).
     * Poin 0 = 100; berkurang proporsional per tier ambang.
     */
    public function calculateDisciplineScore(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $totalDays = (int) User::query()->whereKey($user->id)->value('id') > 0
            ? $start->diff($end)->days + 1
            : 0;

        $attendanceRate = $this->calculateAttendanceRate($user, $start, $end);

        $violationPoints = (int) EmployeeViolation::forUser($user->id)
            ->whereBetween('occurred_at', [$start, $end])
            ->sum('points');

        // Konversi poin: 0 → 100, ≥85 → 0 (linear terhadap 85)
        $violationScore = max(0, 100 - ($violationPoints * (100 / 85)));

        $score = round(($attendanceRate * 0.6) + ($violationScore * 0.4), 2);

        return [
            'score' => $score,
            'attendance_rate' => $attendanceRate,
            'violation_points' => $violationPoints,
        ];
    }

    /**
     * Persentase hari hadir vs hari kerja terjadwal (fallback: hari kerja Senin-Sabtu).
     */
    public function calculateAttendanceRate(User $user, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $scheduledDays = 0;
        $presentDays = 0;

        $from = \DateTime::createFromInterface($start);
        $to = \DateTime::createFromInterface($end);

        $attendances = \App\Models\Attendance::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->whereIn('status', ['present', 'late'])
            ->pluck('date')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string) $d)
            ->all();

        $presentSet = array_flip($attendances);

        for ($date = $from; $date <= $to; $date->modify('+1 day')) {
            $dayOfWeek = (int) $date->format('N');
            if ($dayOfWeek > 6) {
                continue; // Minggu libur
            }
            $scheduledDays++;
            if (isset($presentSet[$date->format('Y-m-d')])) {
                $presentDays++;
            }
        }

        if ($scheduledDays === 0) {
            return 0;
        }

        return (int) round(($presentDays / $scheduledDays) * 100);
    }

    /**
     * Auto-assign modul PIP (grade D) atau Pre-Promotion (grade A/B).
     * Idempoten per raport — cek title mengandung [Report #id].
     */
    private function ensurePipOrPrePromotionAssignment(User $user, SemesterReport $report): void
    {
        $tag = "[Report #{$report->id}]";

        if ($report->grade === SemesterReport::GRADE_D) {
            if (LmsAssignment::where('title', 'like', "%{$tag} PIP%")->exists()) {
                return;
            }
            $category = LmsCategory::firstOrCreate(
                ['name' => 'Program PIP', 'parent_id' => null],
                ['is_active' => true, 'visible_roles' => null]
            );
            LmsAssignment::create([
                'lms_category_id' => $category->id,
                'title' => "Rencana PIP 30 Hari — {$user->name} {$tag} PIP",
                'description' => "PIP otomatis: predikat D pada {$report->year} S{$report->semester} untuk {$user->name}. Wajib perbaikan 30 hari + coaching mingguan.",
                'instructions' => "Selesaikan target mingguan bersama atasan. Batas 30 hari sejak raport diterbitkan. {$tag} PIP",
                'due_at' => now()->addDays(30),
                'max_score' => 100,
                'max_attempts' => 3,
                'is_active' => true,
            ]);
            return;
        }

        if (in_array($report->grade, [SemesterReport::GRADE_A, SemesterReport::GRADE_B], true)) {
            // hormati freeze — tetap buat tugas tapi beri catatan diblokir di title/instruksi? skip bila dibekukan
            $rec = $report->recommendation ?? [];
            if (! empty($rec['blocked_by_freeze']) || ! empty($rec['promotion_frozen'])) {
                return;
            }
            if (LmsAssignment::where('title', 'like', "%{$tag} PRE%")->exists()) {
                return;
            }
            $category = LmsCategory::firstOrCreate(
                ['name' => 'Pre-Promotion Course', 'parent_id' => null],
                ['is_active' => true, 'visible_roles' => null]
            );
            LmsAssignment::create([
                'lms_category_id' => $category->id,
                'title' => "Pre-Promotion Course — {$user->name} {$tag} PRE",
                'description' => "Pre-Promotion otomatis: predikat {$report->grade} pada {$report->year} S{$report->semester} untuk {$user->name}. Persiapan naik grade/jabatan.",
                'instructions' => "Pelajari leadership & SOP level atas, selesaikan studi kasus outlet. Batas 21 hari. {$tag} PRE",
                'due_at' => now()->addDays(21),
                'max_score' => 100,
                'max_attempts' => 2,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Eksekusi rekomendasi gaji & PKWTT dari raport. Hormati freeze promotion (SP1 aktif).
     * - salary_raise (A2, 2x A): +10% base_salary via SalarySetting baru efektif bulan depan
     * - pkwtt_eligible (A2): konversi PKWT aktif → PKWTT via ContractAlertService
     * Idempoten — cek recommendation.executed_at, skip bila sudah dieksekusi.
     *
     * @return array{skipped:bool, already_executed:bool, salary_raise_applied:bool, pkwtt_converted:bool, message:string, blocked_by_freeze:bool}
     */
    public function executeRecommendation(SemesterReport $report, ?User $executor = null): array
    {
        $report->loadMissing(['user.position', 'user.department']);
        $user = $report->user;
        $rec = $report->recommendation ?? [];

        if (! empty($rec['executed_at'])) {
            return [
                'skipped' => true,
                'already_executed' => true,
                'salary_raise_applied' => false,
                'pkwtt_converted' => false,
                'blocked_by_freeze' => false,
                'message' => 'Rekomendasi sudah dieksekusi pada ' . $rec['executed_at'] . '.',
            ];
        }

        $isFrozen = ! empty($rec['blocked_by_freeze']) || ! empty($rec['promotion_frozen']);
        // Double-check live freeze (SP1 bisa muncul setelah generate)
        if (! $isFrozen && \App\Models\DisciplinaryAction::isPromotionFrozen($user->id)) {
            $isFrozen = true;
        }

        $salaryRaise = ! empty($rec['salary_raise']) && ! $isFrozen;
        $pkwttEligible = ! empty($rec['pkwtt_eligible']) && ! $isFrozen;

        if (! $salaryRaise && ! $pkwttEligible) {
            $reason = $isFrozen
                ? 'Diblokir freeze promotion (SP 1 aktif) — tidak ada aksi gaji/PKWTT yang dieksekusi.'
                : 'Tidak ada rekomendasi gaji/PKWTT untuk predikat ' . $report->grade . '.';
            // tetap tandai executed agar tidak spam klik
            $rec['executed_at'] = now()->toIsoString();
            $rec['executed_by'] = $executor?->id ?? auth()->id();
            $rec['execution_result'] = ['skipped' => true, 'reason' => $reason];
            $report->update(['recommendation' => $rec]);

            return [
                'skipped' => true,
                'already_executed' => false,
                'salary_raise_applied' => false,
                'pkwtt_converted' => false,
                'blocked_by_freeze' => $isFrozen,
                'message' => $reason,
            ];
        }

        $salaryApplied = false;
        $pkwttConverted = false;

        \Illuminate\Support\Facades\DB::transaction(function () use ($user, $report, &$rec, $executor, $salaryRaise, $pkwttEligible, &$salaryApplied, &$pkwttConverted) {
            if ($salaryRaise) {
                $current = SalarySetting::forUser($user->id)->active()->first();
                $base = $current ? (float) $current->base_salary : (float) ($user->position?->base_salary ?? 0);
                if ($base <= 0) {
                    $base = 5000000; // fallback bila belum ada setting & posisi tanpa base
                }
                $newBase = round($base * 1.10, 2); // +10%
                $effectiveDate = now()->addMonth()->startOfMonth()->toDateString();

                if ($current) {
                    SalarySetting::where('user_id', $user->id)->where('is_active', true)->update(['is_active' => false]);
                }

                SalarySetting::create([
                    'user_id' => $user->id,
                    'base_salary' => $newBase,
                    'allowances' => $current?->allowances ?? [],
                    'overtime_rate' => $current?->overtime_rate ?? 1.5,
                    'effective_date' => $effectiveDate,
                    'is_active' => true,
                ]);
                $salaryApplied = true;
            }

            if ($pkwttEligible) {
                $contract = EmploymentContract::query()
                    ->where('user_id', $user->id)
                    ->where('status', EmploymentContract::STATUS_ACTIVE)
                    ->where('type', EmploymentContract::TYPE_PKWT)
                    ->orderByDesc('id')
                    ->first();

                if ($contract) {
                    $this->contractService->convertToPkwtt($contract, $executor ?? auth()->user());
                    // sinkron users agar /employees/{id}/edit ikut berubah
                    $user->update([
                        'contract_type' => EmploymentContract::TYPE_PKWTT,
                        'contract_end_date' => null,
                    ]);
                    $pkwttConverted = true;
                }
            }

            $rec['executed_at'] = now()->toIsoString();
            $rec['executed_by'] = $executor?->id ?? auth()->id();
            $rec['execution_result'] = [
                'salary_raise_applied' => $salaryApplied,
                'pkwtt_converted' => $pkwttConverted,
            ];
            $report->update(['recommendation' => $rec]);
        });

        $parts = [];
        if ($salaryApplied) $parts[] = 'Kenaikan gaji +10% (efektif bulan depan)';
        if ($pkwttConverted) $parts[] = 'Konversi PKWT → PKWTT';
        $msg = $parts ? implode(' + ', $parts) . ' berhasil dieksekusi.' : 'Tidak ada aksi yang dieksekusi.';

        return [
            'skipped' => false,
            'already_executed' => false,
            'salary_raise_applied' => $salaryApplied,
            'pkwtt_converted' => $pkwttConverted,
            'blocked_by_freeze' => $isFrozen,
            'message' => $msg,
        ];
    }

    /**
     * Batas periode semester: Sem I = Jan–Jun, Sem II = Jul–Des.
     *
     * @return array{\DateTimeImmutable, \DateTimeImmutable}
     */
    public function getPeriodBounds(int $year, int $semester): array
    {
        if ($semester === 1) {
            return [
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $year . '-01-01 00:00:00'),
                \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $year . '-06-30 23:59:59'),
            ];
        }

        return [
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $year . '-07-01 00:00:00'),
            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $year . '-12-31 23:59:59'),
        ];
    }
}
