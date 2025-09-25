<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\EmployeeDocument;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan direktori documents ada
        if (!Storage::disk('private')->exists('documents')) {
            Storage::disk('private')->makeDirectory('documents');
        }

        // Get all users (employees) dan document types
        $users = User::where('is_admin', false)->get();
        $documentTypes = DocumentType::all();
        $adminUser = User::where('is_admin', true)->first();

        if ($users->isEmpty() || $documentTypes->isEmpty() || !$adminUser) {
            $this->command->info('Tidak ada user atau document types. Pastikan seeder User dan DocumentType sudah dijalankan.');
            return;
        }

        // Create sample documents for each employee
        foreach ($users as $user) {
            // Setiap karyawan akan memiliki beberapa dokumen
            $this->createDocumentForUser($user, $documentTypes->where('code', 'ktp')->first(), $adminUser);
            $this->createDocumentForUser($user, $documentTypes->where('code', 'npwp')->first(), $adminUser);
            $this->createDocumentForUser($user, $documentTypes->where('code', 'contract')->first(), $adminUser);

            // Beberapa karyawan juga punya CV dan ijazah
            if (rand(0, 1)) {
                $this->createDocumentForUser($user, $documentTypes->where('code', 'cv')->first(), $adminUser);
            }

            if (rand(0, 1)) {
                $this->createDocumentForUser($user, $documentTypes->where('code', 'certificate')->first(), $adminUser);
            }
        }

        $this->command->info('Employee documents seeded successfully!');
    }

    private function createDocumentForUser($user, $documentType, $adminUser)
    {
        if (!$documentType) return;

        // Create dummy file content
        $fileContent = $this->generateDummyFileContent($documentType->code, $user);
        $fileName = $this->generateFileName($documentType->code, $user);
        $filePath = 'documents/' . $fileName;

        // Save dummy file
        Storage::disk('private')->put($filePath, $fileContent);

        // Simple dates - optional
        $issuedDate = null;
        $expiryDate = null;

        // Create document record
        EmployeeDocument::create([
            'user_id' => $user->id,
            'document_type_id' => $documentType->id,
            'title' => $this->getDocumentTitle($documentType->code, $user),
            'description' => $this->getDocumentDescription($documentType->code, $user),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_extension' => $this->getFileExtension($documentType->code),
            'file_size' => strlen($fileContent),
            'mime_type' => $this->getMimeType($documentType->code),
            'issued_date' => $issuedDate,
            'expiry_date' => $expiryDate,
            'uploaded_by' => $adminUser->id,
            'notes' => $this->getDocumentNotes($documentType->code),
            'version' => 1,
            'is_active' => true,
            'created_at' => now()->subDays(rand(1, 90)),
            'updated_at' => now()->subDays(rand(0, 30)),
        ]);
    }

    private function generateDummyFileContent($documentCode, $user)
    {
        return match($documentCode) {
            'ktp' => "DUMMY KTP FILE\nNama: {$user->name}\nNIK: " . rand(1000000000000000, 9999999999999999),
            'npwp' => "DUMMY NPWP FILE\nNama: {$user->name}\nNPWP: " . rand(100000000000000, 999999999999999),
            'contract' => "DUMMY CONTRACT FILE\nKaryawan: {$user->name}\nPosisi: Employee\nTanggal Mulai: " . now()->format('Y-m-d'),
            'cv' => "DUMMY CV FILE\nNama: {$user->name}\nPengalaman: 3+ years\nPendidikan: S1",
            'certificate' => "DUMMY CERTIFICATE FILE\nNama: {$user->name}\nJenjang: Sarjana\nJurusan: Teknologi Informasi",
            default => "DUMMY DOCUMENT FILE\nEmployee: {$user->name}\nDocument Type: {$documentCode}",
        };
    }

    private function generateFileName($documentCode, $user)
    {
        $timestamp = now()->format('YmdHis');
        $userName = str_replace(' ', '_', strtolower($user->name));

        return match($documentCode) {
            'ktp' => "{$timestamp}_KTP_{$userName}.pdf",
            'npwp' => "{$timestamp}_NPWP_{$userName}.pdf",
            'contract' => "{$timestamp}_Contract_{$userName}.pdf",
            'cv' => "{$timestamp}_CV_{$userName}.pdf",
            'certificate' => "{$timestamp}_Certificate_{$userName}.jpg",
            default => "{$timestamp}_{$documentCode}_{$userName}.pdf",
        };
    }

    private function getDocumentTitle($documentCode, $user)
    {
        return match($documentCode) {
            'ktp' => "KTP - {$user->name}",
            'npwp' => "NPWP - {$user->name}",
            'contract' => "Kontrak Kerja - {$user->name}",
            'cv' => "Curriculum Vitae - {$user->name}",
            'certificate' => "Ijazah S1 - {$user->name}",
            default => "Dokumen {$documentCode} - {$user->name}",
        };
    }

    private function getDocumentDescription($documentCode, $user)
    {
        return match($documentCode) {
            'ktp' => "Kartu Tanda Penduduk atas nama {$user->name}",
            'npwp' => "Nomor Pokok Wajib Pajak atas nama {$user->name}",
            'contract' => "Kontrak kerja karyawan tetap atas nama {$user->name}",
            'cv' => "Daftar riwayat hidup lengkap {$user->name}",
            'certificate' => "Ijazah pendidikan terakhir {$user->name}",
            default => null,
        };
    }

    private function getFileExtension($documentCode)
    {
        return match($documentCode) {
            'certificate' => 'jpg',
            default => 'pdf',
        };
    }

    private function getMimeType($documentCode)
    {
        return match($documentCode) {
            'certificate' => 'image/jpeg',
            default => 'application/pdf',
        };
    }


    private function getDocumentNotes($documentCode)
    {
        $notes = [
            'ktp' => ['Dokumen asli tersimpan di HRD', 'Kondisi dokumen baik', null],
            'npwp' => ['Dokumen original sudah diverifikasi', 'Status pajak aktif', null],
            'contract' => ['Kontrak sudah ditandatangani', 'Dokumen lengkap', null],
            'cv' => ['CV sesuai posisi yang dilamar', 'Referensi sudah diverifikasi', null],
            'certificate' => ['Ijazah sudah diverifikasi', 'Sesuai kualifikasi', null],
        ];

        $documentNotes = $notes[$documentCode] ?? [null];
        return $documentNotes[array_rand($documentNotes)];
    }
}
