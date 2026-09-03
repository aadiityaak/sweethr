<?php

namespace App\Services;

use App\Models\DisciplinaryAction;
use App\Models\SemesterReport;
use App\Models\User;

class RewardRecommendationService
{
    /**
     * Bangun rekomendasi reward & karir berdasarkan raport semester.
     * Matriks: A 2x beruntun / A-B 1 semester / C / D / E.
     * Freeze promotion (SP 1 aktif) memblokir rekomendasi promosi.
     */
    public function buildRecommendation(User $user, SemesterReport $report, int $year, int $semester): array
    {
        $isFrozen = DisciplinaryAction::isPromotionFrozen($user->id);
        $consecutiveA = $this->hasConsecutiveA($user, $year, $semester);
        $previousGrade = $this->getPreviousGrade($user, $year, $semester);

        $base = match (true) {
            $report->grade === SemesterReport::GRADE_A && $consecutiveA => $this->matrixATwice(),
            in_array($report->grade, [SemesterReport::GRADE_A, SemesterReport::GRADE_B], true) => $this->matrixAB(),
            $report->grade === SemesterReport::GRADE_C => $this->matrixC(),
            $report->grade === SemesterReport::GRADE_D => $this->matrixD(),
            default => $this->matrixE(),
        };

        if ($isFrozen && ($base['promotion_recommended'] || ($base['pkwtt_eligible'] ?? false))) {
            $base['promotion_recommended'] = false;
            $base['pkwtt_eligible'] = false;
            $base['blocked_by_freeze'] = true;
            $base['warnings'][] = 'Rekomendasi promosi diblokir: pembekuan promosi aktif (SP 1).';
        }

        $base['previous_grade'] = $previousGrade;
        $base['consecutive_a'] = $consecutiveA;
        $base['promotion_frozen'] = $isFrozen;

        return $base;
    }

    private function matrixATwice(): array
    {
        return [
            'tier' => 'A2',
            'label' => 'Predikat A (2 Semester Beruntun)',
            'salary_raise' => true,
            'bonus' => 'Bonus Insentif Outstanding Performance',
            'promotion_recommended' => true,
            'pkwtt_eligible' => true,
            'priority' => 'Prioritas Utama Promosi Grade / Jabatan + Opsi Pengangkatan PKWTT',
            'warnings' => [],
        ];
    }

    private function matrixAB(): array
    {
        return [
            'tier' => 'AB',
            'label' => 'Predikat A / B (1 Semester)',
            'salary_raise' => false,
            'bonus' => 'Bonus Insentif Kinerja Semesteran',
            'promotion_recommended' => true,
            'pkwtt_eligible' => false,
            'priority' => 'Rekomendasi Promosi Grade (Level Upskilling) + Pre-Promotion Course',
            'warnings' => [],
        ];
    }

    private function matrixC(): array
    {
        return [
            'tier' => 'C',
            'label' => 'Predikat C (Standar)',
            'salary_raise' => false,
            'bonus' => 'Insentif Normal sesuai KPI',
            'promotion_recommended' => false,
            'pkwtt_eligible' => false,
            'priority' => 'Perpanjangan Kontrak PKWT Standar (Tanpa Kenaikan Grade)',
            'warnings' => [],
        ];
    }

    private function matrixD(): array
    {
        return [
            'tier' => 'D',
            'label' => 'Predikat D (Dibawah Standar)',
            'salary_raise' => false,
            'bonus' => null,
            'promotion_recommended' => false,
            'pkwtt_eligible' => false,
            'priority' => 'Pembekuan Promosi (Freeze Promotion) + Masuk Program PIP',
            'pip_required' => true,
            'warnings' => [],
        ];
    }

    private function matrixE(): array
    {
        return [
            'tier' => 'E',
            'label' => 'Predikat E (Tidak Memenuhi)',
            'salary_raise' => false,
            'bonus' => null,
            'promotion_recommended' => false,
            'pkwtt_eligible' => false,
            'priority' => 'Evaluasi Non-Perpanjangan Kontrak PKWT / Pertimbangan Demosi atau PHK',
            'exit_review' => true,
            'warnings' => [],
        ];
    }

    /**
     * Cek apakah raport semester ini + semester sebelumnya sama-sama predikat A.
     */
    private function hasConsecutiveA(User $user, int $year, int $semester): bool
    {
        if ($semester === 1) {
            $prev = SemesterReport::query()
                ->where('user_id', $user->id)
                ->where('year', $year - 1)
                ->where('semester', '2')
                ->first();
        } else {
            $prev = SemesterReport::query()
                ->where('user_id', $user->id)
                ->where('year', $year)
                ->where('semester', '1')
                ->first();
        }

        return $prev !== null && $prev->grade === SemesterReport::GRADE_A;
    }

    private function getPreviousGrade(User $user, int $year, int $semester): ?string
    {
        $query = SemesterReport::query()->where('user_id', $user->id);

        if ($semester === 1) {
            $prev = $query->where('year', $year - 1)->where('semester', '2')->first();
        } else {
            $prev = $query->where('year', $year)->where('semester', '1')->first();
        }

        return $prev?->grade;
    }
}
