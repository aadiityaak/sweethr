<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        // Get filters
        $filters = $request->only(['date', 'status', 'department', 'search']);
        $date = $filters['date'] ?? Carbon::today()->format('Y-m-d');

        // Query attendance records with filters
        $attendanceQuery = Attendance::with([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation:id,name,address',
        ])
            ->where('date', $date)
            ->when($filters['status'] ?? false, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['department'] ?? false, function ($query, $department) {
                $query->whereHas('user', function ($q) use ($department) {
                    $q->where('department_id', $department);
                });
            })
            ->when($filters['search'] ?? false, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            });

        $attendanceRecords = $attendanceQuery->orderBy('created_at', 'desc')->get();

        // Calculate attendance statistics for the selected date
        $totalEmployees = User::where('is_admin', false)->count();
        $presentToday = Attendance::where('date', $date)
            ->whereIn('status', ['present', 'late'])
            ->count();
        $lateToday = Attendance::where('date', $date)
            ->where('status', 'late')
            ->count();
        $absentToday = $totalEmployees - $presentToday;
        $onLeaveToday = 0; // This would need leave request integration

        $attendanceRate = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100) : 0;

        // Calculate average work hours and overtime for the date
        $avgWorkHours = Attendance::where('date', $date)
            ->whereNotNull('work_duration')
            ->avg('work_duration') ?? 0;
        $avgWorkHours = round($avgWorkHours / 60, 1); // Convert to hours

        $totalOvertimeHours = Attendance::where('date', $date)
            ->sum('overtime_duration') ?? 0;
        $totalOvertimeHours = round($totalOvertimeHours / 60, 1); // Convert to hours

        $stats = [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'late_today' => $lateToday,
            'absent_today' => $absentToday,
            'on_leave_today' => $onLeaveToday,
            'attendance_rate' => $attendanceRate,
            'average_work_hours' => $avgWorkHours,
            'total_overtime_hours' => $totalOvertimeHours,
        ];

        // Get departments for filter dropdown
        $departments = Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Attendance/Index', [
            'attendanceRecords' => $attendanceRecords,
            'stats' => $stats,
            'filters' => $filters,
            'departments' => $departments,
        ]);
    }

    public function show(Attendance $attendance): Response
    {
        // Load the attendance record with all related data
        $attendance->load([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation',
        ]);

        return Inertia::render('admin/Attendance/Show', [
            'attendance' => $attendance,
        ]);
    }

    public function edit(Attendance $attendance): Response
    {
        // Load the attendance record with all related data
        $attendance->load([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation',
        ]);

        // Get all office locations for the dropdown
        $officeLocations = OfficeLocation::select('id', 'name', 'address')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Attendance/Edit', [
            'attendance' => $attendance,
            'officeLocations' => $officeLocations,
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $data = $request->validated();

        // Calculate work duration if both check in and check out times are provided
        if ($data['check_in_time'] && $data['check_out_time']) {
            $checkIn = Carbon::parse($data['check_in_time']);
            $checkOut = Carbon::parse($data['check_out_time']);
            $data['work_duration'] = $checkOut->diffInMinutes($checkIn);
        } else {
            $data['work_duration'] = null;
        }

        // Update the attendance record
        $attendance->update($data);

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', 'Data kehadiran berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $employeeName = $attendance->user->name;
        $date = $attendance->date->format('d/m/Y');

        $attendance->delete();

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', "Data kehadiran {$employeeName} tanggal {$date} berhasil dihapus.");
    }
}
