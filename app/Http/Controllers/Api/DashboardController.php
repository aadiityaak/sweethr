<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->with('officeLocation')
            ->first();

        // Get pending leave requests (if manager)
        $pendingLeaves = [];
        if ($user->is_admin || $user->subordinates()->count() > 0) {
            $pendingLeaves = LeaveRequest::pending()
                ->with(['user:id,name,employee_id', 'leaveType:id,name'])
                ->limit(5)
                ->get();
        }

        // Get recent announcements (placeholder)
        $announcements = [
            [
                'id' => 1,
                'title' => 'Selamat Datang di Sistem HR',
                'message' => 'HR Management System is now live!',
                'created_at' => now()->subDays(1),
            ],
        ];

        return response()->json([
            'user' => $user->load(['department', 'position']),
            'today_attendance' => $todayAttendance,
            'pending_leaves' => $pendingLeaves,
            'announcements' => $announcements,
        ]);
    }

    public function stats(Request $request)
    {
        $user = $request->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        if ($user->is_admin) {
            // Admin stats - company-wide
            $stats = [
                'total_employees' => User::where('employment_status', 'active')->count(),
                'departments_count' => Department::active()->count(),
                'today_present' => Attendance::whereDate('date', Carbon::today())
                    ->where('status', 'present')
                    ->count(),
                'monthly_attendance_rate' => $this->calculateMonthlyAttendanceRate(),
                'pending_leave_requests' => LeaveRequest::pending()->count(),
                'monthly_overtime_hours' => $this->calculateMonthlyOvertimeHours(),
            ];

            // Department-wise attendance
            $departmentStats = Department::active()
                ->withCount(['employees' => function ($query) {
                    $query->where('employment_status', 'active');
                }])
                ->get()
                ->map(function ($dept) {
                    $presentToday = Attendance::whereDate('date', Carbon::today())
                        ->whereHas('user', function ($q) use ($dept) {
                            $q->where('department_id', $dept->id);
                        })
                        ->where('status', 'present')
                        ->count();

                    return [
                        'department' => $dept->name,
                        'total_employees' => $dept->employees_count,
                        'present_today' => $presentToday,
                        'attendance_rate' => $dept->employees_count > 0
                            ? round(($presentToday / $dept->employees_count) * 100, 2)
                            : 0,
                    ];
                });

            $stats['department_stats'] = $departmentStats;
        } else {
            // Employee stats - personal
            $userAttendances = Attendance::where('user_id', $user->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->get();

            $stats = [
                'monthly_attendance_days' => $userAttendances->where('status', 'present')->count(),
                'monthly_late_days' => $userAttendances->where('status', 'late')->count(),
                'monthly_work_hours' => round($userAttendances->sum('work_duration') / 60, 2),
                'monthly_overtime_hours' => round($userAttendances->sum('overtime_duration') / 60, 2),
                'leave_balance' => $this->calculateLeaveBalance($user->id),
                'pending_leave_requests' => LeaveRequest::where('user_id', $user->id)
                    ->pending()
                    ->count(),
            ];
        }

        return response()->json($stats);
    }

    private function calculateMonthlyAttendanceRate()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $workingDays = Carbon::now()->startOfMonth()->diffInWeekdays(Carbon::now());

        $totalEmployees = User::where('employment_status', 'active')->count();
        $totalPossibleAttendances = $totalEmployees * $workingDays;

        $actualAttendances = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('status', 'present')
            ->count();

        return $totalPossibleAttendances > 0
            ? round(($actualAttendances / $totalPossibleAttendances) * 100, 2)
            : 0;
    }

    private function calculateMonthlyOvertimeHours()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalOvertimeMinutes = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('overtime_duration');

        return round($totalOvertimeMinutes / 60, 2);
    }

    private function calculateLeaveBalance($userId)
    {
        // Simplified leave balance calculation
        // In a real application, this would be more complex based on leave policies
        $currentYear = Carbon::now()->year;

        $usedLeaves = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $annualLeaveEntitlement = 12; // Default annual leave days

        return max(0, $annualLeaveEntitlement - $usedLeaves);
    }
}
