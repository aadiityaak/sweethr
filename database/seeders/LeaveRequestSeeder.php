<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing leave requests first
        LeaveRequest::truncate();

        // Get leave types and users
        $annualLeave = LeaveType::where('code', 'ANNUAL')->first();
        $sickLeave = LeaveType::where('code', 'SICK')->first();
        $personalLeave = LeaveType::where('code', 'PERSONAL')->first();
        $maternityLeave = LeaveType::where('code', 'MATERNITY')->first();
        $emergencyLeave = LeaveType::where('code', 'EMERGENCY')->first();
        $bereavementLeave = LeaveType::where('code', 'BEREAVEMENT')->first();

        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();
        $admin = User::where('is_admin', true)->first();

        if (! $annualLeave || $employees->isEmpty()) {
            return;
        }

        $leaveRequests = [
            // === PENDING REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(12),
                'total_days' => 3,
                'reason' => 'Liburan keluarga ke Bali bersama istri dan anak-anak. Sudah booking hotel dan tiket pesawat.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addDays(2),
                'total_days' => 2,
                'reason' => 'Demam tinggi dan flu berat. Dokter menyarankan istirahat total untuk mencegah penyebaran.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(16),
                'total_days' => 2,
                'reason' => 'Menghadiri acara pernikahan adik kandung di Yogyakarta. Keluarga besar berkumpul.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(29),
                'total_days' => 5,
                'reason' => 'Liburan akhir tahun ke Singapura bersama keluarga. Sudah planning dari 6 bulan lalu.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $emergencyLeave->id,
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => Carbon::now()->addDays(2),
                'total_days' => 1,
                'reason' => 'Ayah masuk rumah sakit mendadak dan perlu pendampingan keluarga.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ],

            // === APPROVED REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $bereavementLeave->id,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'total_days' => 3,
                'reason' => 'Menghadiri pemakaman kakek di kampung halaman Purworejo.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(8),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(8),
                'rejection_reason' => 'Approved. Turut berduka cita. Semoga keluarga diberikan kekuatan.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $maternityLeave->id,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'total_days' => 90,
                'reason' => 'Cuti melahirkan anak pertama. Dokter menyarankan istirahat total sebelum dan sesudah melahirkan.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(40),
                'updated_at' => Carbon::now()->subDays(35),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(35),
                'rejection_reason' => 'Approved. Selamat atas kelahiran buah hati. Semoga ibu dan bayi sehat selalu.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->subDays(11),
                'total_days' => 5,
                'reason' => 'Honeymoon ke Lombok setelah menikah bulan lalu. Trip romantis yang sudah lama direncanakan.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(20),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(20),
                'rejection_reason' => 'Approved. Selamat menempuh hidup baru! Enjoy your honeymoon.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->subDays(18),
                'total_days' => 3,
                'reason' => 'Operasi usus buntu di RSU Dr. Soetomo. Perlu recovery time setelah operasi.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(21),
                'updated_at' => Carbon::now()->subDays(20),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(20),
                'rejection_reason' => 'Approved. Semoga cepat sembuh dan pulih seperti sedia kala. Take care!',
            ],

            // === REJECTED REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(10),
                'total_days' => 8,
                'reason' => 'Liburan ke Eropa bersama teman-teman kuliah. Tour 8 negara selama seminggu.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subHours(2),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subHours(2),
                'rejection_reason' => 'Maaf, periode ini sedang peak season dan kita butuh semua tim untuk project deadline. Mohon ajukan di bulan berikutnya.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(9),
                'total_days' => 3,
                'reason' => 'Ingin istirahat karena kelelahan kerja dan stress.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(1),
                'rejection_reason' => 'Untuk alasan kelelahan, silakan konsultasi dengan HR untuk program work-life balance atau medical checkup terlebih dahulu.',
            ],

            // === HISTORICAL DATA (LAST 6 MONTHS) ===

            // 6 months ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(6)->subDays(5),
                'end_date' => Carbon::now()->subMonths(6)->addDays(2),
                'total_days' => 8,
                'reason' => 'Liburan ke Jepang untuk melihat sakura bersama keluarga.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(6)->subDays(20),
                'updated_at' => Carbon::now()->subMonths(6)->subDays(15),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(6)->subDays(15),
                'rejection_reason' => 'Approved. Enjoy the cherry blossoms season!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subMonths(6)->subDays(10),
                'end_date' => Carbon::now()->subMonths(6)->subDays(8),
                'total_days' => 3,
                'reason' => 'Diare akut dan dehidrasi, perlu istirahat total.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(6)->subDays(10),
                'updated_at' => Carbon::now()->subMonths(6)->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(6)->subDays(10),
                'rejection_reason' => 'Approved. Jaga pola makan dan minum yang cukup.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(6)->addDays(10),
                'end_date' => Carbon::now()->subMonths(6)->addDays(10),
                'total_days' => 1,
                'reason' => 'Mengurus perpanjangan paspor di imigrasi.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(6)->subDays(3),
                'updated_at' => Carbon::now()->subMonths(6)->subDays(2),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(6)->subDays(2),
                'rejection_reason' => 'Approved. Dokumen penting memang perlu diurus.',
            ],

            // 5 months ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(5)->subDays(3),
                'end_date' => Carbon::now()->subMonths(5)->addDays(1),
                'total_days' => 5,
                'reason' => 'Liburan ke Banyuwangi dan Pulau Ijen bersama teman-teman.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(5)->subDays(14),
                'updated_at' => Carbon::now()->subMonths(5)->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(5)->subDays(10),
                'rejection_reason' => 'Approved. Have fun exploring East Java!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $bereavementLeave->id,
                'start_date' => Carbon::now()->subMonths(5)->addDays(15),
                'end_date' => Carbon::now()->subMonths(5)->addDays(17),
                'total_days' => 3,
                'reason' => 'Menghadiri pemakaman paman di Surabaya.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(5)->addDays(14),
                'updated_at' => Carbon::now()->subMonths(5)->addDays(14),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(5)->addDays(14),
                'rejection_reason' => 'Approved immediately. Turut berduka cita atas kehilangan paman.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $emergencyLeave->id,
                'start_date' => Carbon::now()->subMonths(5)->addDays(20),
                'end_date' => Carbon::now()->subMonths(5)->addDays(21),
                'total_days' => 2,
                'reason' => 'Anak demam tinggi dan kejang, harus dibawa ke UGD.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(5)->addDays(20),
                'updated_at' => Carbon::now()->subMonths(5)->addDays(20),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(5)->addDays(20),
                'rejection_reason' => 'Approved. Prioritas utama adalah kesehatan anak.',
            ],

            // 4 months ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(4)->subDays(7),
                'end_date' => Carbon::now()->subMonths(4)->subDays(3),
                'total_days' => 5,
                'reason' => 'Menghadiri pernikahan sepupu di Medan, Sumatera Utara.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(4)->subDays(15),
                'updated_at' => Carbon::now()->subMonths(4)->subDays(12),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(4)->subDays(12),
                'rejection_reason' => 'Approved. Selamat untuk sepupu yang menikah!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subMonths(4)->addDays(10),
                'end_date' => Carbon::now()->subMonths(4)->addDays(11),
                'total_days' => 2,
                'reason' => 'Migrain parah dan mual-mual, tidak bisa berkonsentrasi.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(4)->addDays(10),
                'updated_at' => Carbon::now()->subMonths(4)->addDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(4)->addDays(10),
                'rejection_reason' => 'Approved. Istirahat yang cukup sangat penting.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(4)->addDays(25),
                'end_date' => Carbon::now()->subMonths(4)->addDays(25),
                'total_days' => 1,
                'reason' => 'Medical check-up tahunan dan konsultasi kesehatan.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(4)->addDays(20),
                'updated_at' => Carbon::now()->subMonths(4)->addDays(22),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(4)->addDays(22),
                'rejection_reason' => 'Approved. Kesehatan preventif sangat penting.',
            ],

            // 3 months ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(3)->subDays(5),
                'end_date' => Carbon::now()->subMonths(3)->addDays(1),
                'total_days' => 7,
                'reason' => 'Liburan keluarga ke Danau Toba, Sumatera Utara.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(3)->subDays(20),
                'updated_at' => Carbon::now()->subMonths(3)->subDays(15),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(3)->subDays(15),
                'rejection_reason' => 'Approved. Explore the beautiful Lake Toba!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subMonths(3)->addDays(12),
                'end_date' => Carbon::now()->subMonths(3)->addDays(14),
                'total_days' => 3,
                'reason' => 'Tipes dan perlu perawatan intensif di rumah.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(3)->addDays(12),
                'updated_at' => Carbon::now()->subMonths(3)->addDays(12),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(3)->addDays(12),
                'rejection_reason' => 'Approved. Jaga pola makan dan kebersihan makanan.',
            ],

            // 2 months ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subMonths(2)->addDays(6),
                'total_days' => 7,
                'reason' => 'Liburan tahun baru ke Singapura dan Malaysia. Quality time dengan keluarga.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(2)->subDays(15),
                'updated_at' => Carbon::now()->subMonths(2)->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(2)->subDays(10),
                'rejection_reason' => 'Approved. Have a great holiday and happy new year!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subMonths(2)->addDays(20),
                'end_date' => Carbon::now()->subMonths(2)->addDays(21),
                'total_days' => 2,
                'reason' => 'Sakit gigi parah, perlu perawatan darurat ke dokter gigi.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(2)->addDays(20),
                'updated_at' => Carbon::now()->subMonths(2)->addDays(20),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(2)->addDays(20),
                'rejection_reason' => 'Approved. Sakit gigi memang sangat mengganggu.',
            ],

            // 1 month ago data
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $sickLeave->id,
                'start_date' => Carbon::now()->subMonths(1)->subDays(10),
                'end_date' => Carbon::now()->subMonths(1)->subDays(8),
                'total_days' => 3,
                'reason' => 'Covid-19 positif, perlu isolasi mandiri di rumah sesuai protokol kesehatan.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(1)->subDays(11),
                'updated_at' => Carbon::now()->subMonths(1)->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1)->subDays(10),
                'rejection_reason' => 'Approved. Segera isolasi dan jaga kesehatan. Semoga cepat sembuh.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->subMonths(1)->addDays(1),
                'total_days' => 2,
                'reason' => 'Mengurus sertifikat tanah di BPN yang harus hadir langsung.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(1)->subDays(5),
                'updated_at' => Carbon::now()->subMonths(1)->subDays(3),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1)->subDays(3),
                'rejection_reason' => 'Approved. Urusan legal property memang penting. Take your time.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(1)->addDays(15),
                'end_date' => Carbon::now()->subMonths(1)->addDays(18),
                'total_days' => 4,
                'reason' => 'Long weekend ke Bromo dan Malang bersama keluarga.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(1)->addDays(5),
                'updated_at' => Carbon::now()->subMonths(1)->addDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1)->addDays(10),
                'rejection_reason' => 'Approved. Enjoy the beautiful sunrise at Bromo!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $emergencyLeave->id,
                'start_date' => Carbon::now()->subDays(45),
                'end_date' => Carbon::now()->subDays(44),
                'total_days' => 2,
                'reason' => 'Ibu dirawat di ICU karena serangan jantung mendadak.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(45),
                'updated_at' => Carbon::now()->subDays(45),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(45),
                'rejection_reason' => 'Approved immediately. Semoga ibu cepat pulih. Tim akan support pekerjaan anda.',
            ],

            // === MORE RECENT REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(33),
                'total_days' => 4,
                'reason' => 'Menghadiri reuni SMA sekaligus jalan-jalan ke Bandung.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $bereavementLeave->id,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(6),
                'total_days' => 2,
                'reason' => 'Menghadiri pemakaman nenek di Semarang.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(18),
                'updated_at' => Carbon::now()->subHours(18),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(12),
                'end_date' => Carbon::now()->addDays(12),
                'total_days' => 1,
                'reason' => 'Interview kerja untuk posisi yang lebih baik di perusahaan lain.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(1),
                'rejection_reason' => 'Mohon diskusi dengan atasan langsung terlebih dahulu mengenai career path di perusahaan ini.',
            ],
        ];

        foreach ($leaveRequests as $request) {
            LeaveRequest::create($request);
        }
    }
}
