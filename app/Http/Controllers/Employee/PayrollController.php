<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $year = $request->get('year', now()->year);

        $payrolls = Payroll::forUser($userId)
            ->where('period_year', $year)
            ->with('payrollPeriod')
            ->orderBy('period_month', 'desc')
            ->get()
            ->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'period_name' => $payroll->period_name,
                    'formatted_period' => $payroll->formatted_period,
                    'gross_salary' => (float) $payroll->gross_salary,
                    'total_deductions' => (float) $payroll->total_deductions,
                    'net_salary' => (float) $payroll->net_salary,
                    'working_days' => $payroll->working_days,
                    'actual_working_days' => $payroll->actual_working_days,
                    'overtime_hours' => (float) $payroll->overtime_hours,
                    'late_minutes' => (float) $payroll->late_minutes,
                    'absent_days' => $payroll->absent_days,
                    'created_at' => $payroll->created_at->toISOString(),
                ];
            });

        return Inertia::render('employee/Payroll/Index', [
            'payrolls' => $payrolls,
            'currentYear' => $year,
        ]);
    }

    public function show(Payroll $payroll)
    {
        // Ensure user can only view their own payroll
        if ($payroll->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to payroll data');
        }

        $payroll->load(['user', 'details']);

        return Inertia::render('employee/Payroll/Show', [
            'payroll' => $payroll,
        ]);
    }
}
