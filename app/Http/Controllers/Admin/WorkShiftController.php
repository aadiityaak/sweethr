<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeShift;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkShiftController extends Controller
{
    public function index(Request $request): Response
    {
        $query = WorkShift::query();

        // Apply search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('code', 'like', '%'.$request->search.'%');
            });
        }

        // Apply status filter
        if ($request->has('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $workShifts = $query->withCount(['employeeShifts'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Get statistics
        $stats = [
            'total_shifts' => WorkShift::count(),
            'active_shifts' => WorkShift::where('is_active', true)->count(),
            'inactive_shifts' => WorkShift::where('is_active', false)->count(),
            'employees_assigned' => EmployeeShift::active()->distinct('user_id')->count(),
        ];

        return Inertia::render('admin/WorkShifts/Index', [
            'workShifts' => $workShifts,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/WorkShifts/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:work_shifts',
            'code' => 'required|string|max:10|unique:work_shifts',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration' => 'required|integer|min:0|max:480',
            'overtime_multiplier' => 'required|numeric|min:1|max:3',
            'workdays' => 'required|array|min:1',
            'workdays.*' => 'integer|between:0,6',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Calculate work hours
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);

        // Handle overnight shifts
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $workHours = $endTime->diffInMinutes($startTime) - $request->break_duration;

        WorkShift::create([
            'name' => $request->name,
            'code' => $request->code,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'work_hours' => $workHours,
            'break_duration' => $request->break_duration,
            'overtime_multiplier' => $request->overtime_multiplier,
            'workdays' => $request->workdays,
            'is_night_shift' => $request->is_night_shift ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Work shift created successfully.');
    }

    public function show(WorkShift $workShift): Response
    {
        $workShift->loadCount('employeeShifts');

        // Get assigned employees
        $assignedEmployees = EmployeeShift::with(['user:id,name,employee_id'])
            ->where('work_shift_id', $workShift->id)
            ->active()
            ->get();

        return Inertia::render('admin/WorkShifts/Show', [
            'workShift' => $workShift,
            'assignedEmployees' => $assignedEmployees,
        ]);
    }

    public function edit(WorkShift $workShift): Response
    {
        return Inertia::render('admin/WorkShifts/Edit', [
            'workShift' => $workShift,
        ]);
    }

    public function update(Request $request, WorkShift $workShift)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_shifts')->ignore($workShift->id),
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                Rule::unique('work_shifts')->ignore($workShift->id),
            ],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'break_duration' => 'required|integer|min:0|max:480',
            'overtime_multiplier' => 'required|numeric|min:1|max:3',
            'workdays' => 'required|array|min:1',
            'workdays.*' => 'integer|between:0,6',
            'is_night_shift' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Calculate work hours
        $startTime = \Carbon\Carbon::createFromFormat('H:i', $request->start_time);
        $endTime = \Carbon\Carbon::createFromFormat('H:i', $request->end_time);

        // Handle overnight shifts
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $workHours = $endTime->diffInMinutes($startTime) - $request->break_duration;

        $workShift->update([
            'name' => $request->name,
            'code' => $request->code,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'work_hours' => $workHours,
            'break_duration' => $request->break_duration,
            'overtime_multiplier' => $request->overtime_multiplier,
            'workdays' => $request->workdays,
            'is_night_shift' => $request->is_night_shift ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Work shift updated successfully.');
    }

    public function destroy(WorkShift $workShift)
    {
        // Check if shift has active assignments
        $activeAssignments = $workShift->employeeShifts()->active()->count();

        if ($activeAssignments > 0) {
            return back()->with('error', 'Cannot delete shift with active employee assignments.');
        }

        $workShift->delete();

        return redirect()->route('admin.work-shifts.index')
            ->with('success', 'Work shift deleted successfully.');
    }

    public function assign(Request $request, WorkShift $workShift)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'effective_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:effective_date',
            'assignment_type' => 'required|in:permanent,temporary,weekly,monthly',
        ]);

        foreach ($request->employee_ids as $employeeId) {
            // Deactivate existing assignments for this employee
            EmployeeShift::where('user_id', $employeeId)
                ->active()
                ->update(['is_active' => false]);

            // Create new assignment
            EmployeeShift::create([
                'user_id' => $employeeId,
                'work_shift_id' => $workShift->id,
                'effective_date' => $request->effective_date,
                'end_date' => $request->end_date,
                'assignment_type' => $request->assignment_type,
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Employees assigned to shift successfully.');
    }

    public function unassign(Request $request, WorkShift $workShift)
    {
        $request->validate([
            'employee_shift_id' => 'required|exists:employee_shifts,id',
        ]);

        $employeeShift = EmployeeShift::find($request->employee_shift_id);
        $employeeShift->update(['is_active' => false]);

        return back()->with('success', 'Employee unassigned from shift successfully.');
    }

    public function employees(Request $request): Response
    {
        $query = User::with(['employeeShifts.workShift'])
            ->where('is_admin', false);

        // Apply search filter
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('employee_id', 'like', '%'.$request->search.'%');
            });
        }

        // Apply shift filter
        if ($request->shift) {
            $query->whereHas('employeeShifts', function ($q) use ($request) {
                $q->where('work_shift_id', $request->shift)->active();
            });
        }

        $employees = $query->paginate(15)->withQueryString();
        $shifts = WorkShift::active()->get();

        return Inertia::render('admin/WorkShifts/Employees', [
            'employees' => $employees,
            'shifts' => $shifts,
            'filters' => [
                'search' => $request->search,
                'shift' => $request->shift,
            ],
        ]);
    }
}
