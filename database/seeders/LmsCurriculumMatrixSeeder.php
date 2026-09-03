<?php

namespace Database\Seeders;

use App\Models\LmsAssignment;
use App\Models\LmsCategory;
use App\Models\LmsCurriculumMatrix;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\Position;
use Illuminate\Database\Seeder;

class LmsCurriculumMatrixSeeder extends Seeder
{
    public function run(): void
    {
        $positions = Position::all()->keyBy('code');

        if ($positions->isEmpty()) {
            $this->command?->warn('Position kosong, jalankan PositionSeeder dulu.');
            return;
        }

        $materials = LmsMaterial::orderBy('id')->get()->keyBy('title');
        $quizzes = LmsQuiz::orderBy('id')->get()->keyBy('title');
        $assignments = LmsAssignment::orderBy('id')->get()->keyBy('title');

        if ($materials->isEmpty() || $quizzes->isEmpty() || $assignments->isEmpty()) {
            $this->command?->warn('LMS konten kosong, jalankan LmsCategorySeeder + LmsMaterialSeeder + LmsQuizSeeder + LmsAssignmentSeeder dulu.');
            return;
        }

        // Helper: ambil category id dari item (fallback null)
        $catOf = fn ($model) => $model?->lms_category_id;

        // Definisi matriks kurikulum per position (untuk demo/test)
        // Tiap entri: [position_code, item_type, title_key, is_mandatory, deadline_days]
        $matrix = [
            // === Operations Manager — butuh semua operasional ===
            ['OPM001', 'material', 'Standar Kebersihan Dapur (Hygiene & Sanitasi)', true, 14],
            ['OPM001', 'material', 'Konsistensi Rasa (Quality Control)', true, 21],
            ['OPM001', 'material', 'FIFO & Manajemen Stok Gudang', true, 14],
            ['OPM001', 'quiz', 'Kuis Hygiene & Sanitasi', true, 14],
            ['OPM001', 'assignment', 'Checklist Kebersihan Harian', true, 7],
            ['OPM001', 'assignment', 'SOP Packing Anti Tumpah', false, 21],

            // === HR Manager & HR Staff — disiplin & service ===
            ['HRM001', 'material', 'Service Excellence untuk Frontliner', true, 30],
            ['HRM001', 'material', 'Konsistensi Rasa (Quality Control)', false, 30],
            ['HRM001', 'quiz', 'Kuis Service Excellence', true, 14],
            ['HRM001', 'assignment', 'Simulasi Handling Komplain Pelanggan', true, 10],
            ['HRS001', 'material', 'Service Excellence untuk Frontliner', true, 21],
            ['HRS001', 'quiz', 'Kuis Service Excellence', true, 21],
            ['HRS001', 'assignment', 'Simulasi Handling Komplain Pelanggan', true, 14],

            // === IT — fokus delivery & packing SOP ===
            ['ITM001', 'material', 'Manajemen Order Online (GoFood/GrabFood)', false, 30],
            ['ITM001', 'assignment', 'SOP Packing Anti Tumpah', false, 30],
            ['DEV001', 'material', 'Manajemen Order Online (GoFood/GrabFood)', true, 21],
            ['DEV001', 'material', 'FIFO & Manajemen Stok Gudang', false, 30],
            ['DEV001', 'assignment', 'SOP Packing Anti Tumpah', true, 14],

            // === Finance — stok & QC ===
            ['FNM001', 'material', 'FIFO & Manajemen Stok Gudang', true, 14],
            ['FNM001', 'material', 'Konsistensi Rasa (Quality Control)', true, 21],
            ['FNM001', 'quiz', 'Kuis Hygiene & Sanitasi', false, 30],
            ['ACC001', 'material', 'FIFO & Manajemen Stok Gudang', true, 14],
            ['ACC001', 'assignment', 'Checklist Kebersihan Harian', false, 30],

            // === Marketing — service & packing ===
            ['MKM001', 'material', 'Service Excellence untuk Frontliner', true, 14],
            ['MKM001', 'material', 'Manajemen Order Online (GoFood/GrabFood)', true, 14],
            ['MKM001', 'quiz', 'Kuis Service Excellence', true, 14],
            ['MKM001', 'assignment', 'Simulasi Handling Komplain Pelanggan', true, 14],
            ['MKS001', 'material', 'Service Excellence untuk Frontliner', true, 14],
            ['MKS001', 'quiz', 'Kuis Service Excellence', true, 21],
            ['MKS001', 'assignment', 'Simulasi Handling Komplain Pelanggan', false, 21],

            // === Sales — service & order ===
            ['SLM001', 'material', 'Service Excellence untuk Frontliner', true, 14],
            ['SLM001', 'material', 'Manajemen Order Online (GoFood/GrabFood)', true, 14],
            ['SLM001', 'material', 'Teknik Dasar Memasak Ayam (Marinasi & Penggorengan)', false, 30],
            ['SLM001', 'quiz', 'Kuis Service Excellence', true, 14],
            ['SLM001', 'assignment', 'Simulasi Handling Komplain Pelanggan', true, 7],

            // === Dapur spesifik — skill masak ===
            ['OPM001', 'material', 'Teknik Dasar Memasak Ayam (Marinasi & Penggorengan)', true, 7],
            ['HRS001', 'material', 'Standar Kebersihan Dapur (Hygiene & Sanitasi)', true, 7],
            ['HRS001', 'quiz', 'Kuis Hygiene & Sanitasi', true, 14],

            // === WARUNG MAS MBULL OUTLET (PDF Matriks Kurikulum) ===
            // Cleaner — SOP Sanitasi & Checklist Kebersihan
            ['CLN001', 'material', 'SOP Sanitasi & Kebersihan Outlet', true, 7],
            ['CLN001', 'material', 'Standar Kebersihan Dapur (Hygiene & Sanitasi)', true, 7],
            ['CLN001', 'assignment', 'Checklist Kebersihan Harian', true, 7],

            // Pramusaji — Customer Service + Product Knowledge + Complaint Handling
            ['PRM001', 'material', 'Service Excellence untuk Frontliner', true, 7],
            ['PRM001', 'material', 'Product Knowledge Menu Ayam Penyet & Paket Sambal', true, 7],
            ['PRM001', 'quiz', 'Kuis Alur Pelayanan & Greeting Pramusaji', true, 7],
            ['PRM001', 'quiz', 'Kuis Service Excellence', true, 14],
            ['PRM001', 'assignment', 'Simulasi Handling Komplain Pelanggan', true, 10],

            // Kasir — SOP Kasir & POS + Audit Harian
            ['KSR001', 'material', 'SOP Kasir & Sistem POS', true, 7],
            ['KSR001', 'quiz', 'Ujian Teori POS Kasir', true, 7],
            ['KSR001', 'assignment', 'Audit Kasir Harian — Rekap Selisih Kas', true, 7],

            // Koki — Standard Recipe + Food Safety + Cooking Test
            ['KOK001', 'material', 'Standard Recipe — Marinasi & Sambal Level 1-10', true, 7],
            ['KOK001', 'material', 'Food Safety & Higienitas Outlet', true, 7],
            ['KOK001', 'material', 'Teknik Dasar Memasak Ayam (Marinasi & Penggorengan)', true, 7],
            ['KOK001', 'quiz', 'Kuis Food Safety & FIFO Koki', true, 7],
            ['KOK001', 'quiz', 'Kuis Cooking Test Standar Rasa & Visual', true, 7],
            ['KOK001', 'assignment', 'Cooking Test — Standar Rasa & Visual', true, 7],

            // Ast SPV — Pengawasan & Stock Opname
            ['ASP001', 'material', 'Pengawasan Operasional & Briefing Shift', true, 7],
            ['ASP001', 'material', 'Manajemen Stok & Stock Opname Outlet', true, 7],
            ['ASP001', 'material', 'Food Safety & Higienitas Outlet', false, 14],
            ['ASP001', 'assignment', 'Audit Checklist Shift (Ast SPV)', true, 7],

            // SPV Outlet — Manajemen P&L & Food Cost
            ['SPV001', 'material', 'Manajemen Outlet & Pencapaian Target (P&L)', true, 14],
            ['SPV001', 'material', 'Food Cost & Waste Control Outlet', true, 14],
            ['SPV001', 'material', 'Pengawasan Operasional & Briefing Shift', true, 14],
            ['SPV001', 'assignment', 'Laporan P&L Outlet (SPV)', true, 14],
        ];

        $created = 0;
        foreach ($matrix as [$posCode, $type, $title, $mandatory, $deadline]) {
            $position = $positions[$posCode] ?? null;
            if (! $position) {
                continue;
            }

            $payload = [
                'position_id' => $position->id,
                'item_type' => $type,
                'is_mandatory' => $mandatory,
                'deadline_days' => $deadline,
                'lms_category_id' => null,
                'lms_material_id' => null,
                'lms_quiz_id' => null,
                'lms_assignment_id' => null,
            ];

            if ($type === LmsCurriculumMatrix::TYPE_MATERIAL) {
                $m = $materials[$title] ?? null;
                if (! $m) continue;
                $payload['lms_material_id'] = $m->id;
                $payload['lms_category_id'] = $catOf($m);
            } elseif ($type === LmsCurriculumMatrix::TYPE_QUIZ) {
                $q = $quizzes[$title] ?? null;
                if (! $q) continue;
                $payload['lms_quiz_id'] = $q->id;
                $payload['lms_category_id'] = $catOf($q);
            } else {
                $a = $assignments[$title] ?? null;
                if (! $a) continue;
                $payload['lms_assignment_id'] = $a->id;
                $payload['lms_category_id'] = $catOf($a);
            }

            // Idempotent: cek duplikat exact
            $exists = LmsCurriculumMatrix::query()->where([
                'position_id' => $payload['position_id'],
                'item_type' => $payload['item_type'],
                'lms_material_id' => $payload['lms_material_id'],
                'lms_quiz_id' => $payload['lms_quiz_id'],
                'lms_assignment_id' => $payload['lms_assignment_id'],
            ])->exists();

            if (! $exists) {
                LmsCurriculumMatrix::create($payload);
                $created++;
            }
        }

        $this->command?->info("LmsCurriculumMatrixSeeder: {$created} entri baru, total " . LmsCurriculumMatrix::count() . " baris.");
    }
}
