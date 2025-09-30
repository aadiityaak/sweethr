<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
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

        // Get filter options
        $leaveTypes = LeaveType::select('id', 'name')->orderBy('name')->get();
        $departments = User::with('department:id,name')
            ->whereHas('department')
            ->get()
            ->pluck('department')
            ->filter() // Remove null values
            ->unique('id')
            ->values();

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'stats' => $stats,
            'monthlyTrend' => $monthlyTrend,
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
            'approved_by' => auth()->id(),
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
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return back()->with('success', 'Leave request rejected successfully.');
    }
}