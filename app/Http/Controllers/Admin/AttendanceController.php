<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function index(Request $request): Response
    {
        // Prevent caching
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Get filters
        $filters = $request->only(['date', 'date_from', 'date_to', 'status', 'department', 'office_location', 'search', 'sort', 'direction']);

        // Handle date range logic - default to current month
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $date = $filters['date'] ?? Carbon::today()->format('Y-m-d');

        // Special handling for absent status - show employees without attendance records
        if (($filters['status'] ?? '') === 'absent') {
            // Get employees who don't have attendance record for the date
            $employeesQuery = User::with(['department:id,name', 'position:id,title'])
                ->where('is_admin', false)
                ->whereDoesntHave('attendances', function ($query) use ($date) {
                    $query->where('date', $date);
                })
                ->when($filters['department'] ?? false, function ($query, $department) {
                    $query->where('department_id', $department);
                })
                ->when($filters['search'] ?? false, function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
                });

            // Add sorting for absent employees
            $sort = $filters['sort'] ?? 'name';
            $direction = $filters['direction'] ?? 'asc';

            // Map frontend sort fields to database columns
            $sortMapping = [
                'user.name' => 'name',
                'date' => 'date', // Not applicable for absent records
                'check_in_time' => 'check_in_time', // Not applicable for absent records
                'status' => 'status',
                'work_duration' => 'work_duration', // Not applicable for absent records
            ];

            $dbSort = $sortMapping[$sort] ?? 'name';
            if ($dbSort !== 'date' && $dbSort !== 'check_in_time' && $dbSort !== 'work_duration') {
                $absentEmployees = $employeesQuery->orderBy($dbSort, $direction)->get();
            } else {
                // For fields not applicable to absent records, default to name sorting
                $absentEmployees = $employeesQuery->orderBy('name', $direction)->get();
            }

            // Convert to attendance record format for consistency
            $attendanceRecords = $absentEmployees->map(function ($user) use ($date) {
                // Get user's assigned shift for the date (since there's no attendance record)
                $shift = $user->employeeShifts()
                    ->where('effective_date', '<=', $date)
                    ->where(function ($query) use ($date) {
                        $query->whereNull('end_date')
                            ->orWhere('end_date', '>=', $date);
                    })
                    ->with('workShift')
                    ->first();

                $workShift = $shift?->workShift;

                if ($workShift) {
                    // Add shift info for display - format times as strings
                    $shiftInfo = [
                        'start_time' => $workShift->start_time instanceof \DateTime
                            ? $workShift->start_time->format('H:i:s')
                            : $workShift->start_time,
                        'end_time' => $workShift->end_time instanceof \DateTime
                            ? $workShift->end_time->format('H:i:s')
                            : $workShift->end_time,
                        'name' => $workShift->name ?? $workShift->start_time.' - '.$workShift->end_time,
                    ];
                } else {
                    // Add default shift info
                    $shiftInfo = [
                        'start_time' => '08:30:00',
                        'end_time' => '17:00:00',
                        'name' => '08:30 - 17:00',
                    ];
                }

                return (object) [
                    'id' => null,
                    'user_id' => $user->id,
                    'date' => $date,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'status' => 'absent',
                    'work_duration' => null,
                    'late_duration' => null,
                    'overtime_duration' => null,
                    'office_location' => null,
                    'notes' => null,
                    'user' => $user,
                    'shift_info' => $shiftInfo,
                ];
            });
        } else {
            // Query attendance records with filters (normal statuses)
            $attendanceQuery = Attendance::with([
                'user' => function ($query) {
                    $query->with(['department:id,name', 'position:id,title']);
                },
                'officeLocation:id,name,address',
            ])
                ->whereBetween('date', [$dateFrom, $dateTo])
                ->when($filters['status'] ?? false, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->when($filters['department'] ?? false, function ($query, $department) {
                    $query->whereHas('user', function ($q) use ($department) {
                        $q->where('department_id', $department);
                    });
                })
                ->when($filters['office_location'] ?? false, function ($query, $officeLocation) {
                    $query->where('office_location_id', $officeLocation);
                })
                ->when($filters['search'] ?? false, function ($query, $search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
                });

            // Add sorting for attendance records
            $sort = $filters['sort'] ?? 'date';
            $direction = $filters['direction'] ?? 'desc';

            // Map frontend sort fields to database columns with relationships
            $sortMapping = [
                'user.name' => 'user.name',
                'shift_info.name' => 'date', // fallback to date for shift sorting
                'date' => 'date',
                'check_in_time' => 'check_in_time',
                'check_out_time' => 'check_out_time',
                'work_duration' => 'work_duration',
                'status' => 'status',
                'office_location.name' => 'office_location.name',
            ];

            $dbSort = $sortMapping[$sort] ?? 'date';

            // Apply sorting with relationship handling
            if (str_contains($dbSort, '.')) {
                // Handle relationship-based sorting
                [$relation, $column] = explode('.', $dbSort);
                if ($relation === 'user') {
                    $attendanceQuery->join('users', 'attendances.user_id', '=', 'users.id')
                        ->orderBy('users.'.$column, $direction)
                        ->select('attendances.*'); // Select only attendance columns to avoid conflicts
                } elseif ($relation === 'office_location') {
                    $attendanceQuery->join('office_locations', 'attendances.office_location_id', '=', 'office_locations.id')
                        ->orderBy('office_locations.'.$column, $direction)
                        ->select('attendances.*');
                } else {
                    $attendanceQuery->orderBy($dbSort, $direction);
                }
            } else {
                // Direct column sorting
                $attendanceQuery->orderBy($dbSort, $direction);
            }

            $attendanceRecords = $attendanceQuery->get();

            // Calculate late duration for each attendance record and add shift info
            $attendanceRecords = $attendanceRecords->map(function ($record) {
                // PRIORITY 1: Use the work_shift_id saved in attendance record (user's selected shift)
                $workShift = null;
                if ($record->work_shift_id) {
                    $workShift = \App\Models\WorkShift::find($record->work_shift_id);
                }

                // FALLBACK: Get user's assigned shift for the attendance date
                if (! $workShift) {
                    $shift = $record->user->employeeShifts()
                        ->where('effective_date', '<=', $record->date)
                        ->where(function ($query) use ($record) {
                            $query->whereNull('end_date')
                                ->orWhere('end_date', '>=', $record->date);
                        })
                        ->with('workShift')
                        ->first();

                    $workShift = $shift?->workShift;
                }

                if ($workShift) {
                    // Ensure shift start time is in proper format
                    $shiftStartTime = $workShift->start_time instanceof \DateTime
                        ? $workShift->start_time->format('H:i:s')
                        : $workShift->start_time;

                    // Calculate late duration - use existing value if available, otherwise calculate
                    if ($record->late_duration !== null) {
                        // Use existing stored value (already calculated at check-in)
                        $lateDuration = $record->late_duration;
                    } else {
                        // Calculate on the fly for backward compatibility
                        $lateDuration = $record->getLateDuration($shiftStartTime);
                    }

                    $record->late_duration = $lateDuration;

                    // Add shift info for display - format times as strings
                    $record->shift_info = [
                        'start_time' => $workShift->start_time instanceof \DateTime
                            ? $workShift->start_time->format('H:i:s')
                            : $workShift->start_time,
                        'end_time' => $workShift->end_time instanceof \DateTime
                            ? $workShift->end_time->format('H:i:s')
                            : $workShift->end_time,
                        'name' => $workShift->name ?? $workShift->start_time.' - '.$workShift->end_time,
                    ];
                } else {
                    // Default to 08:30 if no shift found (for existing data)
                    $record->late_duration = $record->getLateDuration('08:30:00');

                    // Add default shift info
                    $record->shift_info = [
                        'start_time' => '08:30:00',
                        'end_time' => '17:00:00',
                        'name' => '08:30 - 17:00',
                    ];
                }

                return $record;
            });
        }

        // Calculate attendance statistics for the selected date range
        $totalEmployees = User::where('is_admin', false)->count();
        $presentToday = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->whereIn('status', ['present', 'late'])
            ->count();
        $lateToday = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->where('status', 'late')
            ->count();

        // Calculate working days in range for absent calculation
        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);
        $workingDays = 0;
        while ($startDate->lte($endDate)) {
            if (! $startDate->isWeekend()) {
                $workingDays++;
            }
            $startDate->addDay();
        }

        $expectedAttendance = $totalEmployees * $workingDays;
        $absentToday = $expectedAttendance - $presentToday;
        $onLeaveToday = 0; // This would need leave request integration

        $attendanceRate = $expectedAttendance > 0 ? round(($presentToday / $expectedAttendance) * 100) : 0;

        // Calculate average work hours and overtime for the date range
        $avgWorkHours = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->whereNotNull('work_duration')
            ->avg('work_duration') ?? 0;
        $avgWorkHours = round($avgWorkHours / 60, 1); // Convert to hours

        $totalOvertimeHours = Attendance::whereBetween('date', [$dateFrom, $dateTo])
            ->sum('overtime_duration') ?? 0;
        $totalOvertimeHours = round($totalOvertimeHours / 60, 1); // Convert to hours

        $stats = [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'late_today' => $lateToday,
            'absent_today' => $absentToday,
            'on_leave_today' => $onLeaveToday,
            'attendance_rate' => $attendanceRate,
            'average_work_hours' => $avgWorkHours,
            'total_overtime_hours' => $totalOvertimeHours,
        ];

        // Get departments for filter dropdown
        $departments = Department::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get office locations for filter dropdown
        $officeLocations = OfficeLocation::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Ensure date_from and date_to are in filters for frontend
        $filters['date_from'] = $dateFrom;
        $filters['date_to'] = $dateTo;

        return Inertia::render('admin/Attendance/Index', [
            'attendanceRecords' => $attendanceRecords,
            'stats' => $stats,
            'filters' => $filters,
            'departments' => $departments,
            'officeLocations' => $officeLocations,
        ]);
    }

    public function show(Attendance $attendance): Response
    {
        // Load the attendance record with all related data
        $attendance->load([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation',
        ]);

        // Convert face photo path to URL if exists
        if ($attendance->face_photo_path) {
            $attendance->face_photo_url = asset('storage/'.$attendance->face_photo_path);
        }

        return Inertia::render('admin/Attendance/Show', [
            'attendance' => $attendance,
        ]);
    }

    public function edit(Attendance $attendance): Response
    {
        // Load the attendance record with all related data
        $attendance->load([
            'user' => function ($query) {
                $query->with(['department:id,name', 'position:id,title']);
            },
            'officeLocation',
        ]);

        // Get all office locations for the dropdown
        $officeLocations = OfficeLocation::select('id', 'name', 'address')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/Attendance/Edit', [
            'attendance' => $attendance,
            'officeLocations' => $officeLocations,
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $data = $request->validated();

        // Calculate work duration if both check in and check out times are provided
        if ($data['check_in_time'] && $data['check_out_time']) {
            $checkIn = Carbon::parse($data['check_in_time']);
            $checkOut = Carbon::parse($data['check_out_time']);
            $data['work_duration'] = $checkOut->diffInMinutes($checkIn);
        } else {
            $data['work_duration'] = null;
        }

        // Update the attendance record
        $attendance->update($data);

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', 'Data kehadiran berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $employeeName = $attendance->user->name;
        $date = $attendance->date->format('d/m/Y');

        $attendance->delete();

        return redirect()
            ->route('admin.attendance.index')
            ->with('success', "Data kehadiran {$employeeName} tanggal {$date} berhasil dihapus.");
    }

    public function export(Request $request)
    {
        // Get filters from request
        $filters = $request->only(['date', 'date_from', 'date_to', 'status', 'department', 'office_location', 'search']);

        // Handle date range for filename
        if (($filters['date_from'] ?? null) && ($filters['date_to'] ?? null)) {
            $dateFromFormatted = Carbon::parse($filters['date_from'])->format('d-m-Y');
            $dateToFormatted = Carbon::parse($filters['date_to'])->format('d-m-Y');
            $filename = "laporan-kehadiran-{$dateFromFormatted}-sampai-{$dateToFormatted}.xlsx";
        } else {
            $date = $filters['date'] ?? Carbon::today()->format('Y-m-d');
            $dateFormatted = Carbon::parse($date)->format('d-m-Y');
            $filename = "laporan-kehadiran-{$dateFormatted}.xlsx";
        }

        // Export to Excel
        return Excel::download(new AttendanceExport($filters), $filename);
    }
}
