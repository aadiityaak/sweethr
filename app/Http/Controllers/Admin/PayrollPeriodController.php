<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollPeriod;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::withCount('payrolls')
            ->orderBy('start_date', 'desc')
            ->get();

        $activePeriod = PayrollPeriod::getActive();

        return Inertia::render('Admin/PayrollPeriod/Index', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/PayrollPeriod/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cut_off_day' => 'required|integer|min:1|max:28',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create period based on cut-off day
        $cutOffDay = $validated['cut_off_day'];
        $now = now();

        $startDate = $now->day >= $cutOffDay
            ? $now->copy()->startOfMonth()->day($cutOffDay)
            : $now->copy()->subMonth()->startOfMonth()->day($cutOffDay);

        $endDate = $startDate->copy()->addMonth()->subDay();

        // Deactivate all existing periods
        PayrollPeriod::where('is_active', true)->update(['is_active' => false]);

        // Create new period
        $period = PayrollPeriod::create([
            'name' => $validated['name'],
            'cut_off_day' => $cutOffDay,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('admin.payroll-periods.index')
            ->with('success', "Periode gaji berhasil dibuat: {$period->formatted_period}");
    }

    public function show(PayrollPeriod $payrollPeriod)
    {
        $payrollPeriod->load('payrolls.user');

        return Inertia::render('Admin/PayrollPeriod/Show', [
            'period' => $payrollPeriod,
        ]);
    }

    public function edit(PayrollPeriod $payrollPeriod)
    {
        return Inertia::render('Admin/PayrollPeriod/Edit', [
            'period' => $payrollPeriod,
        ]);
    }

    public function update(Request $request, PayrollPeriod $payrollPeriod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        // If setting this period as active, deactivate all others
        if ($validated['is_active']) {
            PayrollPeriod::where('is_active', true)->update(['is_active' => false]);
        }

        $payrollPeriod->update($validated);

        return redirect()->route('admin.payroll-periods.index')
            ->with('success', 'Periode gaji berhasil diperbarui');
    }

    public function destroy(PayrollPeriod $payrollPeriod)
    {
        if ($payrollPeriod->payrolls()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus periode yang sudah memiliki payroll.');
        }

        $payrollPeriod->delete();

        return redirect()->route('admin.payroll-periods.index')
            ->with('success', 'Periode gaji berhasil dihapus');
    }

    public function setActive(PayrollPeriod $payrollPeriod)
    {
        // Deactivate all periods
        PayrollPeriod::where('is_active', true)->update(['is_active' => false]);

        // Activate selected period
        $payrollPeriod->update(['is_active' => true]);

        return back()->with('success', 'Periode gaji berhasil diaktifkan');
    }

    public function generateYearlyPeriods(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2030',
            'cut_off_day' => 'required|integer|min:1|max:28',
        ]);

        $periods = PayrollPeriod::generateYearlyPeriods($validated['year'], $validated['cut_off_day']);

        foreach ($periods as $periodData) {
            PayrollPeriod::create($periodData);
        }

        return back()->with('success', count($periods).' periode gaji berhasil dibuat untuk tahun '.$validated['year']);
    }

    public function generatePayroll(PayrollPeriod $period, PayrollService $payrollService)
    {
        $result = $payrollService->generatePayrollsForPeriod(
            $period->start_date->year,
            $period->start_date->month,
            $period
        );

        if ($result['generated'] > 0) {
            return back()->with('success', "Berhasil generate {$result['generated']} payroll untuk periode {$period->payroll_period_name}");
        }

        return back()->with('info', 'Tidak ada payroll yang di-generate. Semua karyawan mungkin sudah memiliki payroll untuk periode ini.');
    }
}
