<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceRecognitionService
    ) {}
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

    public function storeCheckIn(Request $request)
    {
        try {
            $validationRules = [
                'office_location_id' => 'required|exists:office_locations,id',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ];

            // Add face recognition validation if required
            $user = auth()->user();
            if ($this->faceRecognitionService->isFaceRecognitionRequired($user)) {
                $validationRules['face_confidence'] = 'required|numeric|min:0|max:100';
            }

            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return back()->withErrors(['message' => 'Anda sudah melakukan check in hari ini']);
        }

        // Validate location
        $office = OfficeLocation::findOrFail($request->office_location_id);
        if (! $office->isWithinRadius($request->latitude, $request->longitude)) {
            return back()->withErrors(['message' => 'Anda berada di luar radius kantor. Mohon mendekat ke lokasi kantor.']);
        }

        // Face recognition verification
        $faceVerificationPassed = false;
        $faceVerificationSkipped = false;
        $faceMatchConfidence = null;
        $faceVerificationNotes = null;

        if ($this->faceRecognitionService->isFaceRecognitionRequired($user)) {
            if ($request->has('face_confidence')) {
                $faceConfidence = $request->input('face_confidence');
                $faceResult = $this->faceRecognitionService->verifyFaceMatch($user, $faceConfidence);

                $faceMatchConfidence = $faceConfidence;
                $faceVerificationPassed = $faceResult['success'];

                if (!$faceResult['success'] && $faceResult['mandatory']) {
                    return back()->withErrors(['message' => $faceResult['error']]);
                }

                if (!$faceResult['success'] && !$faceResult['mandatory']) {
                    $faceVerificationSkipped = true;
                    $faceVerificationNotes = 'Face verification skipped after max attempts';
                }
            } else {
                return back()->withErrors(['message' => 'Verifikasi wajah diperlukan untuk check in.']);
            }
        } else {
            $faceVerificationSkipped = true;
            $faceVerificationNotes = 'Face recognition not required for this user';
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

        $attendance = Attendance::updateOrCreate(
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
                'face_match_confidence' => $faceMatchConfidence,
                'face_verification_passed' => $faceVerificationPassed,
                'face_verification_skipped' => $faceVerificationSkipped,
                'face_verification_notes' => $faceVerificationNotes,
            ]
        );

        $message = 'Check in berhasil!';
        if ($faceVerificationSkipped && $faceMatchConfidence) {
            $message .= ' (Verifikasi wajah dilewati)';
        } elseif ($faceVerificationPassed) {
            $message .= ' (Wajah terverifikasi)';
        }

        return back()->with('success', $message);
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

        if (! $attendance || ! $attendance->check_in_time) {
            return back()->withErrors(['message' => 'Anda harus check in terlebih dahulu']);
        }

        if ($attendance->check_out_time) {
            return back()->withErrors(['message' => 'Anda sudah melakukan check out hari ini']);
        }

        // Note: We don't validate location for checkout as strictly as check-in
        // Users might be allowed to checkout from different locations
        // Location is still recorded for audit purposes

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

        return back()->with('success', 'Check out berhasil!');
    }
}
