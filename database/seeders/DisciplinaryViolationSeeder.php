<?php

namespace Database\Seeders;

use App\Models\DisciplinaryViolation;
use Illuminate\Database\Seeder;

class DisciplinaryViolationSeeder extends Seeder
{
    /**
     * Master kategori pelanggaran sesuai matriks rancangan HR.
     */
    public function run(): void
    {
        $items = [
            // === PELANGGARAN RINGAN (5–10 poin) ===
            [
                'code' => 'LT-01',
                'name' => 'Datang terlambat kurang dari 15 menit',
                'category' => 'ringan',
                'points' => 5,
                'description' => 'Terlambat masuk kerja < 15 menit dari jadwal shift.',
            ],
            [
                'code' => 'AT-01',
                'name' => 'Atribut seragam tidak lengkap / tidak rapi',
                'category' => 'ringan',
                'points' => 5,
                'description' => 'Seragam, name tag, atau atribut kerja tidak sesuai standar.',
            ],
            [
                'code' => 'KB-01',
                'name' => 'Area kerja kurang bersih setelah closing',
                'category' => 'ringan',
                'points' => 10,
                'description' => 'Check-list kebersihan closing tidak tuntas.',
            ],

            // === PELANGGARAN SEDANG (15–25 poin) ===
            [
                'code' => 'LT-02',
                'name' => 'Terlambat lebih dari 30 menit tanpa konfirmasi',
                'category' => 'sedang',
                'points' => 15,
                'description' => 'Terlambat > 30 menit tanpa kabar/konfirmasi ke atasan.',
            ],
            [
                'code' => 'TR-01',
                'name' => 'Salah mencatat pesanan / transaksi yang merugikan',
                'category' => 'sedang',
                'points' => 20,
                'description' => 'Kesalahan input order/POS yang menyebabkan kerugian operasional.',
            ],
            [
                'code' => 'FS-01',
                'name' => 'Tidak menjalankan prosedur FIFO',
                'category' => 'sedang',
                'points' => 25,
                'description' => 'Bahan baku rusak/kadaluarsa karena FIFO tidak dijalankan.',
            ],
            [
                'code' => 'FS-02',
                'name' => 'Pelanggaran standar Food Safety ringan',
                'category' => 'sedang',
                'points' => 15,
                'description' => 'Higienitas/sanitasi dapur di bawah standar (tidak fatal).',
            ],

            // === PELANGGARAN BERAT (35+ poin) ===
            [
                'code' => 'AL-01',
                'name' => 'Tidak masuk kerja tanpa keterangan (Alpha)',
                'category' => 'berat',
                'points' => 35,
                'description' => 'Absen tanpa izin/keterangan sama sekali.',
            ],
            [
                'code' => 'KS-01',
                'name' => 'Selisih kasir berulang / kelalaian berat',
                'category' => 'berat',
                'points' => 40,
                'description' => 'Selisih kas terjadi berulang atau kelalaian kasir yang merugikan.',
            ],
            [
                'code' => 'PL-01',
                'name' => 'Bersikap kasar / tidak sopan kepada pelanggan',
                'category' => 'berat',
                'points' => 40,
                'description' => 'Perilaku tidak sopan yang merusak reputasi outlet.',
            ],
            [
                'code' => 'MN-01',
                'name' => 'Manipulasi data operasional / stock opname',
                'category' => 'berat',
                'points' => 50,
                'description' => 'Memalsukan atau memanipulasi data laporan/stock opname.',
            ],
        ];

        foreach ($items as $item) {
            DisciplinaryViolation::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
        }
    }
}
