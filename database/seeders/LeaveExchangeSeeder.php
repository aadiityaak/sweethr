<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LeaveExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing leave requests first to avoid duplicates
        LeaveRequest::where('reason', 'like', '%tukar libur%')->delete();
        LeaveRequest::where('reason', 'like', '%exchange%')->delete();

        // Get leave types and users
        $annualLeave = LeaveType::where('code', 'ANNUAL')->first();
        $personalLeave = LeaveType::where('code', 'PERSONAL')->first();

        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();
        $admin = User::where('is_admin', true)->first();

        if (! $annualLeave || $employees->isEmpty()) {
            if ($this->command) {
                $this->command->info('Not enough data to create leave exchange samples.');
            }

            return;
        }

        $leaveExchanges = [
            // === PENDING LEAVE EXCHANGE REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(8),
                'end_date' => Carbon::now()->addDays(10),
                'total_days' => 3,
                'reason' => 'Tukar libur untuk menghadiri acara pernikahan saudara di Bandung. Tanggal tidak bisa digeser karena sudah fix.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(17),
                'total_days' => 3,
                'reason' => 'Tukar libur karena ada keperluan keluarga yang mendadak di luar kota. Mohon izin untuk tukar jadwal liburan.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(22),
                'end_date' => Carbon::now()->addDays(23),
                'total_days' => 2,
                'reason' => 'Tukar libur untuk mengurus dokumen penting di kantor imigrasi. Jadwal tidak bisa diubah.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(35),
                'total_days' => 6,
                'reason' => 'Tukar libur untuk liburan keluarga ke Yogyakarta. Sudah booking hotel dan tiket kereta.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(12),
                'end_date' => Carbon::now()->addDays(12),
                'total_days' => 1,
                'reason' => 'Tukar libur untuk menghadiri wisuda anak di universitas. Acara sangat penting untuk keluarga.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],

            // === APPROVED LEAVE EXCHANGE REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subDays(8),
                'end_date' => Carbon::now()->subDays(6),
                'total_days' => 3,
                'reason' => 'Tukar libur untuk menghadiri acara keluarga di Surabaya. Berhasil mengurus semua keperluan dengan baik.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(10),
                'admin_notes' => 'Approved. Semoga acara keluarga berjalan lancar.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->subDays(14),
                'total_days' => 2,
                'reason' => 'Tukar libur untuk mengurus perpanjangan SIM di kantor polisi. Semua proses berjalan lancar.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(18),
                'updated_at' => Carbon::now()->subDays(16),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(16),
                'admin_notes' => 'Approved. Dokumen penting memang perlu diurus tepat waktu.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subDays(25),
                'end_date' => Carbon::now()->subDays(22),
                'total_days' => 4,
                'reason' => 'Tukar libur untuk liburan singkat ke Bali bersama pasangan. Sudah direncanakan jauh-jauh hari.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(28),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(28),
                'admin_notes' => 'Approved. Enjoy your short vacation to Bali!',
            ],

            // === REJECTED LEAVE EXCHANGE REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(8),
                'total_days' => 4,
                'reason' => 'Tukar libur untuk jalan-jalan ke luar kota bersama teman-teman.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subDays(2),
                'updated_at' => Carbon::now()->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(1),
                'rejection_reason' => 'Maaf, periode ini sedang banyak deadline project. Tukar libur tidak dapat disetujui.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(18),
                'end_date' => Carbon::now()->addDays(19),
                'total_days' => 2,
                'reason' => 'Tukar libur karena ingin istirahat di rumah saja.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subDays(4),
                'updated_at' => Carbon::now()->subDays(2),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(2),
                'rejection_reason' => 'Tukar libur untuk alasan istirahat di rumah tidak dapat disetujui. Mohon gunakan jadwal libur yang sudah ditentukan.',
            ],

            // === HISTORICAL LEAVE EXCHANGE DATA (LAST 3 MONTHS) ===

            // 3 months ago
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(3)->subDays(10),
                'end_date' => Carbon::now()->subMonths(3)->subDays(8),
                'total_days' => 3,
                'reason' => 'Tukar libur untuk menghadiri rapat keluarga besar di kampung halaman.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(3)->subDays(15),
                'updated_at' => Carbon::now()->subMonths(3)->subDays(12),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(3)->subDays(12),
                'admin_notes' => 'Approved. Semoga rapat keluarga berjalan lancar.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(3)->addDays(5),
                'end_date' => Carbon::now()->subMonths(3)->addDays(5),
                'total_days' => 1,
                'reason' => 'Tukar libur untuk mengantar orang tua ke rumah sakit.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(3)->subDays(2),
                'updated_at' => Carbon::now()->subMonths(3)->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(3)->subDays(1),
                'admin_notes' => 'Approved. Kesehatan orang tua adalah prioritas.',
            ],

            // 2 months ago
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(2)->subDays(8),
                'end_date' => Carbon::now()->subMonths(2)->subDays(5),
                'total_days' => 4,
                'reason' => 'Tukar libur untuk liburan ke Lombok bersama keluarga.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(2)->subDays(15),
                'updated_at' => Carbon::now()->subMonths(2)->subDays(12),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(2)->subDays(12),
                'admin_notes' => 'Approved. Enjoy your vacation to Lombok!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(2)->addDays(12),
                'end_date' => Carbon::now()->subMonths(2)->addDays(13),
                'total_days' => 2,
                'reason' => 'Tukar libur untuk mengurus pembuatan paspor di kantor imigrasi.',
                'status' => 'rejected',
                'created_at' => Carbon::now()->subMonths(2)->subDays(5),
                'updated_at' => Carbon::now()->subMonths(2)->subDays(3),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(2)->subDays(3),
                'rejection_reason' => 'Mohon maaf, tukar libur tidak dapat disetujui karena kebutuhan operasional. Silakan ajukan di hari lain.',
            ],

            // 1 month ago
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->subMonths(1)->subDays(12),
                'end_date' => Carbon::now()->subMonths(1)->subDays(9),
                'total_days' => 4,
                'reason' => 'Tukar libur untuk menghadiri acara pernikahan teman kuliah.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(1)->subDays(18),
                'updated_at' => Carbon::now()->subMonths(1)->subDays(15),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1)->subDays(15),
                'admin_notes' => 'Approved. Selamat untuk teman Anda yang menikah!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->subMonths(1)->addDays(8),
                'end_date' => Carbon::now()->subMonths(1)->addDays(8),
                'total_days' => 1,
                'reason' => 'Tukar libur untuk menghadiri parent meeting di sekolah anak.',
                'status' => 'approved',
                'created_at' => Carbon::now()->subMonths(1)->subDays(3),
                'updated_at' => Carbon::now()->subMonths(1)->subDays(2),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1)->subDays(2),
                'admin_notes' => 'Approved. Pendidikan anak adalah prioritas utama.',
            ],

            // === UPCOMING LEAVE EXCHANGE REQUESTS ===
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $annualLeave->id,
                'start_date' => Carbon::now()->addDays(40),
                'end_date' => Carbon::now()->addDays(43),
                'total_days' => 4,
                'reason' => 'Tukar libur untuk long weekend ke Malang dan Batu bersama keluarga.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $personalLeave->id,
                'start_date' => Carbon::now()->addDays(25),
                'end_date' => Carbon::now()->addDays(25),
                'total_days' => 1,
                'reason' => 'Tukar libur untuk menghadiri seminar wajib di kantor pusat.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
        ];

        foreach ($leaveExchanges as $exchange) {
            LeaveRequest::create($exchange);
        }

        // Only show command info if running in console context
        if ($this->command) {
            $this->command->info('Leave exchange samples seeded successfully!');
            $this->command->info('Total leave exchanges created: '.count($leaveExchanges));
        }
    }
}
