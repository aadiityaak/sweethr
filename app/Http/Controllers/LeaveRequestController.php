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
        $query = LeaveRequest::with(['user:id,name,employee_id', 'leaveType:id,name', 'reviewer:id,name'])
            ->orderByDesc('applied_at');

        // Apply filters
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%');
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
        $stats = [
            'pending_count' => LeaveRequest::where('status', 'pending')->count(),
            'approved_count' => LeaveRequest::where('status', 'approved')->count(),
            'rejected_count' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        // Get leave types for filter
        $leaveTypes = LeaveType::orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/LeaveRequests/Index', [
            'leaveRequests' => $leaveRequests,
            'leaveTypes' => $leaveTypes,
            'currentUser' => auth()->user()->load('department', 'position'),
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
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
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
            'reviewer_id' => auth()->id(),
            'reviewed_at' => now(),
            'reviewer_notes' => $validated['notes'],
        ]);

        return back()->with('success', 'Leave request rejected.');
    }
}
