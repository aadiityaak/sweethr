<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Services\FaceRecognitionService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Check if work_shift_id column exists in attendances table
        if (!\Illuminate\Support\Facades\Schema::hasColumn('attendances', 'work_shift_id')) {
            Log::warning('work_shift_id column does not exist in attendances table');

            // Try to add the column if it doesn't exist
            try {
                \Illuminate\Support\Facades\Schema::table('attendances', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->foreignId('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
                });
                Log::info('work_shift_id column added to attendances table');
            } catch (\Exception $e) {
                Log::error('Failed to add work_shift_id column: ' . $e->getMessage());
            }
        }

        $attendances = Attendance::where('user_id', $user->id)
            ->with(['officeLocation:id,name', 'workShift:id,name,code,start_time,end_time'])
            ->orderByDesc('date')
            ->when($request->show_all, function ($query) {
                // Show all data without date filtering
                return $query;
            })
            ->when(!$request->show_all, function ($query) use ($request) {
                // Apply date filters when not showing all data
                return $query->when($request->date_from, function ($query, $dateFrom) {
                    $query->whereDate('date', '>=', $dateFrom);
                })
                ->when($request->date_to, function ($query, $dateTo) {
                    $query->whereDate('date', '<=', $dateTo);
                })
                ->when(!$request->date_from && !$request->date_to, function ($query) use ($request) {
                    // Fall back to month/year filtering if no date range specified
                    $query->when($request->month, function ($query, $month) {
                        $query->whereMonth('date', $month);
                    })
                    ->when($request->year, function ($query, $year) {
                        $query->whereYear('date', $year);
                    });
                });
            })
            ->paginate(20);

        // Debug logging
        Log::info('Attendance data with shift', [
            'user_id' => $user->id,
            'attendances_count' => $attendances->count(),
            'sample_attendance' => $attendances->first(),
        ]);

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->with(['officeLocation', 'workShift:id,name,code,start_time,end_time'])
            ->first();

        // Debug logging for today's attendance
        Log::info('Today attendance with shift', [
            'user_id' => $user->id,
            'today_attendance' => $todayAttendance,
            'has_work_shift' => $todayAttendance ? isset($todayAttendance->workShift) : null,
            'work_shift' => $todayAttendance ? $todayAttendance->workShift : null,
            'work_shift_id' => $todayAttendance ? $todayAttendance->work_shift_id : null,
        ]);

        // Get user's current assigned shift (fallback for display)
        $currentShift = $user->getCurrentShiftData();

        // Debug logging for current shift
        Log::info('Current assigned shift', [
            'user_id' => $user->id,
            'current_shift' => $currentShift,
        ]);

        return Inertia::render('user/Attendance/Index', [
            'attendances' => $attendances,
            'todayAttendance' => $todayAttendance,
            'currentShift' => $currentShift,
            'filters' => $request->only(['month', 'year', 'date_from', 'date_to', 'show_all']),
        ]);
    }

    public function checkIn(): Response
    {
        $user = auth()->user();
        // Force refresh user data from database to get latest allow_outside_radius value
        $user->refresh();
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
            'user' => [
                'face_recognition_enabled' => $user->face_recognition_enabled ?? false,
                'face_recognition_mandatory' => $user->face_recognition_mandatory ?? true,
                'face_descriptors' => $user->face_descriptors,
                'allow_outside_radius' => $user->allow_outside_radius ?? false,
            ],
        ]);
    }

    public function storeCheckIn(Request $request)
    {
        Log::info('Check-in request received', [
            'user_id' => auth()->id(),
            'has_face_photo' => $request->has('face_photo'),
            'has_face_confidence' => $request->has('face_confidence'),
            'face_photo_length' => $request->has('face_photo') ? strlen($request->face_photo) : 0,
            'all_keys' => array_keys($request->all()),
            'selected_shift_id' => $request->input('selected_shift_id'),
        ]);

        try {
            $user = auth()->user();

            if (!$user) {
                Log::error('Check-in failed: User not authenticated');
                return back()->withErrors(['message' => 'Anda harus login terlebih dahulu']);
            }
            // Force refresh user data from database to get latest settings
            $user->refresh();

            // For users with remote attendance permission, office_location_id is optional
            $validationRules = [
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ];

            // Office location is required only if user doesn't have remote attendance permission
            if (!$user->allow_outside_radius) {
                $validationRules['office_location_id'] = 'required|exists:office_locations,id';
            } else {
                $validationRules['office_location_id'] = 'nullable|exists:office_locations,id';
            }

            // Add face recognition validation if required
            if ($this->faceRecognitionService->isFaceRecognitionRequired($user)) {
                $validationRules['face_confidence'] = 'required|numeric|min:0|max:100';
                $validationRules['face_photo'] = 'required|string'; // Base64 image data
            }

            // Add selected shift validation if provided
            if ($request->has('selected_shift_id')) {
                $validationRules['selected_shift_id'] = 'exists:work_shifts,id';
            }

            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Check-in validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id(),
            ]);
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Check-in validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return back()->withErrors(['message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }

        $today = Carbon::today();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($existingAttendance && $existingAttendance->check_in_time) {
            return back()->withErrors(['message' => 'Anda sudah melakukan check in hari ini']);
        }

        // Validate location - only if office_location_id is provided
        $office = null;
        if ($request->office_location_id) {
            $office = OfficeLocation::findOrFail($request->office_location_id);

            // Check if user is within radius OR has permission to check-in outside radius
            if (! $office->isWithinRadius($request->latitude, $request->longitude)) {
                if (! $user->allow_outside_radius) {
                    return back()->withErrors(['message' => 'Anda berada di luar radius kantor. Mohon mendekat ke lokasi kantor.']);
                }
                // Log when user checks in outside radius but is allowed
                Log::info('User checked in outside radius with permission', [
                    'user_id' => $user->id,
                    'office_id' => $office->id,
                    'distance_from_office' => $office->calculateDistance($request->latitude, $request->longitude),
                    'allowed_radius' => $office->radius_meters,
                ]);
            }
        } else {
            // User with remote attendance permission checking in without office location
            Log::info('User checked in remotely without office location', [
                'user_id' => $user->id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);
        }

        // Face recognition verification - TIDAK WAJIB COCOK, HANYA DETEKSI
        $faceVerificationPassed = false;
        $faceVerificationSkipped = false;
        $faceMatchConfidence = null;
        $faceVerificationNotes = null;
        $facePhotoPath = null;
        $faceConfidenceScore = null;
        $faceDetected = false;

        if ($this->faceRecognitionService->isFaceRecognitionRequired($user)) {
            if ($request->has('face_confidence') || $request->has('face_photo')) {
                // Jika ada confidence score atau photo, berarti wajah terdeteksi
                $faceDetected = true;

                if ($request->has('face_confidence')) {
                    $faceConfidence = $request->input('face_confidence');
                    $faceConfidenceScore = $faceConfidence / 100; // Convert to decimal (0.0 - 1.0)

                    // Untuk compatibility dengan sistem lama
                    $faceMatchConfidence = $faceConfidence;

                    // LOGIKA BARU: Selalu dianggap berhasil jika wajah terdeteksi
                    $faceVerificationPassed = true;

                    // Catat tingkat confidence untuk admin
                    if ($faceConfidenceScore >= 0.8) {
                        $faceVerificationNotes = 'High confidence match';
                    } elseif ($faceConfidenceScore >= 0.6) {
                        $faceVerificationNotes = 'Medium confidence match';
                    } elseif ($faceConfidenceScore >= 0.4) {
                        $faceVerificationNotes = 'Low confidence match';
                    } else {
                        $faceVerificationNotes = 'Very low confidence - face detected but poor match';
                    }
                } else {
                    // Jika hanya photo tanpa confidence, anggap sebagai deteksi wajah
                    $faceVerificationPassed = true;
                    $faceConfidenceScore = 0.0; // Unknown confidence
                    $faceVerificationNotes = 'Face detected without confidence score';
                }

                // Save face photo if provided
                if ($request->has('face_photo')) {
                    Log::info('Face photo received for user', [
                        'user_id' => $user->id,
                        'photo_length' => strlen($request->face_photo),
                        'confidence_score' => $faceConfidenceScore,
                    ]);
                    $facePhotoPath = $this->saveFacePhoto($request->face_photo, $user->id, $today);
                    Log::info('Face photo saved result', [
                        'user_id' => $user->id,
                        'photo_path' => $facePhotoPath,
                    ]);
                } else {
                    Log::warning('Face photo not provided in request', [
                        'user_id' => $user->id,
                        'has_face_confidence' => $request->has('face_confidence'),
                    ]);
                }
            } else {
                // Tidak ada face data, skip verification
                $faceVerificationSkipped = true;
                $faceVerificationNotes = 'Face recognition skipped - no face data provided';
                $faceDetected = false;
            }
        } else {
            $faceVerificationSkipped = true;
            $faceVerificationNotes = 'Face recognition not required for this user';
            $faceDetected = false;
        }

        // Determine if late
        $checkInTime = now();
        $shift = null;

        // First try to use the selected shift from the request
        if ($request->has('selected_shift_id')) {
            $selectedShiftId = $request->input('selected_shift_id');
            // Verify this shift is assigned to the user
            $assignedShift = $user->employeeShifts()
                ->where('work_shift_id', $selectedShiftId)
                ->where('effective_date', '<=', $today)
                ->where(function ($query) use ($today) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $today);
                })
                ->with('workShift')
                ->first();

            if ($assignedShift) {
                $shift = $assignedShift;
                Log::info('Using selected shift for attendance', [
                    'user_id' => $user->id,
                    'selected_shift_id' => $selectedShiftId,
                    'shift_name' => $shift->workShift->name,
                ]);
            }
        }

        // If no valid selected shift, fall back to current assigned shift
        if (!$shift) {
            $shift = $user->employeeShifts()->current()->with('workShift')->first();
            Log::info('Using current assigned shift for attendance', [
                'user_id' => $user->id,
                'shift_name' => $shift?->workShift->name,
            ]);
        }

        $status = 'present';
        $lateDuration = 0;

        if ($shift && $shift->workShift) {
            $shiftStartTime = Carbon::parse($shift->workShift->start_time);
            // No tolerance - any check-in after shift start is considered late
            if ($checkInTime->format('H:i:s') > $shiftStartTime->format('H:i:s')) {
                $status = 'late';

                // Calculate late duration in minutes (absolute value to ensure positive)
                $checkInCarbon = Carbon::parse($checkInTime->format('H:i:s'));
                $lateDuration = abs($checkInCarbon->diffInMinutes($shiftStartTime));

                Log::info('Late check-in detected', [
                    'user_id' => $user->id,
                    'shift_start' => $shiftStartTime->format('H:i:s'),
                    'check_in_time' => $checkInTime->format('H:i:s'),
                    'late_minutes' => $lateDuration,
                ]);
            }
        }

        Log::info('About to save attendance record', [
            'user_id' => $user->id,
            'face_photo_path_value' => $facePhotoPath,
            'face_match_confidence' => $faceMatchConfidence,
            'face_verification_passed' => $faceVerificationPassed,
        ]);

        try {
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'office_location_id' => $request->office_location_id,
                    'work_shift_id' => $shift?->work_shift_id,
                    'check_in_time' => $checkInTime->format('H:i:s'),
                    'check_in_latitude' => $request->latitude,
                    'check_in_longitude' => $request->longitude,
                    'status' => $status,
                    'late_duration' => $lateDuration,
                    'face_match_confidence' => $faceMatchConfidence,
                    'face_verification_passed' => $faceVerificationPassed,
                    'face_verification_skipped' => $faceVerificationSkipped,
                    'face_verification_notes' => $faceVerificationNotes,
                    'face_photo_path' => $facePhotoPath,
                    'face_confidence_score' => $faceConfidenceScore,
                    'face_detected' => $faceDetected,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to save attendance record', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'data' => [
                    'office_location_id' => $request->office_location_id,
                    'work_shift_id' => $shift?->work_shift_id,
                ],
            ]);
            return back()->withErrors(['message' => 'Gagal menyimpan absensi: ' . $e->getMessage()]);
        }

        // Debug logging for saved attendance
        Log::info('Saved attendance with shift', [
            'attendance_id' => $attendance->id,
            'work_shift_id' => $attendance->work_shift_id,
            'shift_name' => $shift?->workShift->name,
        ]);

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
        $checkOutTime = now();

        // Create a full datetime for check-in time (today at the check-in time)
        $checkInDateTime = Carbon::today()->setTimeFromTimeString($attendance->check_in_time);

        // If check-out time is earlier than check-in time, it means it's an overnight shift
        // So we need to set the check-in datetime to yesterday
        if ($checkOutTime->lessThan($checkInDateTime)) {
            $checkInDateTime->subDay();
        }

        // Calculate the absolute difference in minutes
        $workDuration = $checkOutTime->diffInMinutes($checkInDateTime);

        Log::info('Work duration calculation', [
            'user_id' => $user->id,
            'check_in_time' => $attendance->check_in_time,
            'check_out_time' => $checkOutTime->format('H:i:s'),
            'check_in_datetime' => $checkInDateTime->format('Y-m-d H:i:s'),
            'work_duration_minutes' => $workDuration,
        ]);

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

    private function saveFacePhoto(string $base64Image, int $userId, Carbon $date): ?string
    {
        try {
            // Extract the base64 data (remove data:image/jpeg;base64, prefix)
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
                $extension = $matches[1];
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    return null;
                }

                // Create face photos directory if it doesn't exist
                $directory = storage_path('app/public/face-photos');
                if (! file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                // Generate filename: user_id_date_timestamp.extension
                $filename = sprintf(
                    'user_%d_%s_%s.%s',
                    $userId,
                    $date->format('Y-m-d'),
                    time(),
                    $extension
                );

                $fullPath = $directory . '/' . $filename;

                if (file_put_contents($fullPath, $imageData)) {
                    return 'face-photos/' . $filename;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to save face photo', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }
}
