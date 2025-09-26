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
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceRecognitionService
    ) {}
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
        // Get fresh user data from database to avoid cache issues
        $userId = auth()->id();
        $user = \App\Models\User::find($userId);

        // Force refresh user data from database to ensure fresh face recognition data
        $user->refresh();

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
                                    ->where('date', Carbon::today())
                                    ->with('officeLocation')
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

        // Get face recognition data - ensure fresh data from database
        $faceRecognitionEnabled = $user->face_recognition_enabled ?? false;
        $faceDescriptors = null;

        // Debug: Log fresh face recognition data
        \Log::info('DashboardController welcome() - Face recognition data', [
            'user_id' => $user->id,
            'face_recognition_enabled' => $faceRecognitionEnabled,
            'face_setup_at' => $user->face_setup_at,
            'has_face_descriptors' => !empty($user->face_descriptors),
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

        \Log::info('DashboardController welcome() - Final data being sent to frontend', [
            'user_id' => $user->id,
            'face_recognition_enabled' => $userData['face_recognition_enabled'],
            'face_recognition_mandatory' => $userData['face_recognition_mandatory'],
            'face_setup_at' => $userData['face_setup_at'],
            'has_face_descriptors' => !empty($user->face_descriptors),
            'faceRecognitionEnabled_prop' => $faceRecognitionEnabled,
            'faceDescriptors_available' => !empty($faceDescriptors),
        ]);

        return Inertia::render('user/Welcome', [
            'user' => $userData,
            'todayAttendance' => $todayAttendance,
            'officeLocations' => $officeLocations,
            'stats' => $stats,
            'faceRecognitionEnabled' => $faceRecognitionEnabled,
            'faceDescriptors' => $faceDescriptors,
            'announcements' => $announcements,
        ]);
    }

    private function getStats(User $user): array
    {
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

        return $stats;
    }

    private function calculateMonthlyAttendanceRate(): float
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