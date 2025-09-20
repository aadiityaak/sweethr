<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\DeductionRule;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function generatePayrolls(int $year, int $month, ?array $userIds = null): void
    {
        $users = User::with('currentSalarySetting')
            ->active()
            ->when($userIds, function ($query, $userIds) {
                return $query->whereIn('id', $userIds);
            })
            ->get();

        foreach ($users as $user) {
            $this->generatePayrollForUser($user, $year, $month);
        }
    }

    public function generatePayrollForUser(User $user, int $year, int $month): Payroll
    {
        // Check if payroll already exists
        $existingPayroll = Payroll::forUser($user->id)
            ->forPeriod($year, $month)
            ->first();

        if ($existingPayroll) {
            return $this->regeneratePayroll($existingPayroll);
        }

        $salarySetting = $user->currentSalarySetting;
        if (! $salarySetting) {
            throw new \Exception("User {$user->name} belum memiliki pengaturan gaji");
        }

        // Calculate attendance data
        $attendanceData = $this->calculateAttendanceData($user, $year, $month);

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

        return DB::transaction(function () use ($user, $year, $month, $salarySetting, $attendanceData, $grossSalary, $deductions, $totalDeductions, $netSalary, $overtimePay) {
            // Create payroll
            $payroll = Payroll::create([
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
            ]);

            // Create allowance details
            if ($salarySetting->allowances) {
                foreach ($salarySetting->allowances as $allowance) {
                    PayrollDetail::create([
                        'payroll_id' => $payroll->id,
                        'type' => 'allowance',
                        'name' => $allowance['name'],
                        'amount' => $allowance['amount'],
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
        // Delete existing details
        $payroll->details()->delete();

        // Generate new payroll for same period
        $newPayroll = $this->generatePayrollForUser(
            $payroll->user,
            $payroll->period_year,
            $payroll->period_month
        );

        // Delete old payroll
        $payroll->delete();

        return $newPayroll;
    }

    private function calculateAttendanceData(User $user, int $year, int $month): array
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

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
        // Assuming late_duration is stored in minutes, or calculate from check_in_time vs expected time
        $lateMinutes = 0;
        foreach ($attendances as $attendance) {
            // If attendance has a method to calculate late duration, use it
            // For now, we'll assume no late time calculation
            $lateMinutes += 0; // Will be updated when we have proper late calculation
        }

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

            if ($amount > 0) {
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
