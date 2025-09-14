<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get leave types and users
        $leaveTypes = LeaveType::all();
        $employees = User::where('is_admin', false)
            ->where('employment_status', 'active')
            ->get();
        $admin = User::where('is_admin', true)->first();

        if ($leaveTypes->isEmpty() || $employees->isEmpty()) {
            return;
        }

        $leaveRequests = [
            // Pending requests
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(10),
                'end_date' => Carbon::now()->addDays(12),
                'total_days' => 3,
                'reason' => 'Liburan keluarga ke Bali bersama istri dan anak-anak.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(2),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->where('name', 'Cuti Sakit')->first()?->id ?? $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(5),
                'end_date' => Carbon::now()->addDays(6),
                'total_days' => 2,
                'reason' => 'Sakit demam tinggi dan perlu istirahat total.',
                'status' => 'pending',
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(19),
                'total_days' => 5,
                'reason' => 'Acara pernikahan adik kandung di Yogyakarta.',
                'status' => 'pending',
                'created_at' =>Carbon::now()->subDays(1),
            ],

            // Approved requests
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(3),
                'total_days' => 3,
                'reason' => 'Menghadiri pemakaman kakek di kampung halaman.',
                'status' => 'approved',
                'created_at' =>Carbon::now()->subDays(10),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(8),
                'rejection_reason' =>'Approved. Mohon segera kembali dan semoga diberikan kekuatan.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->where('name', 'Cuti Melahirkan')->first()?->id ?? $leaveTypes->random()->id,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'total_days' => 90,
                'reason' => 'Cuti melahirkan anak pertama.',
                'status' => 'approved',
                'created_at' =>Carbon::now()->subDays(40),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(35),
                'rejection_reason' =>'Approved. Selamat atas kelahiran buah hatinya. Semoga sehat selalu.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->subDays(11),
                'total_days' => 5,
                'reason' => 'Honeymoon ke Lombok setelah menikah.',
                'status' => 'approved',
                'created_at' =>Carbon::now()->subDays(25),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(20),
                'rejection_reason' =>'Approved. Selamat menempuh hidup baru!',
            ],

            // Rejected requests
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addDays(10),
                'total_days' => 8,
                'reason' => 'Liburan ke Eropa bersama teman-teman kuliah.',
                'status' => 'rejected',
                'created_at' =>Carbon::now()->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subHours(2),
                'rejection_reason' =>'Maaf, periode ini sedang peak season dan kita butuh semua tim. Mohon ajukan di bulan berikutnya.',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(7),
                'end_date' => Carbon::now()->addDays(14),
                'total_days' => 8,
                'reason' => 'Ingin istirahat karena kelelahan kerja.',
                'status' => 'rejected',
                'created_at' =>Carbon::now()->subDays(3),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subDays(1),
                'rejection_reason' =>'Cuti terlalu lama untuk alasan tersebut. Silakan konsultasi dengan HR untuk work-life balance.',
            ],

            // More pending requests with variety
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->where('name', 'Cuti Haji/Umroh')->first()?->id ?? $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addMonths(2),
                'end_date' => Carbon::now()->addMonths(2)->addDays(14),
                'total_days' => 15,
                'reason' => 'Menunaikan ibadah haji yang sudah lama ditunggu.',
                'status' => 'pending',
                'created_at' =>Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->addDays(20),
                'end_date' => Carbon::now()->addDays(21),
                'total_days' => 2,
                'reason' => 'Mengurus dokumen penting di kantor catatan sipil.',
                'status' => 'pending',
                'created_at' =>Carbon::now()->subHours(12),
            ],

            // Historical approved requests
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->random()->id,
                'start_date' => Carbon::now()->subMonths(2),
                'end_date' => Carbon::now()->subMonths(2)->addDays(6),
                'total_days' => 7,
                'reason' => 'Liburan tahun baru ke Singapura.',
                'status' => 'approved',
                'created_at' =>Carbon::now()->subMonths(2)->subDays(15),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(2)->subDays(10),
                'rejection_reason' =>'Approved. Have a great holiday!',
            ],
            [
                'user_id' => $employees->random()->id,
                'leave_type_id' => $leaveTypes->where('name', 'Cuti Sakit')->first()?->id ?? $leaveTypes->random()->id,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->subMonths(1)->addDays(2),
                'total_days' => 3,
                'reason' => 'Operasi usus buntu di rumah sakit.',
                'status' => 'approved',
                'created_at' =>Carbon::now()->subMonths(1)->subDays(1),
                'approved_by' => $admin->id,
                'approved_at' => Carbon::now()->subMonths(1),
                'rejection_reason' =>'Approved. Semoga cepat sembuh dan pulih seperti sedia kala.',
            ],
        ];

        foreach ($leaveRequests as $request) {
            LeaveRequest::create($request);
        }
    }
}
