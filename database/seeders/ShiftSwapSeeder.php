<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ShiftSwap;
use App\Models\WorkShift;

class ShiftSwapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('is_admin', false)->take(5)->get();
        $shifts = WorkShift::all();

        if ($users->count() < 2 || $shifts->count() < 2) {
            $this->command->info('Not enough users or shifts to create shift swap samples.');
            return;
        }

        // Create some sample shift swap requests
        $sampleSwaps = [
            [
                'requester_id' => $users[0]->id,
                'target_user_id' => $users[1]->id,
                'requested_date' => now()->addDays(5)->toDateString(),
                'target_date' => now()->addDays(6)->toDateString(),
                'requester_shift_id' => $shifts[0]->id,
                'target_shift_id' => $shifts[1]->id,
                'reason' => 'Ada keperluan keluarga mendadak',
                'status' => 'pending',
            ],
            [
                'requester_id' => $users[1]->id,
                'target_user_id' => $users[2]->id ?? $users[0]->id,
                'requested_date' => now()->addDays(10)->toDateString(),
                'target_date' => now()->addDays(11)->toDateString(),
                'requester_shift_id' => $shifts[1]->id,
                'target_shift_id' => $shifts[0]->id,
                'reason' => 'Ingin tukar shift untuk keperluan pribadi',
                'status' => 'approved',
                'approved_at' => now()->subDays(1),
            ],
            [
                'requester_id' => $users[2] ?? $users[0]->id,
                'target_user_id' => $users[0]->id,
                'requested_date' => now()->subDays(3)->toDateString(),
                'target_date' => now()->subDays(2)->toDateString(),
                'requester_shift_id' => $shifts[0]->id,
                'target_shift_id' => $shifts[1]->id,
                'reason' => 'Jadwal bentrok dengan pelatihan',
                'status' => 'rejected',
                'rejection_reason' => 'Tidak dapat mengakomodasi karena kebutuhan operasional',
            ],
        ];

        foreach ($sampleSwaps as $swap) {
            ShiftSwap::create($swap);
        }

        $this->command->info('Shift swap samples seeded successfully!');
    }
}