<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DisciplinaryAction;
use App\Models\DisciplinaryViolation;
use App\Models\EmployeeViolation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DisciplinaryPointService
{
    /** Rolling window poin pelanggaran */
    public const ROLLING_MONTHS = 6;

    /** Ambang tier aksi disiplin */
    public const THRESHOLD_TEGURAN = 15;
    public const THRESHOLD_SP1 = 35;
    public const THRESHOLD_SP2 = 50;
    public const THRESHOLD_SP3 = 70;
    public const THRESHOLD_PHK_EVAL = 85;

    /** Clean record bonus */
    public const CLEAN_MONTHS_REQUIRED = 3;
    public const CLEAN_BONUS_POINTS = 10;

    /** Auto-detect keterlambatan dari absensi */
    public const LATE_MINOR_MINUTES = 15;
    public const LATE_MAJOR_MINUTES = 30;

    /**
     * Akumulasi poin aktif karyawan (rolling 6 bulan + clean record bonus).
     */
    public function getActivePoints(User $user): int
    {
        $base = (int) EmployeeViolation::forUser($user->id)
            ->withinPeriod(now()->subMonths(self::ROLLING_MONTHS))
            ->sum('points');

        $bonus = $this->getCleanRecordBonus($user);

        return max(0, $base - $bonus);
    }

    /**
     * Clean record bonus: 3 bulan terakhir tanpa pelanggaran → -10 poin.
     */
    public function getCleanRecordBonus(User $user): int
    {
        $hasRecent = EmployeeViolation::forUser($user->id)
            ->where('occurred_at', '>=', now()->subMonths(self::CLEAN_MONTHS_REQUIRED))
            ->exists();

        return $hasRecent ? 0 : self::CLEAN_BONUS_POINTS;
    }

    /**
     * Breakdown poin per kategori untuk ditampilkan di UI.
     */
    public function getPointsBreakdown(User $user): array
    {
        $rows = EmployeeViolation::forUser($user->id)
            ->withinPeriod(now()->subMonths(self::ROLLING_MONTHS))
            ->join('disciplinary_violations', 'disciplinary_violations.id', '=', 'employee_violations.disciplinary_violation_id')
            ->selectRaw('disciplinary_violations.category, sum(employee_violations.points) as total, count(*) as count')
            ->groupBy('disciplinary_violations.category')
            ->get();

        $breakdown = [
            DisciplinaryViolation::CATEGORY_RINGAN => ['points' => 0, 'count' => 0],
            DisciplinaryViolation::CATEGORY_SEDANG => ['points' => 0, 'count' => 0],
            DisciplinaryViolation::CATEGORY_BERAT => ['points' => 0, 'count' => 0],
        ];

        foreach ($rows as $row) {
            $breakdown[$row->category] = [
                'points' => (int) $row->total,
                'count' => (int) $row->count,
            ];
        }

        return $breakdown;
    }

    /**
     * Catat pelanggaran manual + trigger threshold.
     */
    public function recordViolation(User $user, DisciplinaryViolation $violation, \DateTimeInterface $occurredAt, ?string $notes = null, ?string $evidencePath = null, ?User $reporter = null): EmployeeViolation
    {
        return DB::transaction(function () use ($user, $violation, $occurredAt, $notes, $evidencePath, $reporter) {
            $record = EmployeeViolation::create([
                'user_id' => $user->id,
                'disciplinary_violation_id' => $violation->id,
                'occurred_at' => $occurredAt,
                'points' => $violation->points,
                'notes' => $notes,
                'evidence_path' => $evidencePath,
                'reported_by' => $reporter?->id ?? auth()->id(),
                'source' => EmployeeViolation::SOURCE_MANUAL,
            ]);

            $this->checkAndTriggerActions($user);

            return $record;
        });
    }

    /**
     * Cek ambang poin & buat aksi disiplin bertingkat.
     * Satu aksi per level aktif — tidak double trigger.
     */
    public function checkAndTriggerActions(User $user): void
    {
        $points = $this->getActivePoints($user);

        $thresholds = [
            self::THRESHOLD_PHK_EVAL => DisciplinaryAction::TYPE_PHK_EVAL,
            self::THRESHOLD_SP3 => DisciplinaryAction::TYPE_SP3,
            self::THRESHOLD_SP2 => DisciplinaryAction::TYPE_SP2,
            self::THRESHOLD_SP1 => DisciplinaryAction::TYPE_SP1,
            self::THRESHOLD_TEGURAN => DisciplinaryAction::TYPE_TEGURAN_LISAN,
        ];

        foreach ($thresholds as $threshold => $actionType) {
            if ($points < $threshold) {
                continue;
            }

            $exists = DisciplinaryAction::forUser($user->id)
                ->where('action_type', $actionType)
                ->whereIn('status', [DisciplinaryAction::STATUS_ACTIVE, DisciplinaryAction::STATUS_RESOLVED])
                ->exists();

            if (! $exists) {
                $this->issueAction($user, $actionType, $points);
            }
        }
    }

    /**
     * Terbitkan aksi (draf — HR konfirmasi via confirm()).
     */
    public function issueAction(User $user, string $actionType, int $triggeredPoints, ?string $notes = null): DisciplinaryAction
    {
        $data = [
            'user_id' => $user->id,
            'action_type' => $actionType,
            'triggered_points' => $triggeredPoints,
            'status' => DisciplinaryAction::STATUS_ACTIVE,
            'issued_at' => now(),
            'notes' => $notes,
            'issued_by' => auth()->id(),
        ];

        if ($actionType === DisciplinaryAction::TYPE_SP1) {
            $data['freeze_until'] = now()->addMonths(3)->toDateString();
        }

        if ($actionType === DisciplinaryAction::TYPE_SP2) {
            $data['required_remediation'] = true;
        }

        if ($actionType === DisciplinaryAction::TYPE_SP3) {
            $data['suspend_incentive'] = true;
        }

        return DisciplinaryAction::create($data);
    }

    /**
     * HR mengonfirmasi penerbitan SP (draf → final).
     */
    public function confirmAction(DisciplinaryAction $action, User $confirmor): DisciplinaryAction
    {
        $action->update([
            'confirmed_by' => $confirmor->id,
            'confirmed_at' => now(),
        ]);

        return $action;
    }

    /**
     * Auto-detect pelanggaran keterlambatan dari absensi.
     * Idempoten — 1 absensi hanya menghasilkan 1 pelanggaran (cek notes marker).
     * Dipanggil command harian.
     */
    public function autoDetectFromAttendance(?\DateTimeInterface $date = null): int
    {
        $date ??= now()->toDateString();
        $marker = 'AUTO-ATTendance:' . $date;

        $attendances = Attendance::query()
            ->whereDate('date', $date)
            ->where('status', 'late')
            ->whereNotNull('late_duration')
            ->where('late_duration', '>', 0)
            ->with('user')
            ->get();

        $created = 0;

        foreach ($attendances as $attendance) {
            // skip jika sudah terdeteksi untuk tanggal ini
            $exists = EmployeeViolation::forUser($attendance->user_id)
                ->where('source', EmployeeViolation::SOURCE_AUTO_ATTENDANCE)
                ->whereDate('occurred_at', $date)
                ->exists();

            if ($exists) {
                continue;
            }

            $major = $attendance->late_duration > self::LATE_MAJOR_MINUTES;

            $violation = DisciplinaryViolation::active()
                ->where('code', $major ? 'LT-02' : 'LT-01')
                ->first();

            if (! $violation) {
                continue;
            }

            EmployeeViolation::create([
                'user_id' => $attendance->user_id,
                'disciplinary_violation_id' => $violation->id,
                'occurred_at' => $attendance->date . ' ' . ($attendance->check_in_time ?? '08:00:00'),
                'points' => $violation->points,
                'notes' => 'Auto: terlambat ' . $attendance->late_duration . ' menit (' . $attendance->date . ')',
                'source' => EmployeeViolation::SOURCE_AUTO_ATTENDANCE,
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Feed pelanggaran bulanan (grafik dashboard).
     */
    public function getMonthlyPointsFeed(int $months = 6): array
    {
        $from = now()->subMonths($months - 1)->startOfMonth();

        $rows = DB::table('employee_violations')
            ->join('disciplinary_violations', 'disciplinary_violations.id', '=', 'employee_violations.disciplinary_violation_id')
            ->where('employee_violations.occurred_at', '>=', $from)
            ->selectRaw("DATE_FORMAT(employee_violations.occurred_at, '%Y-%m') as month, disciplinary_violations.category, sum(employee_violations.points) as points, count(*) as count")
            ->groupBy('month', 'disciplinary_violations.category')
            ->orderBy('month')
            ->get();

        $monthsList = collect(range(0, $months - 1))
            ->map(fn ($i) => now()->subMonths($months - 1 - $i)->format('Y-m'))
            ->values();

        $feed = [];
        foreach ($monthsList as $month) {
            $feed[$month] = [
                DisciplinaryViolation::CATEGORY_RINGAN => 0,
                DisciplinaryViolation::CATEGORY_SEDANG => 0,
                DisciplinaryViolation::CATEGORY_BERAT => 0,
            ];
        }

        foreach ($rows as $row) {
            if (isset($feed[$row->month])) {
                $feed[$row->month][$row->category] = (int) $row->points;
            }
        }

        return $feed;
    }

    /**
     * Poin semua karyawan aktif untuk ranking/feed dashboard.
     */
    public function getTopViolators(int $limit = 10): array
    {
        return EmployeeViolation::query()
            ->withinPeriod(now()->subMonths(self::ROLLING_MONTHS))
            ->join('users', 'users.id', '=', 'employee_violations.user_id')
            ->selectRaw('users.id, users.name, sum(employee_violations.points) as total_points, count(*) as violation_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
