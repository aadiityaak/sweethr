<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalarySetting;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalarySettingController extends Controller
{
    public function index(Request $request)
    {
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        // Validate sort field
        $allowedSortFields = ['name', 'employee_id', 'department', 'position', 'current_salary', 'total_allowances', 'has_salary_setting'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'name';
        }

        // Validate sort direction
        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $query = User::with(['currentSalarySetting', 'department', 'position'])
            ->active();

        // Apply sorting based on field
        switch ($sortField) {
            case 'department':
                $query->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                    ->orderBy('departments.name', $sortDirection)
                    ->select('users.*');
                break;

            case 'position':
                $query->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                    ->orderBy('positions.name', $sortDirection)
                    ->select('users.*');
                break;

            case 'current_salary':
                $query->leftJoin('salary_settings', function ($join) {
                    $join->on('users.id', '=', 'salary_settings.user_id')
                        ->where('salary_settings.is_active', true);
                })
                    ->orderBy('salary_settings.base_salary', $sortDirection)
                    ->select('users.*');
                break;

            case 'total_allowances':
                $query->leftJoin('salary_settings as ss', function ($join) {
                    $join->on('users.id', '=', 'ss.user_id')
                        ->where('ss.is_active', true);
                })
                    ->orderByRaw("COALESCE(ss.total_allowances, 0) $sortDirection")
                    ->select('users.*');
                break;

            case 'has_salary_setting':
                $query->leftJoin('salary_settings as ss2', function ($join) {
                    $join->on('users.id', '=', 'ss2.user_id')
                        ->where('ss2.is_active', true);
                })
                    ->orderByRaw("CASE WHEN ss2.id IS NOT NULL THEN 1 ELSE 0 END $sortDirection")
                    ->select('users.*');
                break;

            default:
                $query->orderBy($sortField, $sortDirection);
                break;
        }

        $users = $query->paginate(20)
            ->through(function ($user) {
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
            'filters' => [
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
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
            'overtime_rate' => 'required|numeric|min:0|max:5',
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
