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

    public function create(): Response
    {
        $leaveTypes = LeaveType::orderBy('name')->get(['id', 'name']);

        return Inertia::render('user/LeaveRequests/Create', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|image|max:5120', // 5MB max
        ]);

        $leaveRequestData = [
            'user_id' => auth()->id(),
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'pending',
        ];

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $originalName = $file->getClientOriginalName();
            $path = $file->store('leave-attachments', 'public');

            $leaveRequestData['attachment_path'] = $path;
            $leaveRequestData['attachment_original_name'] = $originalName;
        }

        LeaveRequest::create($leaveRequestData);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    public function show(LeaveRequest $leaveRequest): Response
    {
        $user = auth()->user();
        $isAdmin = $user->is_admin ?? false;

        // Non-admin users can only view their own requests
        if (! $isAdmin && $leaveRequest->user_id !== $user->id) {
            abort(403);
        }

        $leaveRequest->load(['user:id,name,employee_id', 'leaveType:id,name', 'approvedBy:id,name']);

        return Inertia::render('user/LeaveRequests/Show', [
            'leaveRequest' => $leaveRequest,
        ]);
    }

    public function edit(LeaveRequest $leaveRequest): Response
    {
        $user = auth()->user();

        // Only allow editing own requests and only if pending
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403);
        }

        $leaveTypes = LeaveType::orderBy('name')->get(['id', 'name']);

        return Inertia::render('user/LeaveRequests/Edit', [
            'leaveRequest' => $leaveRequest,
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // Only allow updating own requests and only if pending
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403);
        }

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);

        $leaveRequest->update($validated);

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request updated successfully.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // Only allow deleting own requests and only if pending
        if ($leaveRequest->user_id !== $user->id || $leaveRequest->status !== 'pending') {
            abort(403);
        }

        $leaveRequest->delete();

        return back()->with('success', 'Leave request deleted successfully.');
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
