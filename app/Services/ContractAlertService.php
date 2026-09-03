<?php

namespace App\Services;

use App\Models\EmploymentContract;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContractAlertService
{
    public const WARNING_DAYS = 60;
    public const CRITICAL_DAYS = 30;

    /**
     * Kontrak PKWT aktif yang mendekati berakhir (H-60 / H-30).
     */
    public function getExpiringContracts(int $days = self::WARNING_DAYS)
    {
        return EmploymentContract::expiringWithin($days)
            ->with(['user.position', 'user.department'])
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Alert terpadu — satu sumber: employment_contracts adalah truth untuk Masa Akhir PKWT.
     * users.contract_end_date disinkronkan otomatis dari/k ke employment_contracts (2 arah).
     */
    public function getAlerts(): array
    {
        $contracts = $this->getExpiringContracts(self::WARNING_DAYS);

        $alerts = [
            'warning' => [],
            'critical' => [],
            'expired' => [],
        ];

        foreach ($contracts as $contract) {
            match ($contract->alert_level) {
                'critical' => $alerts['critical'][] = $contract,
                'warning' => $alerts['warning'][] = $contract,
                default => null,
            };
        }

        $alerts['expired'] = EmploymentContract::query()
            ->where('type', EmploymentContract::TYPE_PKWT)
            ->where('status', EmploymentContract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->toDateString())
            ->with(['user.position'])
            ->orderBy('end_date')
            ->get()
            ->all();

        return $alerts;
    }

    /**
     * @deprecated Dipakai internal saja — getAlerts() sudah sinkron dengan users.
     */
    public function getUserContractAlerts(): array
    {
        $users = User::query()
            ->with(['position:id,title', 'department:id,name'])
            ->where('contract_type', 'pkwt')
            ->whereNotNull('contract_end_date')
            ->where('employment_status', 'active')
            ->orderBy('contract_end_date')
            ->get();

        $alerts = ['warning' => [], 'critical' => [], 'expired' => []];
        foreach ($users as $user) {
            match ($user->contract_alert_level) {
                'warning' => $alerts['warning'][] = $user,
                'critical' => $alerts['critical'][] = $user,
                'expired' => $alerts['expired'][] = $user,
                default => null,
            };
        }
        return $alerts;
    }

    public function getExpiringUsers(int $days = self::WARNING_DAYS)
    {
        return User::query()
            ->with(['position:id,title'])
            ->where('contract_type', 'pkwt')
            ->whereNotNull('contract_end_date')
            ->where('employment_status', 'active')
            ->whereBetween('contract_end_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('contract_end_date')
            ->get();
    }

    public function getStats(): array
    {
        $stats = [
            'pkwt_active' => 0,
            'pkwtt_active' => 0,
            'expiring_60' => 0,
            'expiring_30' => 0,
            'expired' => 0,
        ];

        $rows = DB::table('employment_contracts')
            ->select('type', DB::raw('count(*) as total'))
            ->where('status', EmploymentContract::STATUS_ACTIVE)
            ->groupBy('type')
            ->get();

        foreach ($rows as $row) {
            if ($row->type === EmploymentContract::TYPE_PKWT) {
                $stats['pkwt_active'] = (int) $row->total;
            } elseif ($row->type === EmploymentContract::TYPE_PKWTT) {
                $stats['pkwtt_active'] = (int) $row->total;
            }
        }

        $stats['expiring_60'] = EmploymentContract::expiringWithin(self::WARNING_DAYS)->count();
        $stats['expiring_30'] = EmploymentContract::expiringWithin(self::CRITICAL_DAYS)->count();
        $stats['expired'] = EmploymentContract::query()
            ->where('type', EmploymentContract::TYPE_PKWT)
            ->where('status', EmploymentContract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->toDateString())
            ->count();

        return $stats;
    }

    /**
     * Tandai kontrak yang sudah lewat end_date sebagai expired.
     * Dipanggil command harian.
     */
    public function markExpiredContracts(): int
    {
        return EmploymentContract::query()
            ->where('type', EmploymentContract::TYPE_PKWT)
            ->where('status', EmploymentContract::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->toDateString())
            ->update(['status' => EmploymentContract::STATUS_EXPIRED]);
    }

    /**
     * Perpanjang kontrak: kontrak lama status renewed + buat kontrak baru.
     */
    public function renewContract(EmploymentContract $contract, string $newEndDate, ?string $salaryGrade = null, ?User $creator = null): EmploymentContract
    {
        return DB::transaction(function () use ($contract, $newEndDate, $salaryGrade, $creator) {
            $contract->update(['status' => EmploymentContract::STATUS_RENEWED]);

            $sequence = EmploymentContract::where('user_id', $contract->user_id)->count() + 1;

            return EmploymentContract::create([
                'user_id' => $contract->user_id,
                'contract_number' => sprintf('PKWT/%s/%03d', now()->format('Ym'), $sequence),
                'type' => EmploymentContract::TYPE_PKWT,
                'start_date' => $contract->end_date->copy()->addDay()->toDateString(),
                'end_date' => $newEndDate,
                'status' => EmploymentContract::STATUS_ACTIVE,
                'salary_grade' => $salaryGrade ?? $contract->salary_grade,
                'created_by' => $creator?->id ?? auth()->id(),
            ]);
        });
    }

    /**
     * Angkat karyawan jadi PKWTT (karyawan tetap).
     */
    public function convertToPkwtt(EmploymentContract $contract, ?User $creator = null): EmploymentContract
    {
        return DB::transaction(function () use ($contract, $creator) {
            $contract->update(['status' => EmploymentContract::STATUS_RENEWED]);

            $sequence = EmploymentContract::where('user_id', $contract->user_id)->count() + 1;

            return EmploymentContract::create([
                'user_id' => $contract->user_id,
                'contract_number' => sprintf('PKWTT/%s/%03d', now()->format('Ym'), $sequence),
                'type' => EmploymentContract::TYPE_PKWTT,
                'start_date' => now()->toDateString(),
                'end_date' => null,
                'status' => EmploymentContract::STATUS_ACTIVE,
                'salary_grade' => $contract->salary_grade,
                'notes' => 'Pengangkatan karyawan tetap dari PKWT ' . $contract->contract_number,
                'created_by' => $creator?->id ?? auth()->id(),
            ]);
        });
    }
}
