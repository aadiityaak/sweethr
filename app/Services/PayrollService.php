<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DeductionRule;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Validate payroll prerequisites for a specific period (with cut-off support)
     */
    public function validatePayrollPrerequisites(int $year, int $month, ?array $userIds = null): array
    {
        return $this->validatePayrollPrerequisitesForPeriod($year, $month, null, $userIds);
    }

    /**
     * Validate payroll prerequisites for a specific payroll period
     */
    public function validatePayrollPrerequisitesForPeriod(int $year, int $month, ?PayrollPeriod $period = null, ?array $userIds = null): array
    {
        $users = User::with('currentSalarySetting')
            ->active()
            ->where('is_admin', false)
            ->when($userIds, function ($query, $userIds) {
                return $query->whereIn('id', $userIds);
            })
            ->get();

        if ($users->isEmpty()) {
            return [
                'valid' => false,
                'message' => 'Tidak ada karyawan aktif yang ditemukan untuk di-generate payroll.',
                'details' => [],
            ];
        }

        $usersWithoutSalary = [];
        $usersWithInvalidSalary = [];

        foreach ($users as $user) {
            $salarySetting = $user->currentSalarySetting;

            if (! $salarySetting) {
                $usersWithoutSalary[] = $user->name;
            } elseif ($salarySetting->base_salary <= 0) {
                $usersWithInvalidSalary[] = $user->name;
            }
        }

        if (! empty($usersWithoutSalary) || ! empty($usersWithInvalidSalary)) {
            $details = [];
            $message = 'Tidak dapat generate payroll karena ada masalah:';

            if (! empty($usersWithoutSalary)) {
                $details['missing_salary'] = $usersWithoutSalary;
                $message .= "\n• Karyawan belum memiliki pengaturan gaji: ".implode(', ', $usersWithoutSalary);
            }

            if (! empty($usersWithInvalidSalary)) {
                $details['invalid_salary'] = $usersWithInvalidSalary;
                $message .= "\n• Karyawan dengan gaji tidak valid: ".implode(', ', $usersWithInvalidSalary);
            }

            return [
                'valid' => false,
                'message' => $message,
                'details' => $details,
            ];
        }

        return [
            'valid' => true,
            'message' => 'Semua prerequisite terpenuhi',
            'users_count' => $users->count(),
        ];
    }

    public function generatePayrolls(int $year, int $month, ?array $userIds = null): array
    {
        return $this->generatePayrollsForPeriod($year, $month, null, $userIds);
    }

    /**
     * Generate payrolls for a specific period (with cut-off support)
     */
    public function generatePayrollsForPeriod(int $year, int $month, ?PayrollPeriod $period = null, ?array $userIds = null): array
    {
        $users = User::with('currentSalarySetting')
            ->active()
            ->where('is_admin', false) // Exclude admin users from payroll generation
            ->when($userIds, function ($query, $userIds) {
                return $query->whereIn('id', $userIds);
            })
            ->get();

        $generated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                // Check if payroll already exists for this period
                $existingPayroll = $period
                    ? Payroll::where('user_id', $user->id)
                        ->where('payroll_period_id', $period->id)
                        ->first()
                    : Payroll::forUser($user->id)
                        ->forPeriod($year, $month)
                        ->first();

                if ($existingPayroll) {
                    $skipped++;

                    continue;
                }

                $this->generatePayrollForUser($user, $year, $month, $period);
                $generated++;
            } catch (\Exception $e) {
                $errors[] = "Error untuk {$user->name}: ".$e->getMessage();
            }
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
            'errors' => $errors,
            'total_users' => $users->count(),
            'period' => $period ? $period->payroll_period_name : null,
        ];
    }

    public function generatePayrollForUser(User $user, int $year, int $month, ?PayrollPeriod $period = null, bool $skipExistingCheck = false): Payroll
    {
        // Check if payroll already exists (skip when regenerating)
        if (! $skipExistingCheck) {
            $existingPayroll = $period
                ? Payroll::where('user_id', $user->id)
                    ->where('payroll_period_id', $period->id)
                    ->first()
                : Payroll::forUser($user->id)
                    ->forPeriod($year, $month)
                    ->first();

            if ($existingPayroll) {
                return $this->regeneratePayroll($existingPayroll);
            }
        }

        $salarySetting = $user->currentSalarySetting;
        if (! $salarySetting) {
            throw new \Exception("User {$user->name} belum memiliki pengaturan gaji");
        }

        // Calculate attendance data with period support
        $attendanceData = $period
            ? $this->calculateAttendanceDataForPeriod($user, $period)
            : $this->calculateAttendanceData($user, $year, $month);

        // Calculate gross salary
        $grossSalary = $salarySetting->base_salary + ($salarySetting->total_allowances ?? 0);

        // Calculate overtime
        $overtimePay = $this->calculateOvertimePay($salarySetting, $attendanceData['overtime_hours']);
        $grossSalary += $overtimePay;

        // Calculate deductions
        $deductions = $this->calculateDeductions($user, $salarySetting, $attendanceData);
        $totalDeductions = collect($deductions)->sum('amount');

        // Net salary
        $netSalary = $grossSalary - $totalDeductions;

        return DB::transaction(function () use ($user, $year, $month, $period, $salarySetting, $attendanceData, $grossSalary, $deductions, $totalDeductions, $netSalary, $overtimePay) {
            // Create payroll
            $payrollData = [
                'user_id' => $user->id,
                'period_year' => $year,
                'period_month' => $month,
                'base_salary' => $salarySetting->base_salary,
                'allowances' => $salarySetting->allowances,
                'gross_salary' => $grossSalary,
                'deductions' => $deductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'working_days' => $attendanceData['working_days'],
                'actual_working_days' => $attendanceData['actual_working_days'],
                'overtime_hours' => $attendanceData['overtime_hours'],
                'late_minutes' => $attendanceData['late_minutes'],
                'absent_days' => $attendanceData['absent_days'],
            ];

            // Add period-specific data if using payroll period
            if ($period) {
                $payrollData['payroll_period_id'] = $period->id;
                $payrollData['start_date'] = $period->start_date;
                $payrollData['end_date'] = $period->end_date;
            }

            $payroll = Payroll::create($payrollData);

            // Create allowance details
            if ($salarySetting->allowances) {
                foreach ($salarySetting->allowances as $name => $amount) {
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'allowance',
                        'name' => $name,
                        'amount' => $amount,
                        'calculation_basis' => ['type' => 'fixed'],
                    ]);
                }
            }

            // Create overtime detail
            if ($overtimePay > 0) {
                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'overtime',
                    'name' => 'Lembur',
                    'amount' => $overtimePay,
                    'calculation_basis' => [
                        'hours' => $attendanceData['overtime_hours'],
                        'rate' => $salarySetting->overtime_rate,
                    ],
                ]);
            }

            // Create deduction details
            foreach ($deductions as $deduction) {
                PayrollDetail::create([
                    'payroll_id' => $payroll->id,
                    'type' => 'deduction',
                    'name' => $deduction['name'],
                    'description' => $deduction['description'] ?? null,
                    'amount' => $deduction['amount'],
                    'calculation_basis' => $deduction['calculation_basis'] ?? [],
                ]);
            }

            return $payroll;
        });
    }

    public function regeneratePayroll(Payroll $payroll): Payroll
    {
        $user = $payroll->user;
        $year = $payroll->period_year;
        $month = $payroll->period_month;

        return DB::transaction(function () use ($payroll, $user, $year, $month) {
            // Delete existing payroll and its details (cascade will handle details)
            $payroll->delete();

            // Generate new payroll for same period
            return $this->generatePayrollForUser($user, $year, $month, true);
        });
    }

    private function calculateAttendanceData(User $user, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return $this->calculateAttendanceDataInRange($user, $startDate, $endDate);
    }

    /**
     * Calculate attendance data for a specific payroll period (cut-off support)
     */
    private function calculateAttendanceDataForPeriod(User $user, PayrollPeriod $period): array
    {
        return $this->calculateAttendanceDataInRange($user, $period->start_date, $period->end_date);
    }

    /**
     * Calculate attendance data within a specific date range
     */
    private function calculateAttendanceDataInRange(User $user, $startDate, $endDate): array
    {
        // Get all attendances for the period
        $attendances = Attendance::where('user_id', $user->id)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        // Calculate working days (excluding weekends)
        $workingDays = 0;
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            if (! $currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        // Calculate attendance statistics
        $actualWorkingDays = $attendances->where('check_out_time', '!=', null)->count();

        // Calculate late minutes from attendance records
        $lateMinutes = $attendances->sum('late_duration');

        $overtimeHours = $attendances->sum('overtime_duration') ?? 0;
        $absentDays = $workingDays - $actualWorkingDays;

        return [
            'working_days' => $workingDays,
            'actual_working_days' => $actualWorkingDays,
            'late_minutes' => $lateMinutes,
            'overtime_hours' => $overtimeHours / 60, // Convert minutes to hours
            'absent_days' => $absentDays,
        ];
    }

    private function calculateOvertimePay($salarySetting, float $overtimeHours): float
    {
        if ($overtimeHours <= 0) {
            return 0;
        }

        $dailySalary = $salarySetting->base_salary / 22; // Assuming 22 working days
        $hourlySalary = $dailySalary / 8; // Assuming 8 hours per day

        return $overtimeHours * $hourlySalary * $salarySetting->overtime_rate;
    }

    private function calculateDeductions(User $user, $salarySetting, array $attendanceData): array
    {
        $deductions = [];
        $deductionRules = DeductionRule::active()->get();

        foreach ($deductionRules as $rule) {
            $amount = 0;
            $parameters = [];

            switch ($rule->type) {
                case 'late':
                    $parameters = ['minutes' => $attendanceData['late_minutes']];
                    break;
                case 'absent':
                    $parameters = ['days' => $attendanceData['absent_days']];
                    break;
                case 'excess_leave':
                    // TODO: Calculate excess leave days
                    $parameters = ['days' => 0];
                    break;
            }

            $amount = $rule->calculateDeduction($salarySetting->base_salary, $parameters);

            // Always show late deduction even if amount is 0, for other types only show if amount > 0
            if ($amount > 0 || $rule->type === 'late') {
                $deductions[] = [
                    'name' => $rule->name,
                    'description' => $rule->description,
                    'amount' => $amount,
                    'calculation_basis' => array_merge($parameters, [
                        'rule_id' => $rule->id,
                        'method' => $rule->calculation_method,
                    ]),
                ];
            }
        }

        return $deductions;
    }
}
