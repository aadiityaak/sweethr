<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();

        $attendances = Attendance::where('user_id', $user->id)
                                ->with(['officeLocation:id,name'])
                                ->orderByDesc('date')
                                ->when($request->month, function ($query, $month) {
                                    $query->whereMonth('date', $month);
                                })
                                ->when($request->year, function ($query, $year) {
                                    $query->whereYear('date', $year);
                                })
                                ->paginate(20);

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
                                    ->where('date', Carbon::today())
                                    ->with('officeLocation')
                                    ->first();

        return Inertia::render('user/Attendance/Index', [
            'attendances' => $attendances,
            'todayAttendance' => $todayAttendance,
            'filters' => $request->only(['month', 'year']),
        ]);
    }

    public function checkIn(): Response
    {
        $user = auth()->user();
        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
                                       ->where('date', $today)
                                       ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return redirect()->route('attendance.index')
                           ->with('error', 'You have already checked in today.');
        }

        // Get all office locations
        $officeLocations = OfficeLocation::active()->get();

        return Inertia::render('user/Attendance/CheckIn', [
            'officeLocations' => $officeLocations,
            'existingAttendance' => $existingAttendance,
        ]);
    }

    public function storeCheckIn(Request $request): RedirectResponse
    {
        $request->validate([
            'office_location_id' => 'required|exists:office_locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = auth()->user();
        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
                                       ->where('date', $today)
                                       ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return back()->with('error', 'Already checked in today');
        }

        // Validate location
        $office = OfficeLocation::findOrFail($request->office_location_id);
        if (!$office->isWithinRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'You are not within the office radius. Please get closer to the office location.');
        }

        // Determine if late
        $checkInTime = now();
        $shift = $user->employeeShifts()->current()->with('workShift')->first();
        $status = 'present';

        if ($shift && $shift->workShift) {
            $shiftStartTime = Carbon::parse($shift->workShift->start_time);
            if ($checkInTime->format('H:i:s') > $shiftStartTime->format('H:i:s')) {
                $status = 'late';
            }
        }

        Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'office_location_id' => $request->office_location_id,
                'check_in_time' => $checkInTime->format('H:i:s'),
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'status' => $status,
            ]
        );

        return redirect()->route('attendance.index')
                        ->with('success', 'Successfully checked in!');
    }

    public function storeCheckOut(Request $request): RedirectResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = auth()->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance || !$attendance->check_in_time) {
            return back()->with('error', 'You must check in first');
        }

        if ($attendance->check_out_time) {
            return back()->with('error', 'You have already checked out today');
        }

        // Validate location
        $office = $attendance->officeLocation;
        if (!$office->isWithinRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'You are not within the office radius');
        }

        // Calculate work duration
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $checkOutTime = now();
        $workDuration = $checkOutTime->diffInMinutes($checkInTime);

        // Calculate overtime (simplified logic)
        $overtimeDuration = 0;
        $shift = $user->employeeShifts()->current()->with('workShift')->first();
        if ($shift && $shift->workShift && $workDuration > $shift->workShift->work_hours) {
            $overtimeDuration = $workDuration - $shift->workShift->work_hours;
        }

        $attendance->update([
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'work_duration' => $workDuration,
            'overtime_duration' => $overtimeDuration,
        ]);

        return redirect()->route('attendance.index')
                        ->with('success', 'Successfully checked out!');
    }
}