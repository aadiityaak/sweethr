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

    // Dashboard route that redirects based on user role
    Route::get('dashboard', function () {
        if (auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('welcome');
    })->name('dashboard');

    // User dashboard (alias for welcome page)
    Route::get('user/dashboard', [App\Http\Controllers\DashboardController::class, 'welcome'])
        ->name('user.dashboard');

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
    Route::get('admin/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('admin.dashboard');

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
    Route::get('admin/attendance/{attendance}', [App\Http\Controllers\Admin\AttendanceController::class, 'show'])
        ->name('admin.attendance.show');
    Route::get('admin/attendance/{attendance}/edit', [App\Http\Controllers\Admin\AttendanceController::class, 'edit'])
        ->name('admin.attendance.edit');
    Route::put('admin/attendance/{attendance}', [App\Http\Controllers\Admin\AttendanceController::class, 'update'])
        ->name('admin.attendance.update');
    Route::delete('admin/attendance/{attendance}', [App\Http\Controllers\Admin\AttendanceController::class, 'destroy'])
        ->name('admin.attendance.destroy');

    // Admin Leave Requests Management
    Route::get('admin/leave-requests', [App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])
        ->name('admin.leave-requests.index');
    Route::patch('admin/leave-requests/{leaveRequest}/approve', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])
        ->name('admin.leave-requests.approve');
    Route::patch('admin/leave-requests/{leaveRequest}/reject', [App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])
        ->name('admin.leave-requests.reject');

    // Admin Work Shifts Management
    Route::resource('admin/work-shifts', App\Http\Controllers\Admin\WorkShiftController::class, [
        'names' => [
            'index' => 'admin.work-shifts.index',
            'create' => 'admin.work-shifts.create',
            'store' => 'admin.work-shifts.store',
            'show' => 'admin.work-shifts.show',
            'edit' => 'admin.work-shifts.edit',
            'update' => 'admin.work-shifts.update',
            'destroy' => 'admin.work-shifts.destroy',
        ],
    ]);
    Route::post('admin/work-shifts/{workShift}/assign', [App\Http\Controllers\Admin\WorkShiftController::class, 'assign'])
        ->name('admin.work-shifts.assign');
    Route::post('admin/work-shifts/{workShift}/unassign', [App\Http\Controllers\Admin\WorkShiftController::class, 'unassign'])
        ->name('admin.work-shifts.unassign');
    Route::get('admin/work-shifts-employees', [App\Http\Controllers\Admin\WorkShiftController::class, 'employees'])
        ->name('admin.work-shifts.employees');

    // Admin Department Management
    Route::resource('admin/departments', App\Http\Controllers\Admin\DepartmentController::class, [
        'names' => [
            'index' => 'admin.departments.index',
            'create' => 'admin.departments.create',
            'store' => 'admin.departments.store',
            'show' => 'admin.departments.show',
            'edit' => 'admin.departments.edit',
            'update' => 'admin.departments.update',
            'destroy' => 'admin.departments.destroy',
        ],
    ]);

    // Admin Company Settings
    Route::get('admin/settings', [App\Http\Controllers\Admin\CompanySettingController::class, 'index'])
        ->name('admin.settings.index');
    Route::put('admin/settings', [App\Http\Controllers\Admin\CompanySettingController::class, 'update'])
        ->name('admin.settings.update');
    Route::delete('admin/settings/file/{key}', [App\Http\Controllers\Admin\CompanySettingController::class, 'deleteFile'])
        ->name('admin.settings.delete-file');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
