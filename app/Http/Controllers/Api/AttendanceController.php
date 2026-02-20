<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with(['user:id,name,employee_id', 'officeLocation:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('check_in_time');

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->date) {
            $query->whereDate('date', $request->date);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        }

        $attendances = $query->paginate(20);

        return response()->json($attendances);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'office_location_id' => 'required|exists:office_locations,id',
            'date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::create($request->all());

        return response()->json($attendance->load(['user', 'officeLocation']), 201);
    }

    public function show(Attendance $attendance)
    {
        return response()->json($attendance->load(['user', 'officeLocation']));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validator = Validator::make($request->all(), [
            'check_out_time' => 'nullable|date_format:H:i:s',
            'work_duration' => 'nullable|integer|min:0',
            'overtime_duration' => 'nullable|integer|min:0',
            'status' => 'nullable|in:present,late,absent,early_leave',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance->update($request->all());

        return response()->json($attendance->load(['user', 'officeLocation']));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json(null, 204);
    }

    public function checkIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'office_location_id' => 'required|exists:office_locations,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return response()->json(['message' => 'Already checked in today'], 400);
        }

        // Validate location
        $office = OfficeLocation::findOrFail($request->office_location_id);
        if (! $office->isWithinRadius($request->latitude, $request->longitude)) {
            return response()->json(['message' => 'Not within office radius'], 400);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'office_location_id' => $request->office_location_id,
                'check_in_time' => now()->format('H:i:s'),
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'status' => 'present',
            ]
        );

        return response()->json([
            'message' => 'Successfully checked in',
            'attendance' => $attendance->load(['user', 'officeLocation']),
        ]);
    }

    public function checkOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in_time) {
            return response()->json(['message' => 'Must check in first'], 400);
        }

        if ($attendance->check_out_time) {
            return response()->json(['message' => 'Already checked out'], 400);
        }

        // Validate location
        $office = $attendance->officeLocation;
        if (! $office->isWithinRadius($request->latitude, $request->longitude)) {
            return response()->json(['message' => 'Not within office radius'], 400);
        }

        // Calculate work duration
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $checkOutTime = now();
        $workDuration = $checkOutTime->diffInMinutes($checkInTime);

        $attendance->update([
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
            'work_duration' => $workDuration,
        ]);

        return response()->json([
            'message' => 'Successfully checked out',
            'attendance' => $attendance->load(['user', 'officeLocation']),
        ]);
    }

    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->with(['officeLocation'])
            ->first();

        return response()->json($attendance);
    }

    public function report(Request $request)
    {
        $query = Attendance::with(['user:id,name,employee_id'])
            ->selectRaw('
                              user_id,
                              COUNT(*) as total_days,
                              SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_days,
                              SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days,
                              SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days,
                              AVG(work_duration) as avg_work_duration,
                              SUM(overtime_duration) as total_overtime
                          ')
            ->groupBy('user_id');

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('date', [$request->date_from, $request->date_to]);
        } else {
            // Default to current month
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }

        $reports = $query->get();

        return response()->json($reports);
    }
}
