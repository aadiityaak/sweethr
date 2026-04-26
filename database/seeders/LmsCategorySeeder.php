<?php

namespace Database\Seeders;

use App\Models\LmsCategory;
use Illuminate\Database\Seeder;

class LmsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Divisi Dapur (Kitchen Training)' => [
                'Basic Cooking Skill',
                'Standar kebersihan dapur (SOP Hygiene & Sanitasi)',
                'Cara mencuci dan menyimpan bahan',
                'Teknik dasar memasak ayam (marinasi, tepung, goreng)',
                'Penggunaan alat dapur',
                'Standard Recipe Ayam Geprek',
                'Resep ayam crispy standar',
                'Resep sambal level 1 sampai 10',
                'Standar plating dan penyajian',
                'Konsistensi rasa (quality control)',
                'Speed dan Efficiency',
                'Workflow dapur saat ramai',
                'Manajemen waktu masak',
                'Batch cooking vs fresh cooking',
                'Quality Control',
                'Ciri ayam matang sempurna',
                'Standar rasa dan tekstur',
                'Cara menangani komplain rasa',
            ],
            'Divisi Kasir dan Frontliner' => [
                'Service Excellence',
                'Cara menyapa customer',
                'Teknik upselling',
                'Handling komplain pelanggan',
                'Penggunaan sistem POS',
                'Cara input order',
                'Manajemen antrian',
                'Metode pembayaran cash dan QRIS',
                'Komunikasi efektif',
                'Bahasa sopan dan cepat',
                'Cara menjelaskan menu',
            ],
            'Divisi Delivery dan Packing' => [
                'Packaging standard',
                'Cara packing agar tidak tumpah',
                'Standar tampilan box',
                'Branding kemasan (stiker dan label)',
                'Delivery handling',
                'Manajemen order online (GoFood dan GrabFood)',
                'Estimasi waktu kirim',
                'Handling order overload',
            ],
            'Divisi Gudang dan Inventory' => [
                'Manajemen stok',
                'FIFO (First In First Out)',
                'Pencatatan bahan masuk dan keluar',
                'Supplier management',
                'Standar kualitas bahan',
                'Cara memilih supplier',
                'Kontrol waste',
                'Mengurangi bahan terbuang',
                'Analisis pemakaian bahan',
            ],
            'Divisi Marketing' => [
                'Digital marketing',
                'Konten Instagram dan TikTok',
                'Foto produk menarik',
                'Copywriting promo',
                'Strategi promo',
                'Diskon dan bundling',
                'Flash sale',
                'Loyalty program',
                'Branding',
                'Positioning brand ayam geprek',
                'Tone komunikasi brand',
            ],
            'Divisi Manajemen dan Owner' => [
                'SOP dan sistem bisnis',
                'Standarisasi operasional',
                'Pembuatan SOP semua divisi',
                'Keuangan',
                'Laporan harian',
                'Harga pokok produksi (HPP)',
                'Profit margin',
                'HR dan team management',
                'Rekrut karyawan',
                'Training dan evaluasi',
                'KPI karyawan',
            ],
            'Soft skill semua divisi' => [
                'Disiplin kerja',
                'Kerja tim',
                'Problem solving',
                'Attitude kerja',
            ],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = LmsCategory::firstOrCreate(
                ['parent_id' => null, 'name' => $parentName],
                ['is_active' => true],
            );

            foreach ($children as $childName) {
                LmsCategory::firstOrCreate(
                    ['parent_id' => $parent->id, 'name' => $childName],
                    ['is_active' => true],
                );
            }
        }
    }
}
