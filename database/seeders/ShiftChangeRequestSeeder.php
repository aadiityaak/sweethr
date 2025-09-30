<?php

namespace Database\Seeders;

use App\Models\ShiftChangeRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShiftChangeRequestSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Check if there are any non-admin users
    $employees = User::where('is_admin', false)->get();

    if ($employees->isEmpty()) {
      $this->command->warn('No employees found. Skipping ShiftChangeRequestSeeder.');
      return;
    }

    // Get admin users for reviewing
    $admins = User::where('is_admin', true)->get();

    // Create sample shift change requests
    $sampleRequests = [
      [
        'user_id' => $employees->first()->id,
        'original_date' => '2025-10-15',
        'requested_date' => '2025-10-18',
        'reason' => 'Ada acara keluarga yang tidak bisa dihindari',
        'status' => 'pending',
        'requested_at' => '2025-09-28 10:30:00',
      ],
      [
        'user_id' => $employees->get(1)?->id ?? $employees->first()->id,
        'original_date' => '2025-10-20',
        'requested_date' => '2025-10-22',
        'reason' => 'Keperluan pribadi mendesak',
        'status' => 'approved',
        'requested_at' => '2025-09-25 14:15:00',
        'reviewed_at' => '2025-09-26 09:30:00',
        'reviewed_by' => $admins->first()->id ?? 1,
        'admin_notes' => 'Request disetujui setelah review',
      ],
      [
        'user_id' => $employees->get(2)?->id ?? $employees->first()->id,
        'original_date' => '2025-11-01',
        'requested_date' => '2025-11-03',
        'reason' => 'Ada janji dengan dokter',
        'status' => 'rejected',
        'requested_at' => '2025-09-20 11:45:00',
        'reviewed_at' => '2025-09-21 16:20:00',
        'reviewed_by' => $admins->first()->id ?? 1,
        'admin_notes' => 'Request ditolak karena alasan yang tidak valid',
      ],
      [
        'user_id' => $employees->get(3)?->id ?? $employees->first()->id,
        'original_date' => '2025-11-10',
        'requested_date' => '2025-11-12',
        'reason' => 'Menghadiri pernikahan kerabat',
        'status' => 'pending',
        'requested_at' => '2025-09-29 08:00:00',
      ],
      [
        'user_id' => $employees->get(4)?->id ?? $employees->first()->id,
        'original_date' => '2025-11-15',
        'requested_date' => '2025-11-17',
        'reason' => 'Liburan bersama keluarga',
        'status' => 'pending',
        'requested_at' => '2025-09-27 13:20:00',
      ],
    ];

    foreach ($sampleRequests as $request) {
      ShiftChangeRequest::create($request);
    }

    // Create additional random requests if we have more employees
    if ($employees->count() > 5) {
      \Database\Factories\ShiftChangeRequestFactory::new()->count(10)->create();
    }

    $this->command->info('Shift change requests seeded successfully!');
  }
}
