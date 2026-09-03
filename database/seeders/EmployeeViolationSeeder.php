<?php

namespace Database\Seeders;

use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryViolation;
use App\Models\EmployeeViolation;
use App\Models\User;
use App\Services\DisciplinaryPointService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeViolationSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::where('email', 'admin@example.com')->first() ?? User::first();
        if (! $reporter) {
            $this->command?->warn('User kosong, jalankan AdminUserSeeder dulu.');

            return;
        }

        $byCode = DisciplinaryViolation::all()->keyBy('code');
        if ($byCode->isEmpty()) {
            $this->command?->warn('Master pelanggaran kosong, jalankan DisciplinaryViolationSeeder dulu.');

            return;
        }

        $byEmail = User::all()->keyBy('email');
        $need = ['budi@example.com', 'ahmad@example.com', 'dewi@example.com', 'roni@example.com'];
        foreach ($need as $email) {
            if (! isset($byEmail[$email])) {
                $this->command?->warn("User {$email} tidak ditemukan, jalankan AdminUserSeeder dulu.");

                return;
            }
        }

        // Bersihkan data uji sebelumnya agar idempoten
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        EmployeeViolation::truncate();
        DisciplinaryAction::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $service = new DisciplinaryPointService();

        // Helper: catat via service (trigger SP otomatis)
        $record = function (string $email, string $code, string $when, ?string $notes = null) use ($byEmail, $byCode, $service, $reporter) {
            $user = $byEmail[$email];
            $violation = $byCode[$code];
            $service->recordViolation($user, $violation, new \DateTimeImmutable($when), $notes, null, $reporter);
        };

        // === Skenario uji — sebar 6 bulan terakhir untuk menguji rolling window, monthlyFeed, topViolators, filter kategori/sumber, dan trigger SP ===

        // 1) Budi Santoso — ringan bertumpuk (Teguran Lisan 15 poin)
        $record('budi@example.com', 'LT-01', now()->subDays(18)->format('Y-m-d 08:07:00'), 'Terlambat 8 menit — shift pagi, manual input HR');
        $record('budi@example.com', 'AT-01', now()->subDays(9)->format('Y-m-d 08:00:00'), 'Name tag tertinggal');
        $record('budi@example.com', 'KB-01', now()->subDays(2)->format('Y-m-d 22:15:00'), 'Meja racik belum dibersihkan saat closing');

        // Di luar rolling 6 bulan — tidak terhitung di poin aktif & grafik (untuk uji rolling window)
        EmployeeViolation::create([
            'user_id' => $byEmail['budi@example.com']->id,
            'disciplinary_violation_id' => $byCode['LT-01']->id,
            'occurred_at' => now()->subMonths(7)->format('Y-m-d 08:10:00'),
            'points' => $byCode['LT-01']->points,
            'notes' => '[DI LUAR WINDOW] Terlambat 7 bulan lalu — tidak masuk hitungan rolling',
            'reported_by' => $reporter->id,
            'source' => EmployeeViolation::SOURCE_MANUAL,
        ]);

        // 2) Ahmad Wijaya — sedang menengah (SP1 35 poin → lanjut SP2 50)
        $record('ahmad@example.com', 'LT-02', now()->subDays(42)->format('Y-m-d 08:35:00'), 'Terlambat 42 menit tanpa konfirmasi');
        $record('ahmad@example.com', 'TR-01', now()->subDays(19)->format('Y-m-d 14:20:00'), 'Salah input 2 order GoFood — komplain pelanggan');
        $record('ahmad@example.com', 'FS-02', now()->subDays(5)->format('Y-m-d 10:00:00'), 'Wastafel cuci tangan tidak diisi sabun');

        // Sumber otomatis (absensi) — untuk uji badge sumber & filter
        EmployeeViolation::create([
            'user_id' => $byEmail['ahmad@example.com']->id,
            'disciplinary_violation_id' => $byCode['LT-01']->id,
            'occurred_at' => now()->subDays(27)->format('Y-m-d 08:12:00'),
            'points' => $byCode['LT-01']->points,
            'notes' => 'Auto: terlambat 12 menit ('.now()->subDays(27)->format('Y-m-d').')',
            'reported_by' => null,
            'source' => EmployeeViolation::SOURCE_AUTO_ATTENDANCE,
        ]);

        // 3) Dewi Kartika — berat + sedang (SP2 50 → SP3 70)
        $record('dewi@example.com', 'AL-01', now()->subDays(11)->format('Y-m-d 08:00:00'), 'Alpha tanpa keterangan');
        $record('dewi@example.com', 'PL-01', now()->subDays(1)->format('Y-m-d 19:30:00'), 'Nada tinggi ke pelanggan — ada komplain tertulis');

        // 4) Roni Setiawan — berat beruntun (SP1 → SP2 → SP3 → Evaluasi PHK 85)
        $record('roni@example.com', 'AL-01', now()->subDays(60)->format('Y-m-d 08:00:00'), 'Alpha — tidak ada kabar');
        $record('roni@example.com', 'KS-01', now()->subDays(33)->format('Y-m-d 21:00:00'), 'Selisih kas Rp 180rb — kedua kalinya bulan ini');
        $record('roni@example.com', 'PL-01', now()->subDays(14)->format('Y-m-d 20:10:00'), 'Bersikap kasar saat handling komplain');
        $record('roni@example.com', 'MN-01', now()->subDays(3)->format('Y-m-d 16:00:00'), 'Manipulasi stok opname ayam — temuan audit');

        // Auto attendance untuk Roni
        EmployeeViolation::create([
            'user_id' => $byEmail['roni@example.com']->id,
            'disciplinary_violation_id' => $byCode['LT-02']->id,
            'occurred_at' => now()->subDays(8)->format('Y-m-d 08:45:00'),
            'points' => $byCode['LT-02']->points,
            'notes' => 'Auto: terlambat 45 menit ('.now()->subDays(8)->format('Y-m-d').')',
            'reported_by' => null,
            'source' => EmployeeViolation::SOURCE_AUTO_ATTENDANCE,
        ]);

        // 5) Administrator — clean record bonus: 1 pelanggaran 4 bulan lalu, 3 bulan terakhir bersih → -10 poin → aktif 0
        EmployeeViolation::create([
            'user_id' => $reporter->id,
            'disciplinary_violation_id' => $byCode['AT-01']->id,
            'occurred_at' => now()->subMonths(4)->subDays(5)->format('Y-m-d 09:00:00'),
            'points' => $byCode['AT-01']->points,
            'notes' => 'Atribut seragam tidak lengkap — sudah 4 bulan lalu (uji clean record bonus)',
            'reported_by' => $reporter->id,
            'source' => EmployeeViolation::SOURCE_MANUAL,
        ]);

        // Sebaran bulanan tambahan untuk grafik 6 bulan (kategori bervariasi tiap bulan)
        $record('budi@example.com', 'KB-01', now()->subMonths(5)->format('Y-m-15 22:00:00'), 'Lantai dapur licin — closing tidak sempurna');
        $record('ahmad@example.com', 'FS-01', now()->subMonths(4)->format('Y-m-10 11:00:00'), 'FIFO tidak jalan — bahan terbuang');
        $record('dewi@example.com', 'TR-01', now()->subMonths(3)->format('Y-m-20 13:00:00'), 'Salah catat promo — rugi operasional');
        $record('roni@example.com', 'KB-01', now()->subMonths(2)->format('Y-m-12 22:10:00'), 'Area kerja berantakan');

        // Trigger ulang untuk auto entries agar SP terbit juga dari auto
        foreach (['ahmad@example.com', 'roni@example.com'] as $email) {
            $service->checkAndTriggerActions($byEmail[$email]);
        }

        $this->command?->info('EmployeeViolationSeeder: '.EmployeeViolation::count().' pelanggaran, '.DisciplinaryAction::count().' aksi disiplin.');
    }
}
