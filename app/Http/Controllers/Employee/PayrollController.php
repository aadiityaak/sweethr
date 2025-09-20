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
            ->orderBy('period_month', 'desc')
            ->get();

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
