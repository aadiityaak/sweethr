<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Jika user tidak login, redirect ke /login
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    // Jika user sudah login (baik admin maupun karyawan), redirect ke /home
    return redirect()->route('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Welcome page for all authenticated users
    Route::get('home', [App\Http\Controllers\DashboardController::class, 'welcome'])
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

    // Leave Requests (accessible by all employees)
    Route::get('leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'index'])
        ->name('leave-requests.index');
    Route::get('leave-requests/create', [App\Http\Controllers\LeaveRequestController::class, 'create'])
        ->name('leave-requests.create');
    Route::post('leave-requests', [App\Http\Controllers\LeaveRequestController::class, 'store'])
        ->name('leave-requests.store');
    Route::get('leave-requests/{leave_request}', [App\Http\Controllers\LeaveRequestController::class, 'show'])
        ->name('leave-requests.show');
    Route::get('leave-requests/{leave_request}/edit', [App\Http\Controllers\LeaveRequestController::class, 'edit'])
        ->name('leave-requests.edit');
    Route::put('leave-requests/{leave_request}', [App\Http\Controllers\LeaveRequestController::class, 'update'])
        ->name('leave-requests.update');
    Route::delete('leave-requests/{leave_request}', [App\Http\Controllers\LeaveRequestController::class, 'destroy'])
        ->name('leave-requests.destroy');

    // User Profile Settings
    Route::get('user/profile', [App\Http\Controllers\User\ProfileController::class, 'show'])
        ->name('user.profile.show');
    Route::put('user/profile/basic', [App\Http\Controllers\User\ProfileController::class, 'updateBasic'])
        ->name('user.profile.update-basic');
    Route::put('user/profile/password', [App\Http\Controllers\User\ProfileController::class, 'updatePassword'])
        ->name('user.profile.update-password');
});

// Admin-only routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Employee management (Admin only)
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);

    // Leave Requests approval/rejection (Admin only)
    Route::patch('leave-requests/{leaveRequest}/approve', [App\Http\Controllers\LeaveRequestController::class, 'approve'])
        ->name('leave-requests.approve');
    Route::patch('leave-requests/{leaveRequest}/reject', [App\Http\Controllers\LeaveRequestController::class, 'reject'])
        ->name('leave-requests.reject');

    // Office Locations management (Admin only)
    Route::resource('office-locations', App\Http\Controllers\OfficeLocationController::class);

    // Admin Attendance Management
    Route::get('admin/attendance', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])
        ->name('admin.attendance.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
