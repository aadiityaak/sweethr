<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user and categories
        $adminUser = User::where('is_admin', true)->first();
        $categories = AnnouncementCategory::all();

        if (!$adminUser || $categories->isEmpty()) {
            $this->command->warn('Please seed users and announcement categories first.');
            return;
        }

        $announcements = [
            [
                'title' => 'Selamat Datang di Sistem HR!',
                'content' => "Kami dengan bangga memperkenalkan sistem HR management yang baru untuk meningkatkan pengalaman kerja kita semua.\n\nFitur-fitur unggulan:\n• Absensi berbasis lokasi dan face recognition\n• Manajemen cuti yang mudah\n• Dashboard yang informatif\n• Notifikasi real-time\n\nMari bersama-sama membangun lingkungan kerja yang lebih baik!",
                'excerpt' => 'Sistem HR management baru telah diluncurkan dengan fitur-fitur canggih untuk meningkatkan produktivitas dan kenyamanan kerja.',
                'category_id' => $categories->where('slug', 'umum')->first()?->id ?? $categories->first()->id,
                'priority' => 'high',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'published_at' => now()->subDays(1),
            ],
            [
                'title' => 'Pelatihan Face Recognition - Wajib untuk Semua Karyawan',
                'content' => "Dalam rangka meningkatkan keamanan dan akurasi absensi, semua karyawan diwajibkan untuk melakukan setup face recognition.\n\nJadwal pelatihan:\n📅 Senin, 23 September 2024\n⏰ 09:00 - 11:00 WIB\n📍 Ruang Meeting Utama\n\nYang perlu dibawa:\n• ID Card karyawan\n• Smartphone pribadi\n• Notebook/laptop (opsional)\n\nDaftarkan diri Anda segera di HRD atau melalui sistem ini.",
                'excerpt' => 'Pelatihan wajib setup face recognition untuk semua karyawan. Daftar sekarang untuk mengamankan slot Anda!',
                'category_id' => $categories->where('slug', 'penting')->first()?->id ?? $categories->first()->id,
                'priority' => 'urgent',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'published_at' => now()->subHours(12),
            ],
            [
                'title' => 'Company Gathering 2024 - Save the Date!',
                'content' => "🎉 Kami mengundang seluruh keluarga besar perusahaan untuk menghadiri Company Gathering tahunan!\n\n📅 Sabtu, 15 Oktober 2024\n⏰ 08:00 - 17:00 WIB\n📍 Pantai Ancol, Jakarta Utara\n\nAgenda acara:\n• Welcome breakfast (08:00 - 09:00)\n• Team building activities (09:00 - 12:00)\n• BBQ lunch (12:00 - 14:00)\n• Games & door prizes (14:00 - 16:00)\n• Closing ceremony (16:00 - 17:00)\n\nSetiap karyawan boleh membawa keluarga (maks. 2 orang). Konfirmasi kehadiran paling lambat 1 Oktober 2024.",
                'excerpt' => 'Company Gathering 2024 di Pantai Ancol! Acara seru dengan keluarga besar perusahaan. Jangan sampai terlewat!',
                'category_id' => $categories->where('slug', 'event')->first()?->id ?? $categories->first()->id,
                'priority' => 'normal',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'published_at' => now()->subHours(6),
            ],
            [
                'title' => 'Selamat Ulang Tahun Budi Santoso! 🎂',
                'content' => "Mari kita ucapkan selamat ulang tahun kepada Budi Santoso dari divisi IT yang berulang tahun hari ini!\n\n🎂 Happy Birthday Budi!\n\nTerima kasih atas dedikasi dan kontribusi yang luar biasa selama ini. Semoga di usia yang baru ini semakin sukses dan bahagia.\n\nDari kami semua keluarga besar perusahaan! 🎉",
                'excerpt' => 'Selamat ulang tahun untuk Budi Santoso! Mari doakan yang terbaik untuk rekan kerja kita.',
                'category_id' => $categories->where('slug', 'ulang-tahun')->first()?->id ?? $categories->first()->id,
                'priority' => 'low',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'published_at' => now()->subHours(3),
            ],
            [
                'title' => 'Perubahan Kebijakan Work From Home',
                'content' => "Effective tanggal 1 Oktober 2024, kami memperbarui kebijakan Work From Home (WFH) sebagai berikut:\n\n📋 Ketentuan Baru:\n• WFH maksimal 2 hari per minggu\n• Harus mendapat persetujuan supervisor\n• Laporan harian wajib dikirim EOD\n• Meeting wajib via video call\n• Tetap absen melalui sistem (lokasi khusus WFH)\n\n⚠️ Pelanggaran kebijakan akan dikenakan sanksi sesuai peraturan perusahaan.\n\nUntuk pertanyaan lebih lanjut, hubungi HRD.",
                'excerpt' => 'Pembaruan kebijakan WFH berlaku mulai 1 Oktober 2024. Pastikan Anda memahami ketentuan terbaru.',
                'category_id' => $categories->where('slug', 'kebijakan')->first()?->id ?? $categories->first()->id,
                'priority' => 'high',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'published_at' => now()->subHours(1),
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::updateOrCreate(
                ['title' => $announcementData['title']],
                $announcementData
            );
        }

        $this->command->info('Sample announcements created successfully!');
    }
}
