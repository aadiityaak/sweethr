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
        // Force fresh data from database, clear any caching
        \Cache::forget('employees_index');

        // Add cache-control headers to prevent browser caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $query = User::with([
            'department',
            'position',
            'employeeShifts' => function ($q) {
                $q->with('workShift:id,name,code,start_time,end_time')
                    ->active()
                    ->latest();
            },
        ]);

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $request->search . '%');
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

        // Apply sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        // Define sortable fields and their mappings
        $sortableFields = [
            'name' => 'name',
            'email' => 'email',
            'employee_id' => 'employee_id',
            'hire_date' => 'hire_date',
            'department' => 'departments.name',
            'position' => 'positions.title',
            'status' => 'employment_status',
        ];

        if (array_key_exists($sortField, $sortableFields)) {
            $sortColumn = $sortableFields[$sortField];

            // Handle relationship sorting
            if ($sortField === 'department') {
                $query->leftJoin('departments', 'users.department_id', '=', 'departments.id')
                    ->orderBy('departments.name', $sortDirection)
                    ->select('users.*');
            } elseif ($sortField === 'position') {
                $query->leftJoin('positions', 'users.position_id', '=', 'positions.id')
                    ->orderBy('positions.title', $sortDirection)
                    ->select('users.*');
            } else {
                $query->orderBy($sortColumn, $sortDirection);
            }
        } else {
            // Default sorting
            $query->orderBy('name', 'asc');
        }

        $employees = $query->paginate(15);

        // Get departments and positions for filters
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title']);

        // Get employee statistics
        $totalEmployees = User::count();
        $activeEmployees = User::where('employment_status', 'active')->count();
        $inactiveEmployees = User::where('employment_status', 'inactive')->count();
        $terminatedEmployees = User::where('employment_status', 'terminated')->count();

        return Inertia::render('admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'positions' => $positions,
            'stats' => [
                'total' => $totalEmployees,
                'active' => $activeEmployees,
                'inactive' => $inactiveEmployees,
                'terminated' => $terminatedEmployees,
            ],
            'filters' => $request->only(['search', 'department', 'position', 'status', 'sort', 'direction']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            // Add timestamp to force fresh data
            'timestamp' => now()->timestamp,
        ]);
    }

    public function create(): Response
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title', 'level', 'department_id']);

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
            'phone' => 'required|numeric|digits_between:10,15',
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

        return to_route('employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan ke sistem.');
    }

    // Show method removed to restrict detailed employee data access
    // Employees can only be viewed from the main list (index page)

    public function edit(User $employee): Response
    {
        // Get fresh data from database with relationships to avoid any caching issues
        $freshEmployee = User::with(['department', 'position'])
            ->findOrFail($employee->id);

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('title')->get(['id', 'title', 'level', 'department_id']);

        return Inertia::render('admin/Employees/Edit', [
            'employee' => $freshEmployee,
            'departments' => $departments,
            'positions' => $positions,
        ]);
    }

    public function update(Request $request, User $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $employee->id,
            'employee_id' => 'required|string|unique:users,employee_id,' . $employee->id,
            'phone' => 'required|numeric|digits_between:10,15',
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

        return to_route('employees.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee)
    {
        \Log::info('Delete employee request received', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'current_user_id' => auth()->id(),
            'current_user_is_admin' => auth()->user()?->is_admin,
        ]);

        // Prevent self-deletion
        if ($employee->id === auth()->id()) {
            \Log::warning('Self-deletion attempt blocked', ['employee_id' => $employee->id]);

            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Check if employee exists before deletion
        $employeeExists = User::find($employee->id);
        if (! $employeeExists) {
            \Log::warning('Attempted to delete non-existent employee', ['employee_id' => $employee->id]);

            return back()->with('error', 'Karyawan tidak ditemukan.');
        }

        try {
            // Use database transaction for safety
            \DB::transaction(function () use ($employee) {
                \Log::info('Starting employee deletion transaction', ['employee_id' => $employee->id]);

                // Check if employee is referenced by other records that would prevent deletion
                $constraintChecks = [
                    'departments as manager' => \DB::table('departments')->where('manager_id', $employee->id)->exists(),
                    'announcements created by' => \DB::table('announcements')->where('created_by', $employee->id)->exists(),
                    'leave_requests approved by' => \DB::table('leave_requests')->where('approved_by', $employee->id)->exists(),
                    'shift_change_requests reviewed by' => \DB::table('shift_change_requests')->where('reviewed_by', $employee->id)->exists(),
                    'shift_swaps approved by' => \DB::table('shift_swaps')->where('approved_by', $employee->id)->exists(),
                    'users managed by' => \DB::table('users')->where('manager_id', $employee->id)->exists(),
                ];

                $hasConstraints = false;
                $constraintMessages = [];
                foreach ($constraintChecks as $description => $exists) {
                    if ($exists) {
                        $hasConstraints = true;
                        $constraintMessages[] = $description;
                    }
                }

                if ($hasConstraints) {
                    throw new \Exception('Cannot delete employee: still referenced by ' . implode(', ', $constraintMessages));
                }

                // Delete related records that can be safely deleted
                $employee->attendances()->delete();
                $employee->leaveRequests()->where('approved_by', '!=', $employee->id)->delete();
                $employee->employeeShifts()->delete();
                $employee->salarySettings()->delete();
                $employee->payrolls()->delete();
                $employee->documents()->delete();
                $employee->uploadedDocuments()->update(['uploaded_by' => null]);
                $employee->shiftChangeRequests()->where('reviewed_by', '!=', $employee->id)->delete();
                $employee->shiftSwapsAsRequester()->delete();
                $employee->shiftSwapsAsTarget()->delete();

                // Delete the employee
                $result = $employee->delete();

                \Log::info('Employee deletion result', [
                    'employee_id' => $employee->id,
                    'deletion_result' => $result,
                ]);

                if (! $result) {
                    throw new \Exception('Employee deletion failed');
                }
            });

            \Log::info('Employee deleted successfully', ['employee_id' => $employee->id]);

            // Verify deletion
            $stillExists = User::find($employee->id);
            if ($stillExists) {
                \Log::error('Employee still exists after deletion!', [
                    'employee_id' => $employee->id,
                    'still_exists' => true,
                ]);

                return back()->with('error', 'Gagal menghapus karyawan dari sistem.');
            }

            \Log::info('Employee deletion verified - record no longer exists', ['employee_id' => $employee->id]);
        } catch (\Exception $e) {
            \Log::error('Employee deletion failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal menghapus karyawan: ' . $e->getMessage());
        }

        return to_route('employees.index')
            ->with('success', 'Karyawan berhasil dihapus dari sistem.');
    }
}
