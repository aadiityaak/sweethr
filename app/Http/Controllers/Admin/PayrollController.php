<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $payrolls = Payroll::with(['user:id,name,employee_id'])
            ->forPeriod($year, $month)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($payroll) {
                return [
                    'id' => $payroll->id,
                    'user' => $payroll->user,
                    'gross_salary' => (float) $payroll->gross_salary,
                    'total_deductions' => (float) $payroll->total_deductions,
                    'net_salary' => (float) $payroll->net_salary,
                    'working_days' => $payroll->working_days,
                    'actual_working_days' => $payroll->actual_working_days,
                    'overtime_hours' => (float) $payroll->overtime_hours,
                ];
            });

        return Inertia::render('admin/Payroll/Index', [
            'payrolls' => $payrolls,
            'currentYear' => (int) $year,
            'currentMonth' => (int) $month,
        ]);
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['user', 'details']);

        return Inertia::render('admin/Payroll/Show', [
            'payroll' => $payroll,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $year = $validated['year'];
        $month = $validated['month'];
        $userIds = $validated['user_ids'] ?? null;

        $this->payrollService->generatePayrolls($year, $month, $userIds);

        return redirect()
            ->route('admin.payrolls.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Payroll berhasil digenerate');
    }

    public function regenerate(Payroll $payroll)
    {
        try {
            $this->payrollService->regeneratePayroll($payroll);

            return back()->with('success', 'Payroll berhasil digenerate ulang');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
