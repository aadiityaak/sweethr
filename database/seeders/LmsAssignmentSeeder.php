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
            // === Outlet praktikum (PDF Warung Mas Mbull) ===
            [
                'title' => 'Audit Kasir Harian — Rekap Selisih Kas',
                'description' => 'Praktikum audit kas harian untuk posisi Kasir.',
                'instructions' => '<p>Rekap transaksi POS 1 shift, hitung selisih kas, dan jelaskan tindak lanjut jika selisih &gt;0.</p>',
                'due_days' => 7,
                'max_score' => 100,
            ],
            [
                'title' => 'Cooking Test — Standar Rasa & Visual',
                'description' => 'Praktikum memasak ayam penyet sesuai standard recipe.',
                'instructions' => '<p>Foto hasil masakan + checklist rasa, kematangan, dan plating. Dinilai SPV/Koki senior.</p>',
                'due_days' => 7,
                'max_score' => 100,
            ],
            [
                'title' => 'Audit Checklist Shift (Ast SPV)',
                'description' => 'Checklist pengawasan operasional 1 shift untuk Ast SPV.',
                'instructions' => '<p>Susun checklist briefing, kebersihan, stok, dan closing. Upload laporan shift.</p>',
                'due_days' => 7,
                'max_score' => 100,
            ],
            [
                'title' => 'Laporan P&L Outlet (SPV)',
                'description' => 'Laporan pencapaian target & P&L mingguan outlet untuk SPV.',
                'instructions' => '<p>Hitung food cost, waste, dan pencapaian target. Lampirkan file rekap.</p>',
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
