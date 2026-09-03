<?php

namespace App\Services;

use App\Models\EmployeeViolation;
use App\Models\LmsPerformanceAppraisal;
use App\Models\LmsQuizAttempt;
use App\Models\SemesterReport;
use App\Models\User;

class SemesterReportService
{
    public function __construct(
        private RewardRecommendationService $rewardService,
        private DisciplinaryPointService $disciplinaryService,
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
