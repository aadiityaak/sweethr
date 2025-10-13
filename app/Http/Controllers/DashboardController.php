<?php

namespace App\Http\Controllers;

use App\Helpers\UrlHelper;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\OfficeLocation;
use App\Models\User;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceRecognitionService
    ) {
        // Constructor
    }

    public function index(): Response
    {
        $user = auth()->user();

        // Admin middleware ensures only admin users can access this method

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->with('officeLocation')
            ->first();

        // Get pending leave requests (if manager/admin)
        $pendingLeaves = [];
        if ($user->is_admin || $user->subordinates()->count() > 0) {
            $pendingLeaves = LeaveRequest::pending()
                ->with(['user:id,name,employee_id', 'leaveType:id,name'])
                ->limit(5)
                ->get();
        }

        // Calculate stats
        $stats = $this->getStats($user);

        // Get recent announcements
        $announcements = Announcement::visible()
            ->with(['category', 'author'])
            ->byPriority()
            ->latest('published_at')
            ->limit(3)
            ->get();

        // Prepare user data with avatar URL
        $userData = $user->load(['department', 'position'])->toArray();
        $userData['avatar'] = UrlHelper::avatar($user->avatar);

        return Inertia::render('admin/Dashboard', [
            'user' => $userData,
            'todayAttendance' => $todayAttendance,
            'pendingLeaves' => $pendingLeaves,
            'stats' => $stats,
            'announcements' => $announcements,
        ]);
    }

    public function welcome(): Response
    {
        // Prevent browser and proxy caching of this response
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Get fresh user data from database to avoid cache issues
        $user = auth()->user();

        // Force refresh user data from database to ensure fresh face recognition data
        $user->refresh();

        // Get today's attendance with work shift
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->with(['officeLocation', 'workShift'])
            ->first();

        // Get user's attendance this month
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $userAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $stats = [
            'monthly_attendance_days' => $userAttendances->where('status', 'present')->count(),
            'monthly_late_days' => $userAttendances->where('status', 'late')->count(),
            'monthly_work_hours' => round($userAttendances->sum('work_duration') / 60, 2),
            'leave_balance' => $this->calculateLeaveBalance($user->id),
        ];

        // Get all office locations for check-in functionality
        $officeLocations = OfficeLocation::active()->get();

        // Get all active work shifts for employees to choose from
        $allWorkShifts = \App\Models\WorkShift::active()
            ->get()
            ->map(function ($workShift) {
                return [
                    'id' => $workShift->id,
                    'name' => $workShift->name,
                    'code' => $workShift->code,
                    'start_time' => $workShift->start_time->format('H:i'),
                    'end_time' => $workShift->end_time->format('H:i'),
                    'work_hours' => $workShift->work_hours,
                    'break_duration' => $workShift->break_duration,
                    'workdays' => $workShift->workdays,
                    'is_night_shift' => $workShift->is_night_shift,
                    'assignment_type' => 'general', // Default assignment type for all shifts
                    'effective_date' => Carbon::today()->toDateString(),
                    'end_date' => null,
                ];
            });

        // Use all work shifts for user to choose from
        $userShifts = $allWorkShifts;

        // Get face recognition data - ensure fresh data from database
        $faceRecognitionEnabled = $user->face_recognition_enabled ?? false;
        $faceDescriptors = null;

        // Debug: Log fresh face recognition data
        Log::info('DashboardController welcome() - Face recognition data', [
            'user_id' => $user->id,
            'face_recognition_enabled' => $faceRecognitionEnabled,
            'face_setup_at' => $user->face_setup_at,
            'has_face_descriptors' => ! empty($user->face_descriptors),
        ]);

        if ($faceRecognitionEnabled) {
            $faceDescriptors = $this->faceRecognitionService->getFaceDescriptors($user);
        }

        // Get announcements for carousel
        $announcements = Announcement::visible()
            ->with(['category', 'author'])
            ->byPriority()
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Prepare user data for frontend with explicit values
        $userData = $user->load(['department', 'position'])->makeVisible(['face_recognition_enabled', 'face_recognition_mandatory', 'face_setup_at'])->toArray();
        $userData['face_recognition_enabled'] = (bool) $user->face_recognition_enabled;
        $userData['face_recognition_mandatory'] = (bool) $user->face_recognition_mandatory;
        $userData['face_setup_at'] = $user->face_setup_at?->toISOString();
        $userData['avatar'] = UrlHelper::avatar($user->avatar);
        $userData['_cache_buster'] = now()->timestamp;

        Log::info('DashboardController welcome() - Final data being sent to frontend', [
            'user_id' => $user->id,
            'face_recognition_enabled' => $userData['face_recognition_enabled'],
            'face_recognition_mandatory' => $userData['face_recognition_mandatory'],
            'face_setup_at' => $userData['face_setup_at'],
            'has_face_descriptors' => ! empty($user->face_descriptors),
            'faceRecognitionEnabled_prop' => $faceRecognitionEnabled,
            'faceDescriptors_available' => ! empty($faceDescriptors),
        ]);

        return Inertia::render('user/Welcome', [
            'user' => $userData,
            'todayAttendance' => $todayAttendance,
            'officeLocations' => $officeLocations,
            'stats' => $stats,
            'faceRecognitionEnabled' => $faceRecognitionEnabled,
            'faceDescriptors' => $faceDescriptors,
            'announcements' => $announcements,
            'allowOutsideRadius' => $user->allow_outside_radius ?? false,
            'userShifts' => $userShifts,
        ]);
    }

    private function getStats(User $user): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        if ($user->is_admin) {
            // Admin stats - company-wide
            $stats = [
                'total_employees' => User::where('employment_status', 'active')->count(),
                'departments_count' => Department::active()->count(),
                'today_present' => Attendance::whereDate('date', Carbon::today())
                    ->whereIn('status', ['present', 'late'])
                    ->count(),
                'monthly_attendance_rate' => $this->calculateMonthlyAttendanceRate(),
                'pending_leave_requests' => LeaveRequest::pending()->count(),
                'monthly_overtime_hours' => $this->calculateMonthlyOvertimeHours(),
            ];

            // Late employees today with details
            $lateToday = Attendance::whereDate('date', Carbon::today())
                ->where('status', 'late')
                ->with(['user:id,name,employee_id,department_id', 'user.department:id,name'])
                ->get()
                ->map(function ($attendance) {
                    return [
                        'id' => $attendance->user->id,
                        'name' => $attendance->user->name,
                        'employee_id' => $attendance->user->employee_id,
                        'department' => $attendance->user->department->name ?? 'N/A',
                        'check_in_time' => $attendance->check_in_time,
                        'late_duration' => $attendance->late_duration,
                    ];
                });

            $stats['late_today'] = $lateToday;
            $stats['late_today_count'] = $lateToday->count();

            // Employees on leave today
            $onLeaveToday = LeaveRequest::where('status', 'approved')
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today())
                ->with(['user:id,name,employee_id', 'leaveType:id,name'])
                ->get()
                ->map(function ($leave) {
                    return [
                        'user_name' => $leave->user->name,
                        'employee_id' => $leave->user->employee_id,
                        'leave_type' => $leave->leaveType->name,
                    ];
                });

            $stats['on_leave_today'] = $onLeaveToday;
            $stats['on_leave_today_count'] = $onLeaveToday->count();

            // Trend comparison: This month vs Last month
            $currentMonthAttendanceRate = $this->calculateMonthlyAttendanceRate();
            $lastMonthAttendanceRate = $this->calculateMonthlyAttendanceRate($lastMonth, $lastMonthYear);

            $currentMonthLateCount = Attendance::whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'late')
                ->count();

            $lastMonthLateCount = Attendance::whereMonth('date', $lastMonth)
                ->whereYear('date', $lastMonthYear)
                ->where('status', 'late')
                ->count();

            $stats['trend_comparison'] = [
                'attendance_rate' => [
                    'current' => $currentMonthAttendanceRate,
                    'last' => $lastMonthAttendanceRate,
                    'change' => $currentMonthAttendanceRate - $lastMonthAttendanceRate,
                ],
                'late_count' => [
                    'current' => $currentMonthLateCount,
                    'last' => $lastMonthLateCount,
                    'change' => $currentMonthLateCount - $lastMonthLateCount,
                ],
            ];

            // Alerts for anomalies
            $alerts = collect([]);

            // Alert: Employees late more than 3 times this week
            $weekStart = Carbon::now()->startOfWeek();
            $frequentLateEmployees = Attendance::whereBetween('date', [$weekStart, Carbon::today()])
                ->where('status', 'late')
                ->with('user:id,name,employee_id')
                ->get()
                ->groupBy('user_id')
                ->filter(fn ($group) => $group->count() >= 3)
                ->map(fn ($group) => [
                    'type' => 'frequent_late',
                    'severity' => 'high',
                    'user_name' => $group->first()->user->name,
                    'employee_id' => $group->first()->user->employee_id,
                    'count' => $group->count(),
                    'message' => "{$group->first()->user->name} terlambat {$group->count()}x minggu ini",
                ])
                ->values();

            $alerts = $alerts->concat($frequentLateEmployees);

            // Alert: Low attendance rate (below 80%)
            if ($currentMonthAttendanceRate < 80) {
                $alerts->push([
                    'type' => 'low_attendance',
                    'severity' => 'medium',
                    'message' => "Tingkat kehadiran bulan ini rendah ({$currentMonthAttendanceRate}%)",
                ]);
            }

            // Alert: High pending leave requests
            $pendingCount = LeaveRequest::pending()->count();
            if ($pendingCount > 10) {
                $alerts->push([
                    'type' => 'pending_leaves',
                    'severity' => 'medium',
                    'message' => "{$pendingCount} pengajuan cuti menunggu persetujuan",
                ]);
            }

            $stats['alerts'] = $alerts;

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
                        ->whereIn('status', ['present', 'late'])
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

        return $stats;
    }

    private function calculateMonthlyAttendanceRate(?int $month = null, ?int $year = null): float
    {
        $month = $month ?? Carbon::now()->month;
        $year = $year ?? Carbon::now()->year;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfPeriod = $month === Carbon::now()->month && $year === Carbon::now()->year
            ? Carbon::now()
            : Carbon::create($year, $month, 1)->endOfMonth();

        $workingDays = $startOfMonth->diffInWeekdays($endOfPeriod);

        $totalEmployees = User::where('employment_status', 'active')->count();
        $totalPossibleAttendances = $totalEmployees * $workingDays;

        $actualAttendances = Attendance::whereMonth('date', $month)
            ->whereYear('date', $year)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return $totalPossibleAttendances > 0
            ? round(($actualAttendances / $totalPossibleAttendances) * 100, 2)
            : 0;
    }

    private function calculateMonthlyOvertimeHours(): float
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $totalOvertimeMinutes = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('overtime_duration');

        return round($totalOvertimeMinutes / 60, 2);
    }

    private function calculateLeaveBalance(int $userId): int
    {
        $currentYear = Carbon::now()->year;

        $usedLeaves = LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereYear('start_date', $currentYear)
            ->sum('total_days');

        $annualLeaveEntitlement = 12; // Default annual leave days

        return max(0, $annualLeaveEntitlement - $usedLeaves);
    }
}
