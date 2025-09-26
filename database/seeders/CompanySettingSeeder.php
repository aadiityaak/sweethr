<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // Branding & Identity
            [
                'key' => 'company_name',
                'value' => 'PT Perusahaan Indonesia',
                'type' => 'text',
                'group' => 'branding',
                'description' => 'Nama resmi perusahaan yang akan ditampilkan di aplikasi',
                'is_public' => true,
            ],
            [
                'key' => 'company_tagline',
                'value' => 'Human Resource Management System',
                'type' => 'text',
                'group' => 'branding',
                'description' => 'Slogan atau tagline perusahaan',
                'is_public' => true,
            ],
            [
                'key' => 'primary_color',
                'value' => '#3b82f6',
                'type' => 'color',
                'group' => 'branding',
                'description' => 'Warna utama untuk tema aplikasi',
                'is_public' => true,
            ],

            // Company Information
            [
                'key' => 'company_address',
                'value' => 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10110',
                'type' => 'textarea',
                'group' => 'company_info',
                'description' => 'Alamat lengkap kantor pusat perusahaan',
                'is_public' => true,
            ],
            [
                'key' => 'company_phone',
                'value' => '+62 21 1234 5678',
                'type' => 'text',
                'group' => 'company_info',
                'description' => 'Nomor telepon kantor utama',
                'is_public' => true,
            ],
            [
                'key' => 'company_email',
                'value' => 'info@perusahaan.co.id',
                'type' => 'email',
                'group' => 'company_info',
                'description' => 'Alamat email resmi perusahaan',
                'is_public' => true,
            ],
            [
                'key' => 'company_website',
                'value' => 'https://www.perusahaan.co.id',
                'type' => 'url',
                'group' => 'company_info',
                'description' => 'URL website resmi perusahaan',
                'is_public' => true,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            CompanySetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}