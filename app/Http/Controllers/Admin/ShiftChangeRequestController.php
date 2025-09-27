<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShiftChangeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = ShiftChangeRequest::with(['user', 'reviewer'])
            ->orderBy('requested_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('requested_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('requested_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('admin/ShiftChangeRequest/Index', [
            'requests' => $requests,
            'filters' => $request->only(['status', 'from_date', 'to_date']),
            'stats' => [
                'pending' => ShiftChangeRequest::pending()->count(),
                'approved' => ShiftChangeRequest::approved()->count(),
                'rejected' => ShiftChangeRequest::rejected()->count(),
                'total' => ShiftChangeRequest::count(),
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShiftChangeRequest $shiftChangeRequest): Response
    {
        $shiftChangeRequest->load(['user.department', 'user.position', 'reviewer']);

        return Inertia::render('admin/ShiftChangeRequest/Show', [
            'request' => $shiftChangeRequest,
        ]);
    }

    /**
     * Approve the shift change request.
     */
    public function approve(Request $request, ShiftChangeRequest $shiftChangeRequest)
    {
        if (!$shiftChangeRequest->isPending()) {
            return back()->withErrors(['error' => 'Request ini sudah diproses sebelumnya.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $shiftChangeRequest->approve(auth()->user(), $validated['admin_notes'] ?? null);

        return redirect()->route('admin.shift-change-requests.index')
            ->with('success', 'Request tukar libur berhasil disetujui.');
    }

    /**
     * Reject the shift change request.
     */
    public function reject(Request $request, ShiftChangeRequest $shiftChangeRequest)
    {
        if (!$shiftChangeRequest->isPending()) {
            return back()->withErrors(['error' => 'Request ini sudah diproses sebelumnya.']);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $shiftChangeRequest->reject(auth()->user(), $validated['admin_notes'] ?? null);

        return redirect()->route('admin.shift-change-requests.index')
            ->with('success', 'Request tukar libur berhasil ditolak.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftChangeRequest $shiftChangeRequest)
    {
        $shiftChangeRequest->delete();

        return redirect()->route('admin.shift-change-requests.index')
            ->with('success', 'Request berhasil dihapus.');
    }
}
