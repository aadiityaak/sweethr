<?php

namespace App\Console\Commands;

use App\Services\ContractAlertService;
use Illuminate\Console\Command;

class SendContractExpiryAlerts extends Command
{
    protected $signature = 'contracts:send-expiry-alerts';

    protected $description = 'Tandai kontrak lewat tanggal sebagai expired dan ringkas alert H-60 / H-30 untuk HR';

    public function handle(ContractAlertService $service): int
    {
        $expired = $service->markExpiredContracts();
        if ($expired > 0) {
            $this->info("{$expired} kontrak PKWT ditandai expired.");
        }

        $alerts = $service->getAlerts();
        $userAlerts = $service->getUserContractAlerts();
        $stats = $service->getStats();

        $this->table(
            ['Kategori', 'Jumlah'],
            [
                ['PKWT aktif', $stats['pkwt_active']],
                ['PKWTT aktif', $stats['pkwtt_active']],
                ['H-60', $stats['expiring_60']],
                ['H-30 (kritis)', $stats['expiring_30']],
                ['Expired (masih aktif)', $stats['expired']],
            ]
        );

        foreach (['expired' => 'Expired', 'critical' => 'Kritis H-30', 'warning' => 'Peringatan H-60'] as $key => $label) {
            $items = $alerts[$key] ?? [];
            if ($items === []) {
                continue;
            }
            $this->line('');
            $this->warn("{$label} (" . count($items) . "):");
            foreach (array_slice($items, 0, 10) as $contract) {
                $name = $contract->user->name ?? ('#' . $contract->user_id);
                $end = $contract->end_date instanceof \DateTimeInterface ? $contract->end_date->format('Y-m-d') : (string) $contract->end_date;
                $this->line("  - {$name} — {$contract->contract_number} — s/d {$end} ({$contract->alert_level})");
            }
        }

        $this->info('Selesai. Integrasikan notifikasi email/database bila diperlukan.');

        return self::SUCCESS;
    }
}
