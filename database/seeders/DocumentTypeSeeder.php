<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $documentTypes = [
            [
                'name' => 'KTP (Kartu Tanda Penduduk)',
                'code' => 'ktp',
                'description' => 'Kartu identitas penduduk Indonesia',
                'requires_expiry' => true,
                'default_validity_months' => 60, // 5 tahun
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'name' => 'NPWP (Nomor Pokok Wajib Pajak)',
                'code' => 'npwp',
                'description' => 'Nomor identitas wajib pajak',
                'requires_expiry' => false,
                'default_validity_months' => null,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Kontrak Kerja',
                'code' => 'contract',
                'description' => 'Kontrak kerja karyawan',
                'requires_expiry' => true,
                'default_validity_months' => 12,
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'max_file_size_mb' => 10,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'name' => 'CV (Curriculum Vitae)',
                'code' => 'cv',
                'description' => 'Daftar riwayat hidup',
                'requires_expiry' => false,
                'default_validity_months' => null,
                'allowed_extensions' => ['pdf', 'doc', 'docx'],
                'max_file_size_mb' => 5,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Ijazah Pendidikan',
                'code' => 'certificate',
                'description' => 'Ijazah atau sertifikat pendidikan',
                'requires_expiry' => false,
                'default_validity_months' => null,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => true,
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Kesehatan',
                'code' => 'bpjs_health',
                'description' => 'Kartu BPJS Kesehatan',
                'requires_expiry' => false,
                'default_validity_months' => null,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => false,
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Ketenagakerjaan',
                'code' => 'bpjs_employment',
                'description' => 'Kartu BPJS Ketenagakerjaan',
                'requires_expiry' => false,
                'default_validity_months' => null,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Keterangan Sehat',
                'code' => 'health_certificate',
                'description' => 'Surat keterangan kesehatan dari dokter',
                'requires_expiry' => true,
                'default_validity_months' => 6,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Surat Keterangan Catatan Kepolisian (SKCK)',
                'code' => 'skck',
                'description' => 'Surat keterangan berkelakuan baik dari kepolisian',
                'requires_expiry' => true,
                'default_validity_months' => 6,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sertifikat Pelatihan',
                'code' => 'training_certificate',
                'description' => 'Sertifikat dari pelatihan atau kursus',
                'requires_expiry' => true,
                'default_validity_months' => 24,
                'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
                'max_file_size_mb' => 5,
                'is_mandatory' => false,
                'is_active' => true,
            ],
        ];

        foreach ($documentTypes as $type) {
            \App\Models\DocumentType::create($type);
        }
    }
}
