<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfficeLocationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = OfficeLocation::query();

        // Apply filters
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('address', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->status) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $officeLocations = $query->orderBy('name')->paginate(15);

        // Get stats
        $stats = [
            'total_locations' => OfficeLocation::count(),
            'active_locations' => OfficeLocation::where('is_active', true)->count(),
            'total_radius_coverage' => OfficeLocation::sum('radius_meters'),
            'today_checkins' => Attendance::whereDate('created_at', Carbon::today())->count(),
        ];

        return Inertia::render('admin/OfficeLocations/Index', [
            'officeLocations' => $officeLocations,
            'filters' => $request->only(['search', 'status']),
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/OfficeLocations/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        OfficeLocation::create($validated);

        return redirect()->route('office-locations.index')
            ->with('success', 'Office location created successfully.');
    }

    public function show(OfficeLocation $officeLocation): Response
    {
        $officeLocation->loadCount([
            'attendances',
            'attendances as today_checkins_count' => function ($query) {
                $query->whereDate('created_at', Carbon::today());
            }
        ]);

        return Inertia::render('admin/OfficeLocations/Show', [
            'officeLocation' => $officeLocation,
        ]);
    }

    public function edit(OfficeLocation $officeLocation): Response
    {
        // Refresh the model to ensure we get the latest data from database
        $officeLocation->refresh();

        return Inertia::render('admin/OfficeLocations/Edit', [
            'officeLocation' => $officeLocation,
        ]);
    }

    public function update(Request $request, OfficeLocation $officeLocation)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $officeLocation->update($validated);

        return redirect()->route('office-locations.index')
            ->with('success', 'Office location updated successfully.');
    }

    public function destroy(OfficeLocation $officeLocation)
    {
        // Check if location has attendances
        if ($officeLocation->attendances()->exists()) {
            return back()->with('error', 'Cannot delete office location with attendance records.');
        }

        $officeLocation->delete();

        return redirect()->route('office-locations.index')
            ->with('success', 'Office location deleted successfully.');
    }
}
