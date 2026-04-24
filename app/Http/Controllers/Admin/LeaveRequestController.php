<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        // Prevent caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $query = LeaveRequest::with(['user:id,name,employee_id', 'leaveType:id,name', 'approvedBy:id,name'])
            ->orderByDesc('created_at');

        // Apply filters
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('employee_id', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->leave_type) {
            $query->where('leave_type_id', $request->leave_type);
        }

        if ($request->department) {
            $query->whereHas('user.department', function ($q) use ($request) {
                $q->where('id', $request->department);
            });
        }

        if ($request->date_from) {
            $query->where('start_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $leaveRequests = $query->paginate(15)->withQueryString();

        // Get comprehensive stats
        $totalRequests = LeaveRequest::count();
        $stats = [
            'total_requests' => $totalRequests,
            'pending_count' => LeaveRequest::where('status', 'pending')->count(),
            'approved_count' => LeaveRequest::where('status', 'approved')->count(),
            'rejected_count' => LeaveRequest::where('status', 'rejected')->count(),
            'this_month_count' => LeaveRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'pending_percentage' => $totalRequests > 0 ?
                round((LeaveRequest::where('status', 'pending')->count() / $totalRequests) * 100, 1) : 0,
        ];

        // Get monthly trend data for chart (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'count' => LeaveRequest::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
                'approved' => LeaveRequest::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->where('status', 'approved')
                    ->count(),
                'pending' => LeaveRequest::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->where('status', 'pending')
                    ->count(),
                'rejected' => LeaveRequest::whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->where('status', 'rejected')
                    ->count(),
            ];
        }

        // Get 30-day trend data for chart
        $dailyTrend = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dailyTrend[] = [
                'date' => $date->format('d/m'),
                'count' => LeaveRequest::whereDate('created_at', $date->format('Y-m-d'))->count(),
                'approved' => LeaveRequest::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'approved')
                    ->count(),
                'pending' => LeaveRequest::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'pending')
                    ->count(),
                'rejected' => LeaveRequest::whereDate('created_at', $date->format('Y-m-d'))
                    ->where('status', 'rejected')
                    ->count(),
            ];
        }

        // Get department-wise leave request data
        $departmentStats = Department::active()
            ->with(['employees' => function ($query) {
                $query->where('employment_status', 'active');
            }])
            ->get()
            ->map(function ($dept) {
                $userIds = $dept->employees->pluck('id');

                $total = LeaveRequest::whereIn('user_id', $userIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count();

                $approved = LeaveRequest::whereIn('user_id', $userIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'approved')
                    ->count();

                $pending = LeaveRequest::whereIn('user_id', $userIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'pending')
                    ->count();

                $rejected = LeaveRequest::whereIn('user_id', $userIds)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'rejected')
                    ->count();

                return [
                    'department' => $dept->name,
                    'total' => $total,
                    'approved' => $approved,
                    'pending' => $pending,
                    'rejected' => $rejected,
                ];
            });

        // Get leave type distribution
        $leaveTypeStats = LeaveType::withCount(['leaveRequests' => function ($query) {
            $query->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year);
        }])
            ->withCount(['leaveRequests as approved_count' => function ($query) {
                $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('status', 'approved');
            }])
            ->get()
            ->map(function ($type) {
                return [
                    'type' => $type->name,
                    'total' => $type->leave_requests_count,
                    'approved' => $type->approved_count,
                ];
            });

        // Get filter options
        $leaveTypes = LeaveType::select('id', 'name')->orderBy('name')->get();
        $departments = Department::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'stats' => $stats,
            'monthlyTrend' => $monthlyTrend,
            'dailyTrend' => $dailyTrend,
            'departmentStats' => $departmentStats,
            'leaveTypeStats' => $leaveTypeStats,
            'leaveTypes' => $leaveTypes,
            'departments' => $departments,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'leave_type' => $request->leave_type,
                'department' => $request->department,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Leave request approved successfully.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Leave request rejected successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $leaveRequest->delete();

        return back()->with('success', 'Leave request deleted successfully.');
    }
}
