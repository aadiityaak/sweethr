<?php

namespace Database\Seeders;

use App\Models\LmsAssignment;
use App\Models\LmsCategory;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Illuminate\Database\Seeder;

class PipAndPrePromotionSeeder extends Seeder
{
    public function run(): void
    {
        $pipCategory = LmsCategory::firstOrCreate(
            ['parent_id' => null, 'name' => 'Program PIP'],
            ['is_active' => true, 'visible_roles' => null]
        );

        $preCategory = LmsCategory::firstOrCreate(
            ['parent_id' => null, 'name' => 'Pre-Promotion Course'],
            ['is_active' => true, 'visible_roles' => null]
        );

        // === PIP: Rencana PIP 30 Hari ===
        $pipAssignment = LmsAssignment::updateOrCreate(
            ['title' => 'Rencana PIP 30 Hari — Perbaikan Kinerja'],
            [
                'lms_category_id' => $pipCategory->id,
                'description' => 'Performance Improvement Plan 30 hari untuk karyawan predikat D. Wajib diselesaikan dalam 30 hari sejak penugasan.',
                'instructions' => '<ol><li>Susun target perbaikan mingguan bersama atasan.</li><li>Laporkan progres mingguan (Coaching Mingguan).</li><li>Upload bukti capaian di akhir periode 30 hari.</li></ol>',
                'due_at' => now()->addDays(30),
                'max_score' => 100,
                'max_attempts' => 3,
                'is_active' => true,
            ]
        );

        LmsAssignment::updateOrCreate(
            ['title' => 'Coaching Mingguan — Laporan Progress PIP'],
            [
                'lms_category_id' => $pipCategory->id,
                'description' => 'Laporan coaching mingguan selama PIP.',
                'instructions' => '<p>Tuliskan capaian minggu ini, kendala, dan rencana minggu depan. Dinilai oleh SPV/HR.</p>',
                'due_at' => now()->addDays(7),
                'max_score' => 100,
                'max_attempts' => 4,
                'is_active' => true,
            ]
        );

        $pipQuiz = LmsQuiz::updateOrCreate(
            ['title' => 'Evaluasi PIP — Pemahaman Target Perbaikan'],
            [
                'lms_category_id' => $pipCategory->id,
                'description' => '<p>Evaluasi pemahaman target PIP dan komitmen perbaikan.</p>',
                'time_limit_minutes' => 15,
                'passing_score' => 70,
                'max_attempts' => 3,
                'is_active' => true,
            ]
        );

        $pipQuestions = [
            [
                'type' => 'multiple_choice',
                'question' => '<p>Tujuan utama PIP adalah…</p>',
                'options' => ['Menghukum karyawan', 'Memberi kesempatan terstruktur untuk memperbaiki kinerja', 'Menunda promosi', 'Mengurangi gaji'],
                'correct_answer' => ['value' => 1],
                'points' => 2,
            ],
            [
                'type' => 'true_false',
                'question' => '<p>Selama PIP, karyawan wajib melapor progres mingguan ke atasan.</p>',
                'options' => null,
                'correct_answer' => ['value' => true],
                'points' => 1,
            ],
        ];

        foreach ($pipQuestions as $order => $q) {
            LmsQuizQuestion::updateOrCreate(
                ['lms_quiz_id' => $pipQuiz->id, 'order' => $order],
                [
                    'type' => $q['type'],
                    'question' => $q['question'],
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'points' => $q['points'],
                    'is_active' => true,
                ]
            );
        }

        // === Pre-Promotion Course ===
        $preAssignment = LmsAssignment::updateOrCreate(
            ['title' => 'Pre-Promotion Course — Kesiapan Naik Grade'],
            [
                'lms_category_id' => $preCategory->id,
                'description' => 'Modul persiapan promosi untuk karyawan predikat A/B. Menyelesaikan modul ini memperkuat kelayakan promosi grade/jabatan.',
                'instructions' => '<ol><li>Pelajari materi leadership & SOP level atas.</li><li>Selesaikan tugas studi kasus outlet.</li><li>Ikuti evaluasi Pre-Promotion Quiz.</li></ol>',
                'due_at' => now()->addDays(21),
                'max_score' => 100,
                'max_attempts' => 2,
                'is_active' => true,
            ]
        );

        $preQuiz = LmsQuiz::updateOrCreate(
            ['title' => 'Pre-Promotion Quiz — Leadership & SOP Outlet'],
            [
                'lms_category_id' => $preCategory->id,
                'description' => '<p>Uji kesiapan promosi: leadership, SOP, dan manajemen outlet.</p>',
                'time_limit_minutes' => 15,
                'passing_score' => 75,
                'max_attempts' => 2,
                'is_active' => true,
            ]
        );

        $preQuestions = [
            [
                'type' => 'multiple_choice',
                'question' => '<p>Saat memimpin shift, prioritas pertama seorang calon SPV adalah…</p>',
                'options' => ['Menyuruh tim bekerja tanpa briefing', 'Briefing shift + pembagian tugas jelas', 'Menunggu instruksi atasan terus', 'Fokus kasir saja'],
                'correct_answer' => ['value' => 1],
                'points' => 2,
            ],
            [
                'type' => 'true_false',
                'question' => '<p>Calon promosi harus memahami P&L outlet dan food cost.</p>',
                'options' => null,
                'correct_answer' => ['value' => true],
                'points' => 1,
            ],
        ];

        foreach ($preQuestions as $order => $q) {
            LmsQuizQuestion::updateOrCreate(
                ['lms_quiz_id' => $preQuiz->id, 'order' => $order],
                [
                    'type' => $q['type'],
                    'question' => $q['question'],
                    'options' => $q['options'],
                    'correct_answer' => $q['correct_answer'],
                    'points' => $q['points'],
                    'is_active' => true,
                ]
            );
        }
    }
}
