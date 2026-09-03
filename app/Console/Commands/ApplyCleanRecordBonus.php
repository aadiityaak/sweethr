<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DisciplinaryPointService;
use Illuminate\Console\Command;

class ApplyCleanRecordBonus extends Command
{
    protected $signature = 'discipline:apply-clean-record-bonus';

    protected $description = 'Ringkasan bonus clean record: karyawan 3 bulan tanpa pelanggaran mendapat −10 poin (diterapkan saat kalkulasi poin aktif)';

    public function handle(DisciplinaryPointService $service): int
    {
        $eligible = 0;
        $total = 0;

        User::query()->active()->chunkById(200, function ($users) use ($service, &$eligible, &$total) {
            foreach ($users as $user) {
                $total++;
                if ($service->getCleanRecordBonus($user) > 0) {
                    $eligible++;
                }
            }
        });

        $this->info("Karyawan aktif: {$total}; memenuhi clean record (3 bulan bersih, −10 poin): {$eligible}.");
        $this->line('Bonus diterapkan otomatis di getActivePoints() — tidak perlu penulisan data.');

        return self::SUCCESS;
    }
}
