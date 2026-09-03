<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DisciplinaryPointService;
use Illuminate\Console\Command;

class CheckDisciplinaryThresholds extends Command
{
    protected $signature = 'discipline:check-point-thresholds {--user= : Batasi ke user_id tertentu}';

    protected $description = 'Periksa ambang poin disiplin rolling 6 bulan dan terbitkan SP bertingkat bila melewati tier';

    public function handle(DisciplinaryPointService $service): int
    {
        $userId = $this->option('user');

        $query = User::query()->active();
        if ($userId) {
            $query->whereKey($userId);
        }

        $checked = 0;
        $triggered = 0;

        $query->chunkById(100, function ($users) use ($service, &$checked, &$triggered) {
            foreach ($users as $user) {
                $before = \App\Models\DisciplinaryAction::forUser($user->id)
                    ->whereIn('status', [\App\Models\DisciplinaryAction::STATUS_ACTIVE, \App\Models\DisciplinaryAction::STATUS_RESOLVED])
                    ->count();
                $service->checkAndTriggerActions($user);
                $after = \App\Models\DisciplinaryAction::forUser($user->id)
                    ->whereIn('status', [\App\Models\DisciplinaryAction::STATUS_ACTIVE, \App\Models\DisciplinaryAction::STATUS_RESOLVED])
                    ->count();
                $checked++;
                if ($after > $before) {
                    $triggered += $after - $before;
                    $this->line("  - {$user->name} (#{$user->id}): +".($after - $before)." aksi (total aktif {$after})");
                }
            }
        });

        $this->info("Diperiksa {$checked} karyawan; {$triggered} aksi baru diterbitkan.");

        return self::SUCCESS;
    }
}
