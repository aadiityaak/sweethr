<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeductionRule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeductionRuleController extends Controller
{
    public function index()
    {
        $deductionRules = DeductionRule::orderBy('type')->orderBy('name')->get();

        return Inertia::render('admin/DeductionRule/Index', [
            'deductionRules' => $deductionRules,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/DeductionRule/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:late,absent,excess_leave,other',
            'calculation_method' => 'required|in:fixed,per_minute,per_hour,per_day,percentage',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        DeductionRule::create($validated);

        return redirect()
            ->route('admin.deduction-rules.index')
            ->with('success', 'Aturan potongan berhasil ditambahkan');
    }

    public function edit(DeductionRule $deductionRule)
    {
        return Inertia::render('admin/DeductionRule/Edit', [
            'deductionRule' => $deductionRule,
        ]);
    }

    public function update(Request $request, DeductionRule $deductionRule)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:late,absent,excess_leave,other',
            'calculation_method' => 'required|in:fixed,per_minute,per_hour,per_day,percentage',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $deductionRule->update($validated);

        return redirect()
            ->route('admin.deduction-rules.index')
            ->with('success', 'Aturan potongan berhasil diperbarui');
    }

    public function destroy(DeductionRule $deductionRule)
    {
        $deductionRule->delete();

        return redirect()
            ->route('admin.deduction-rules.index')
            ->with('success', 'Aturan potongan berhasil dihapus');
    }
}
