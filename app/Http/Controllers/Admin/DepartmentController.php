<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::with(['manager:id,name,employee_id'])
            ->withCount(['employees' => function ($query) {
                $query->where('employment_status', 'active');
            }]);

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('code', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $departments = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $stats = [
            'total_departments' => Department::count(),
            'active_departments' => Department::where('is_active', true)->count(),
            'inactive_departments' => Department::where('is_active', false)->count(),
            'departments_with_managers' => Department::whereNotNull('manager_id')->where('is_active', true)->count(),
        ];

        return Inertia::render('admin/Departments/Index', [
            'departments' => $departments,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        // Get potential managers (active employees)
        $managers = User::where('employment_status', 'active')
            ->where('is_admin', false)
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Departments/Create', [
            'managers' => $managers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect('/admin/departments')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load(['manager:id,name,employee_id,phone,email']);

        // Get department employees with their positions
        $employees = User::where('department_id', $department->id)
            ->where('employment_status', 'active')
            ->with(['position:id,name', 'manager:id,name'])
            ->select('id', 'name', 'employee_id', 'email', 'phone', 'position_id', 'manager_id', 'hire_date')
            ->orderBy('name')
            ->get();

        // Get department statistics
        $stats = [
            'total_employees' => $employees->count(),
            'positions_count' => $department->positions()->count(),
            'active_positions' => $department->positions()->where('is_active', true)->count(),
            'avg_tenure_months' => $employees->where('hire_date', '!=', null)
                ->avg(function ($employee) {
                    return $employee->hire_date ? now()->diffInMonths($employee->hire_date) : 0;
                }),
        ];

        return Inertia::render('admin/Departments/Show', [
            'department' => $department,
            'employees' => $employees,
            'stats' => $stats,
        ]);
    }

    public function edit(Department $department)
    {
        // Get potential managers (active employees, excluding current manager)
        $managers = User::where('employment_status', 'active')
            ->where('is_admin', false)
            ->select('id', 'name', 'employee_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Departments/Edit', [
            'department' => $department,
            'managers' => $managers,
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departments,code,'.$department->id,
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect('/admin/departments')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Check if department has employees
        $employeeCount = User::where('department_id', $department->id)
            ->where('employment_status', 'active')
            ->count();

        if ($employeeCount > 0) {
            return back()->with('error', 'Cannot delete department with active employees.');
        }

        // Check if department has positions
        $positionCount = $department->positions()->count();
        if ($positionCount > 0) {
            return back()->with('error', 'Cannot delete department with existing positions.');
        }

        $department->delete();

        return redirect('/admin/departments')
            ->with('success', 'Department deleted successfully.');
    }
}
