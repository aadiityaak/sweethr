<?php

namespace Database\Seeders;

use App\Models\AnnouncementCategory;
use Illuminate\Database\Seeder;

class AnnouncementCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Umum',
                'slug' => 'umum',
                'color' => 'blue',
                'icon' => 'megaphone',
                'description' => 'Pengumuman umum untuk semua karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Event',
                'slug' => 'event',
                'color' => 'purple',
                'icon' => 'calendar-days',
                'description' => 'Acara dan kegiatan perusahaan',
                'is_active' => true,
            ],
            [
                'name' => 'Penting',
                'slug' => 'penting',
                'color' => 'red',
                'icon' => 'alert-triangle',
                'description' => 'Pengumuman penting yang memerlukan perhatian',
                'is_active' => true,
            ],
            [
                'name' => 'Kebijakan',
                'slug' => 'kebijakan',
                'color' => 'amber',
                'icon' => 'file-text',
                'description' => 'Perubahan kebijakan dan prosedur',
                'is_active' => true,
            ],
            [
                'name' => 'Ulang Tahun',
                'slug' => 'ulang-tahun',
                'color' => 'green',
                'icon' => 'cake',
                'description' => 'Ucapan ulang tahun karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Prestasi',
                'slug' => 'prestasi',
                'color' => 'yellow',
                'icon' => 'trophy',
                'description' => 'Pencapaian dan penghargaan karyawan',
                'is_active' => true,
            ],
            [
                'name' => 'Jadwal',
                'slug' => 'jadwal',
                'color' => 'indigo',
                'icon' => 'clock',
                'description' => 'Perubahan jadwal dan shift',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            AnnouncementCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
