<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::with(['department', 'position'])
            ->orderBy('name');

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%')
                    ->orWhere('employee_id', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->department) {
            $query->where('department_id', $request->department);
        }

        if ($request->position) {
            $query->where('position_id', $request->position);
        }

        if ($request->status) {
            $query->where('employment_status', $request->status);
        }

        $employees = $query->paginate(15);

        // Get departments and positions for filters
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title']);

        return Inertia::render('admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'filters' => $request->only(['search', 'department', 'position', 'status']),
        ]);
    }

    public function create(): Response
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title', 'level']);

        return Inertia::render('admin/Employees/Create', [
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'employee_id' => 'required|string|unique:users,employee_id',
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'required|date',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'allow_outside_radius' => 'boolean',
            'employment_status' => 'in:active,inactive,terminated',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['allow_outside_radius'] = $request->boolean('allow_outside_radius');
        $validated['employment_status'] = $validated['employment_status'] ?? 'active';

        User::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(User $employee): Response
    {
        $employee->load(['department', 'position', 'attendances' => function ($query) {
            $query->with('officeLocation')->latest()->take(10);
        }]);

        return Inertia::render('admin/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(User $employee): Response
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title', 'level']);

        return Inertia::render('admin/Employees/Edit', [
            'employee' => $employee,
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$employee->id,
            'employee_id' => 'required|string|unique:users,employee_id,'.$employee->id,
            'phone' => 'nullable|string|max:20',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'hire_date' => 'required|date',
            'password' => 'nullable|string|min:8|confirmed',
            'is_admin' => 'boolean',
            'allow_outside_radius' => 'boolean',
            'employment_status' => 'in:active,inactive,terminated',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_admin'] = $request->boolean('is_admin');
        $validated['allow_outside_radius'] = $request->boolean('allow_outside_radius');
        $validated['employment_status'] = $validated['employment_status'] ?? $employee->employment_status;

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(User $employee)
    {
        // Prevent self-deletion
        if ($employee->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employee deleted successfully.');
    }
}
