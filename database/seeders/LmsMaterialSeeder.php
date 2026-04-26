<?php

namespace Database\Seeders;

use App\Models\LmsCategory;
use App\Models\LmsMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LmsMaterialSeeder extends Seeder
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
                'title' => 'Standar Kebersihan Dapur (Hygiene & Sanitasi)',
                'description' => '<p>Materi ini membahas standar kebersihan dapur, sanitasi, dan checklist harian untuk menjaga kualitas produk.</p><ul><li>Personal hygiene</li><li>Area kerja & peralatan</li><li>Cross-contamination</li></ul>',
                'file_ext' => 'pdf',
            ],
            [
                'title' => 'Teknik Dasar Memasak Ayam (Marinasi & Penggorengan)',
                'description' => '<p>Ringkasan teknik marinasi, tepung, hingga proses penggorengan agar matang merata dan crispy.</p>',
                'file_ext' => 'pdf',
            ],
            [
                'title' => 'Service Excellence untuk Frontliner',
                'description' => '<p>Panduan standar pelayanan: cara menyapa, komunikasi efektif, dan handling komplain.</p>',
                'file_ext' => 'pdf',
            ],
            [
                'title' => 'Manajemen Order Online (GoFood/GrabFood)',
                'description' => '<p>Alur kerja saat order ramai, prioritas, dan SOP packing agar tidak tumpah.</p>',
                'file_ext' => 'pdf',
            ],
            [
                'title' => 'FIFO & Manajemen Stok Gudang',
                'description' => '<p>Konsep FIFO, pencatatan bahan masuk/keluar, dan kontrol waste.</p>',
                'file_ext' => 'pdf',
            ],
            [
                'title' => 'Konsistensi Rasa (Quality Control)',
                'description' => '<p>Standar rasa & tekstur, cara sampling, dan tindakan koreksi bila terjadi deviasi.</p>',
                'file_ext' => 'pdf',
            ],
        ];

        foreach ($items as $index => $item) {
            $categoryId = $leafCategories[$index % $leafCategories->count()]->id;
            $slug = Str::slug($item['title']);
            $filePath = "lms/materials/{$slug}.{$item['file_ext']}";

            LmsMaterial::updateOrCreate(
                ['title' => $item['title']],
                [
                    'description' => $item['description'],
                    'lms_category_id' => $categoryId,
                    'file_path' => $filePath,
                    'thumbnail_path' => null,
                    'is_active' => true,
                ],
            );
        }
    }
}
