<?php

namespace Database\Seeders;

use App\Models\LmsPerformanceAppraisal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LmsPerformanceAppraisalSeeder extends Seeder
{
    public function run(): void
    {
        $evaluatorId = User::query()->where('is_admin', true)->value('id');

        if (! $evaluatorId) {
            return;
        }

        $employees = User::query()
            ->where('is_admin', false)
            ->orderBy('id')
            ->limit(12)
            ->get(['id']);

        if ($employees->isEmpty()) {
            return;
        }

        $templates = [
            '<p><strong>Kekuatan:</strong> Konsisten menjaga kualitas kerja dan cepat belajar SOP baru.</p><p><strong>Area perbaikan:</strong> Tingkatkan komunikasi saat jam ramai agar koordinasi lebih rapi.</p><p><strong>Rencana tindak lanjut:</strong> Review SOP mingguan dan mentoring singkat 10 menit sebelum shift.</p>',
            '<p><strong>Kekuatan:</strong> Disiplin dan tanggung jawab tinggi, kontribusi tim baik.</p><p><strong>Area perbaikan:</strong> Manajemen waktu saat multi-tasking perlu ditingkatkan.</p><p><strong>Rencana tindak lanjut:</strong> Prioritaskan tugas dengan checklist harian.</p>',
            '<p><strong>Kekuatan:</strong> Inisiatif bagus, proaktif membantu rekan kerja.</p><p><strong>Area perbaikan:</strong> Perlu lebih teliti pada detail agar hasil makin rapi.</p><p><strong>Rencana tindak lanjut:</strong> Terapkan double-check sebelum submit pekerjaan.</p>',
        ];

        $dates = [
            Carbon::now()->subMonthsNoOverflow(2)->startOfMonth()->addDays(7)->toDateString(),
            Carbon::now()->subMonthsNoOverflow(1)->startOfMonth()->addDays(7)->toDateString(),
            Carbon::now()->startOfMonth()->addDays(7)->toDateString(),
        ];

        foreach ($employees as $i => $employee) {
            $isManager = User::query()
                ->whereKey($employee->id)
                ->whereHas('subordinates')
                ->exists();

            $appraisalsToCreate = $i % 3 === 0 ? 2 : 1;
            for ($j = 0; $j < $appraisalsToCreate; $j++) {
                $evaluatedAt = $dates[($i + $j) % count($dates)];
                $base = 3 + (($i + $j) % 3);

                $payload = [
                    'user_id' => $employee->id,
                    'evaluator_id' => $evaluatorId,
                    'evaluated_at' => $evaluatedAt,
                    'quality_work' => min(5, max(1, $base)),
                    'quantity_work' => min(5, max(1, $base - 1)),
                    'task_knowledge' => min(5, max(1, $base)),
                    'discipline' => min(5, max(1, $base)),
                    'teamwork' => min(5, max(1, $base)),
                    'communication' => min(5, max(1, $base - 1)),
                    'initiative' => min(5, max(1, $base)),
                    'target_realization' => min(5, max(1, $base - 1)),
                    'time_management' => min(5, max(1, $base - 1)),
                    'attitude' => min(5, max(1, $base)),
                    'adaptability' => min(5, max(1, $base)),
                    'leadership_delegation' => $isManager ? min(5, max(1, $base)) : null,
                    'leadership_development' => $isManager ? min(5, max(1, $base - 1)) : null,
                    'feedback' => $templates[($i + $j) % count($templates)],
                ];

                LmsPerformanceAppraisal::updateOrCreate(
                    [
                        'user_id' => $employee->id,
                        'evaluated_at' => $evaluatedAt,
                    ],
                    $payload
                );
            }
        }
    }
}

