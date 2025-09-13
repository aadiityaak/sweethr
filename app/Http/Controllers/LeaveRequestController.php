<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $isAdmin = $user->is_admin ?? false;

        $query = LeaveRequest::with(['user:id,name,employee_id', 'leaveType:id,name', 'approvedBy:id,name'])
            ->orderByDesc('created_at');

        // If not admin, only show user's own requests
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        // Apply filters
        if ($request->search && $isAdmin) {
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

        $leaveRequests = $query->paginate(15);

        // Get stats
        if ($isAdmin) {
            $stats = [
                'pending_count' => LeaveRequest::where('status', 'pending')->count(),
                'approved_count' => LeaveRequest::where('status', 'approved')->count(),
                'rejected_count' => LeaveRequest::where('status', 'rejected')->count(),
            ];
        } else {
            $stats = [
                'pending_count' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
                'approved_count' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->count(),
                'rejected_count' => LeaveRequest::where('user_id', $user->id)->where('status', 'rejected')->count(),
                'leave_balance' => 12, // You might want to calculate this from user's data
            ];
        }

        // Get leave types for filter
        $leaveTypes = LeaveType::orderBy('name')->get(['id', 'name']);

        // Choose the right view based on user role
        $view = $isAdmin ? 'admin/LeaveRequests/Index' : 'user/LeaveRequests/Index';

        return Inertia::render($view, [
            'leaveRequests' => $leaveRequests,
            'leaveTypes' => $leaveTypes,
            'filters' => $request->only(['search', 'status', 'leave_type']),
            'stats' => $stats,
        ]);
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Leave request has already been reviewed.');
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Leave request approved successfully.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return back()->with('error', 'Leave request has already been reviewed.');
        }

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $validated['notes'],
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
