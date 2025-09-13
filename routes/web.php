<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('user/Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Welcome page for all authenticated users
    Route::get('welcome', [App\Http\Controllers\DashboardController::class, 'welcome'])
        ->name('welcome');

    // Attendance routes (accessible by all employees)
    Route::get('attendance', [App\Http\Controllers\AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::get('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn'])
        ->name('attendance.check-in');
    Route::post('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'storeCheckIn'])
        ->name('attendance.store-check-in');
    Route::post('attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'storeCheckOut'])
        ->name('attendance.store-check-out');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Employee management (Admin only)
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);

    // Leave Requests management (Admin only)
    Route::resource('leave-requests', App\Http\Controllers\LeaveRequestController::class);
    Route::patch('leave-requests/{leaveRequest}/approve', [App\Http\Controllers\LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::patch('leave-requests/{leaveRequest}/reject', [App\Http\Controllers\LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');

    // Office Locations management (Admin only)
    Route::resource('office-locations', App\Http\Controllers\OfficeLocationController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
