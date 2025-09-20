<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalarySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalarySettingController extends Controller
{
    public function index()
    {
        $users = User::with(['currentSalarySetting', 'department', 'position'])
            ->active()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'employee_id' => $user->employee_id,
                    'department' => $user->department?->name,
                    'position' => $user->position?->name,
                    'current_salary' => $user->currentSalarySetting?->base_salary ?? 0,
                    'total_allowances' => $user->currentSalarySetting?->total_allowances ?? 0,
                    'has_salary_setting' => $user->currentSalarySetting !== null,
                ];
            });

        return Inertia::render('admin/SalarySetting/Index', [
            'users' => $users,
        ]);
    }

    public function show(User $user)
    {
        $user->load(['salarySettings' => function ($query) {
            $query->orderBy('effective_date', 'desc');
        }, 'department', 'position']);

        return Inertia::render('admin/SalarySetting/Show', [
            'user' => $user,
            'salarySettings' => $user->salarySettings,
        ]);
    }

    public function edit(User $user)
    {
        $user->load(['currentSalarySetting', 'department', 'position']);

        return Inertia::render('admin/SalarySetting/Edit', [
            'user' => $user,
            'currentSetting' => $user->currentSalarySetting,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|array',
            'allowances.*.name' => 'required|string',
            'allowances.*.amount' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:1|max:5',
            'effective_date' => 'required|date',
        ]);

        // Deactivate previous settings
        SalarySetting::where('user_id', $user->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        // Create new setting
        SalarySetting::create([
            'user_id' => $user->id,
            'base_salary' => $validated['base_salary'],
            'allowances' => $validated['allowances'] ?? [],
            'overtime_rate' => $validated['overtime_rate'],
            'effective_date' => $validated['effective_date'],
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.salary-settings.index')
            ->with('success', 'Pengaturan gaji berhasil diperbarui');
    }
}
