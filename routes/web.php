<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Attendance routes
    Route::get('attendance', [App\Http\Controllers\AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::get('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'checkIn'])
        ->name('attendance.check-in');
    Route::post('attendance/check-in', [App\Http\Controllers\AttendanceController::class, 'storeCheckIn'])
        ->name('attendance.store-check-in');
    Route::post('attendance/check-out', [App\Http\Controllers\AttendanceController::class, 'storeCheckOut'])
        ->name('attendance.store-check-out');

    // Employee management
    Route::resource('employees', App\Http\Controllers\EmployeeController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
