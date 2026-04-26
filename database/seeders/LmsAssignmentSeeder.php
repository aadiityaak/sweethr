<?php

namespace Database\Seeders;

use App\Models\LmsAssignment;
use App\Models\LmsCategory;
use Illuminate\Database\Seeder;

class LmsAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $leafCategories = LmsCategory::query()
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->get();

        if ($leafCategories->isEmpty()) {
            $fallback = LmsCategory::firstOrCreate(
                ['parent_id' => null, 'name' => 'Modul'],
                ['is_active' => true],
            );

            $leafCategories = collect([$fallback]);
        }

        $items = [
            [
                'title' => 'Checklist Kebersihan Harian',
                'description' => 'Buat checklist kebersihan area kerja dan peralatan untuk 1 shift.',
                'instructions' => '<ol><li>Susun checklist minimal 10 poin.</li><li>Tambahkan siapa PIC dan frekuensi.</li><li>Upload sebagai teks di kolom jawaban.</li></ol>',
                'due_days' => 7,
                'max_score' => 100,
            ],
            [
                'title' => 'Simulasi Handling Komplain Pelanggan',
                'description' => 'Tuliskan langkah-langkah menangani komplain dengan bahasa yang sopan dan efektif.',
                'instructions' => '<p>Tuliskan:</p><ul><li>Kalimat pembuka</li><li>Empati</li><li>Solusi</li><li>Penutup</li></ul>',
                'due_days' => 10,
                'max_score' => 100,
            ],
            [
                'title' => 'SOP Packing Anti Tumpah',
                'description' => 'Buat SOP singkat untuk packing makanan/minuman agar tidak tumpah saat delivery.',
                'instructions' => '<p>Sertakan bahan kemasan, urutan packing, dan quality check sebelum driver berangkat.</p>',
                'due_days' => 14,
                'max_score' => 100,
            ],
        ];

        foreach ($items as $index => $item) {
            $categoryId = $leafCategories[$index % $leafCategories->count()]->id;

            LmsAssignment::updateOrCreate(
                ['title' => $item['title']],
                [
                    'lms_category_id' => $categoryId,
                    'description' => $item['description'],
                    'instructions' => $item['instructions'],
                    'due_at' => now()->addDays($item['due_days']),
                    'max_score' => $item['max_score'],
                    'is_active' => true,
                ],
            );
        }
    }
}
