<?php

namespace App\Http\Controllers;

use App\Models\ShiftChangeRequest;
use App\Services\DatabaseHealthService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ShiftChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        try {
            $requests = auth()->user()->shiftChangeRequests()
                ->with('reviewer')
                ->orderBy('requested_at', 'desc')
                ->paginate(10);

            $monthlyCount = $this->getMonthlyRequestCount();

            return Inertia::render('user/ShiftChangeRequest/Index', [
                'requests' => $requests,
                'monthlyCount' => $monthlyCount,
                'monthlyLimit' => 5,
            ]);
        } catch (QueryException $e) {
            Log::error('Database error in ShiftChangeRequestController@index', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'db_health' => DatabaseHealthService::getConnectionInfo(),
            ]);

            // Attempt to reconnect and retry
            if (DatabaseHealthService::reconnect()) {
                try {
                    $requests = auth()->user()->shiftChangeRequests()
                        ->with('reviewer')
                        ->orderBy('requested_at', 'desc')
                        ->paginate(10);

                    $monthlyCount = $this->getMonthlyRequestCount();

                    Log::info('Database reconnection successful in ShiftChangeRequestController@index', [
                        'user_id' => auth()->id(),
                    ]);

                    return Inertia::render('user/ShiftChangeRequest/Index', [
                        'requests' => $requests,
                        'monthlyCount' => $monthlyCount,
                        'monthlyLimit' => 5,
                    ]);
                } catch (QueryException $retryError) {
                    Log::error('Database retry failed after reconnection in ShiftChangeRequestController@index', [
                        'user_id' => auth()->id(),
                        'retry_error' => $retryError->getMessage(),
                    ]);
                }
            }

            // Return graceful fallback
            return Inertia::render('user/ShiftChangeRequest/Index', [
                'requests' => collect([]),
                'monthlyCount' => 0,
                'monthlyLimit' => 5,
                'error' => 'Terjadi masalah koneksi database. Silakan refresh halaman atau coba lagi nanti.',
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('user/ShiftChangeRequest/Create', [
            'monthlyCount' => $this->getMonthlyRequestCount(),
            'monthlyLimit' => 5,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Check monthly limit
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $monthlyCount = auth()->user()->shiftChangeRequests()
            ->forMonth($currentYear, $currentMonth)
            ->count();

        if ($monthlyCount >= 5) {
            return back()->withErrors([
                'limit' => 'Anda telah mencapai batas maksimal 5 request per bulan.',
            ]);
        }

        $validated = $request->validate([
            'original_date' => 'required|date|after_or_equal:today',
            'requested_date' => 'required|date|after_or_equal:today|different:original_date',
            'reason' => 'nullable|string|max:500',
        ]);

        // Check for conflicting requests
        $conflict = auth()->user()->shiftChangeRequests()
            ->where('status', ShiftChangeRequest::STATUS_PENDING)
            ->where(function ($query) use ($validated) {
                $query->where('original_date', $validated['original_date'])
                    ->orWhere('requested_date', $validated['requested_date']);
            })
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'conflict' => 'Sudah ada request pending untuk tanggal tersebut.',
            ]);
        }

        ShiftChangeRequest::create([
            'user_id' => auth()->id(),
            'original_date' => $validated['original_date'],
            'requested_date' => $validated['requested_date'],
            'reason' => $validated['reason'],
            'requested_at' => now(),
        ]);

        return redirect()->route('shift-change-requests.index')
            ->with('success', 'Request tukar libur berhasil diajukan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShiftChangeRequest $shiftChangeRequest): Response
    {
        // Ensure user can only view their own requests
        if ($shiftChangeRequest->user_id !== auth()->id()) {
            abort(403);
        }

        $shiftChangeRequest->load('reviewer');

        return Inertia::render('user/ShiftChangeRequest/Show', [
            'request' => $shiftChangeRequest,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShiftChangeRequest $shiftChangeRequest)
    {
        // Ensure user can only delete their own pending requests
        if ($shiftChangeRequest->user_id !== auth()->id() || ! $shiftChangeRequest->isPending()) {
            abort(403);
        }

        $shiftChangeRequest->delete();

        return redirect()->route('shift-change-requests.index')
            ->with('success', 'Request berhasil dibatalkan.');
    }

    /**
     * Get monthly request count for current user
     */
    private function getMonthlyRequestCount(): int
    {
        try {
            return auth()->user()->shiftChangeRequests()
                ->forMonth(now()->year, now()->month)
                ->count();
        } catch (QueryException $e) {
            Log::error('Database error in getMonthlyRequestCount', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
