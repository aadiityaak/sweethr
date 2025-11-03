<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeValidationController;
use App\Http\Controllers\Api\FaceRecognitionController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\ShiftController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware(['auth:web'])->group(function () {
    // Auth routes (authenticated)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Employee Management
    Route::apiResource('api-employees', EmployeeController::class);
    Route::get('/employees/{employee}/attendance', [EmployeeController::class, 'attendance']);
    Route::get('/employees/{employee}/leave-requests', [EmployeeController::class, 'leaveRequests']);

    // Employee Validation
    Route::post('/employees/validate/employee-id', [EmployeeValidationController::class, 'checkEmployeeId']);
    Route::get('/employees/generate/next-id', [EmployeeValidationController::class, 'generateNextEmployeeId']);

    // Department Management
    Route::apiResource('departments', DepartmentController::class);
    Route::get('/departments/{department}/employees', [DepartmentController::class, 'employees']);

    // Attendance Management
    Route::apiResource('attendances', AttendanceController::class);
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendances/today', [AttendanceController::class, 'today']);
    Route::get('/attendances/report', [AttendanceController::class, 'report']);

    // Leave Management
    Route::apiResource('leaves', LeaveController::class);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveController::class, 'approve']);
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveController::class, 'reject']);
    Route::get('/leave-types', [LeaveController::class, 'types']);

    // Shift Management
    Route::apiResource('shifts', ShiftController::class);
    Route::get('/shifts/employee/{employee}', [ShiftController::class, 'employeeShifts']);
    Route::post('/shift-swaps', [ShiftController::class, 'requestSwap']);
    Route::post('/shift-swaps/{shiftSwap}/approve', [ShiftController::class, 'approveSwap']);
    Route::post('/shift-swaps/{shiftSwap}/reject', [ShiftController::class, 'rejectSwap']);

    // Office Locations
    Route::get('/office-locations', function () {
        return \App\Models\OfficeLocation::active()->get();
    });

    // Face Recognition
    Route::prefix('face-recognition')->group(function () {
        Route::post('/setup', [FaceRecognitionController::class, 'setup']);
        Route::post('/verify', [FaceRecognitionController::class, 'verify']);
        Route::get('/status', [FaceRecognitionController::class, 'status']);
        Route::get('/descriptors', [FaceRecognitionController::class, 'getDescriptors']);
        Route::delete('/delete', [FaceRecognitionController::class, 'delete']);
        Route::post('/reset-attempts', [FaceRecognitionController::class, 'resetAttempts']);
        Route::get('/stats', [FaceRecognitionController::class, 'stats']); // Admin only
    });
});
