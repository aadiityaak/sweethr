<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Jika user sudah login, redirect ke dashboard sesuai role
    if (auth()->check()) {
        if (auth()->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('welcome');
    }

    // Jika user belum login, tampilkan halaman landing publik
    return \Inertia\Inertia::render('Public/Landing');
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

    // Shift Change Requests (accessible by all employees)
    Route::get('shift-change-requests', [App\Http\Controllers\ShiftChangeRequestController::class, 'index'])
        ->name('shift-change-requests.index');
    Route::get('shift-change-requests/create', [App\Http\Controllers\ShiftChangeRequestController::class, 'create'])
        ->name('shift-change-requests.create');
    Route::post('shift-change-requests', [App\Http\Controllers\ShiftChangeRequestController::class, 'store'])
        ->name('shift-change-requests.store');
    Route::get('shift-change-requests/{shiftChangeRequest}', [App\Http\Controllers\ShiftChangeRequestController::class, 'show'])
        ->name('shift-change-requests.show');
    Route::delete('shift-change-requests/{shiftChangeRequest}', [App\Http\Controllers\ShiftChangeRequestController::class, 'destroy'])
        ->name('shift-change-requests.destroy');

    // User Profile Settings
    Route::get('user/profile', [App\Http\Controllers\User\ProfileController::class, 'show'])
        ->name('user.profile.show');
    Route::put('user/profile/basic', [App\Http\Controllers\User\ProfileController::class, 'updateBasic'])
        ->name('user.profile.update-basic');
    Route::put('user/profile/password', [App\Http\Controllers\User\ProfileController::class, 'updatePassword'])
        ->name('user.profile.update-password');
    Route::post('user/profile/avatar', [App\Http\Controllers\User\ProfileController::class, 'uploadAvatar'])
        ->name('user.profile.upload-avatar');
    Route::delete('user/profile/avatar', [App\Http\Controllers\User\ProfileController::class, 'deleteAvatar'])
        ->name('user.profile.delete-avatar');

    // Employee Payroll
    Route::get('payrolls', [App\Http\Controllers\Employee\PayrollController::class, 'index'])
        ->name('employee.payrolls.index');
    Route::get('payrolls/{payroll}', [App\Http\Controllers\Employee\PayrollController::class, 'show'])
        ->name('employee.payrolls.show');

    Route::get('lms', [App\Http\Controllers\User\LmsController::class, 'index'])
        ->name('user.lms.index');

    Route::get('lms/quizzes', [App\Http\Controllers\User\LmsQuizController::class, 'index'])
        ->name('user.lms-quizzes.index');
    Route::get('lms/quizzes/{lms_quiz}', [App\Http\Controllers\User\LmsQuizController::class, 'show'])
        ->name('user.lms-quizzes.show');
    Route::post('lms/quizzes/{lms_quiz}/start', [App\Http\Controllers\User\LmsQuizController::class, 'start'])
        ->name('user.lms-quizzes.start');
    Route::get('lms/quizzes/{lms_quiz}/attempts/{attempt}/take', [App\Http\Controllers\User\LmsQuizController::class, 'take'])
        ->name('user.lms-quizzes.attempts.take');
    Route::post('lms/quizzes/{lms_quiz}/attempts/{attempt}/submit', [App\Http\Controllers\User\LmsQuizController::class, 'submit'])
        ->name('user.lms-quizzes.attempts.submit');
    Route::get('lms/quizzes/{lms_quiz}/attempts/{attempt}', [App\Http\Controllers\User\LmsQuizController::class, 'result'])
        ->name('user.lms-quizzes.attempts.show');

    Route::get('lms/assignments', [App\Http\Controllers\User\LmsAssignmentController::class, 'index'])
        ->name('user.lms-assignments.index');
    Route::get('lms/assignments/{lms_assignment}', [App\Http\Controllers\User\LmsAssignmentController::class, 'show'])
        ->name('user.lms-assignments.show');
    Route::post('lms/assignments/{lms_assignment}/submit', [App\Http\Controllers\User\LmsAssignmentController::class, 'submit'])
        ->name('user.lms-assignments.submit');

    Route::get('lms/{lms_material}', [App\Http\Controllers\User\LmsController::class, 'show'])
        ->name('user.lms.show');

    // ===== Self-Service Karyawan: Kontrak, Disiplin, Raport Semester =====
    Route::get('my/contract', [App\Http\Controllers\User\SelfServiceController::class, 'contract'])
        ->name('user.my-contract');
    Route::get('my/disciplinary-record', [App\Http\Controllers\User\SelfServiceController::class, 'disciplinaryRecord'])
        ->name('user.my-disciplinary-record');
    Route::get('my/semester-report', [App\Http\Controllers\User\SelfServiceController::class, 'semesterReport'])
        ->name('user.my-semester-report');
});

// ===== ADMIN: LMS Dashboard, Kontrak Kerja, Disiplin & Raport Semester =====
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // Executive LMS Dashboard (5 widget)
    Route::get('admin/lms-dashboard', [App\Http\Controllers\Admin\LmsDashboardController::class, 'index'])
        ->name('admin.lms-dashboard');

    // Employment Contracts (PKWT/PKWTT) + alerts
    Route::get('admin/contracts/alerts', [App\Http\Controllers\Admin\EmploymentContractController::class, 'alerts'])
        ->name('admin.contracts.alerts');
    Route::patch('admin/contracts/{contract}/renew', [App\Http\Controllers\Admin\EmploymentContractController::class, 'renew'])
        ->name('admin.contracts.renew');
    Route::patch('admin/contracts/{contract}/convert-pkwtt', [App\Http\Controllers\Admin\EmploymentContractController::class, 'convertToPkwtt'])
        ->name('admin.contracts.convert-pkwtt');
    Route::resource('admin/contracts', App\Http\Controllers\Admin\EmploymentContractController::class, [
        'except' => ['show'],
        'names' => [
            'index' => 'admin.contracts.index',
            'create' => 'admin.contracts.create',
            'store' => 'admin.contracts.store',
            'edit' => 'admin.contracts.edit',
            'update' => 'admin.contracts.update',
            'destroy' => 'admin.contracts.destroy',
        ],
    ]);

    // Curriculum Matrix (matriks jabatan x modul LMS)
    Route::resource('admin/curriculum-matrix', App\Http\Controllers\Admin\CurriculumMatrixController::class, [
        'except' => ['show'],
        'names' => [
            'index' => 'admin.curriculum-matrix.index',
            'create' => 'admin.curriculum-matrix.create',
            'store' => 'admin.curriculum-matrix.store',
            'edit' => 'admin.curriculum-matrix.edit',
            'update' => 'admin.curriculum-matrix.update',
            'destroy' => 'admin.curriculum-matrix.destroy',
        ],
    ]);

    // Disciplinary Violations (master kategori)
    Route::resource('admin/disciplinary-violations', App\Http\Controllers\Admin\DisciplinaryViolationController::class, [
        'names' => [
            'index' => 'admin.disciplinary-violations.index',
            'store' => 'admin.disciplinary-violations.store',
            'update' => 'admin.disciplinary-violations.update',
            'destroy' => 'admin.disciplinary-violations.destroy',
        ],
    ])->only(['index', 'store', 'update', 'destroy']);

    // Employee Violations (feed pelanggaran)
    Route::resource('admin/employee-violations', App\Http\Controllers\Admin\EmployeeViolationController::class, [
        'names' => [
            'index' => 'admin.employee-violations.index',
            'store' => 'admin.employee-violations.store',
            'destroy' => 'admin.employee-violations.destroy',
        ],
    ])->only(['index', 'store', 'destroy']);

    // Disciplinary Actions (SP)
    Route::get('admin/disciplinary-actions', [App\Http\Controllers\Admin\EmployeeViolationController::class, 'actionsIndex'])
        ->name('admin.disciplinary-actions.index');
    Route::patch('admin/disciplinary-actions/{disciplinary_action}/confirm', [App\Http\Controllers\Admin\EmployeeViolationController::class, 'confirmAction'])
        ->name('admin.disciplinary-actions.confirm');
    Route::patch('admin/disciplinary-actions/{disciplinary_action}/resolve', [App\Http\Controllers\Admin\EmployeeViolationController::class, 'resolveAction'])
        ->name('admin.disciplinary-actions.resolve');
    Route::patch('admin/disciplinary-actions/{disciplinary_action}/revoke', [App\Http\Controllers\Admin\EmployeeViolationController::class, 'revokeAction'])
        ->name('admin.disciplinary-actions.revoke');

    // Semester Reports (raport kinerja)
    Route::post('admin/semester-reports/generate', [App\Http\Controllers\Admin\SemesterReportController::class, 'generate'])
        ->name('admin.semester-reports.generate');
    Route::patch('admin/semester-reports/{semester_report}/publish', [App\Http\Controllers\Admin\SemesterReportController::class, 'publish'])
        ->name('admin.semester-reports.publish');
    Route::post('admin/semester-reports/bulk-publish', [App\Http\Controllers\Admin\SemesterReportController::class, 'bulkPublish'])
        ->name('admin.semester-reports.bulk-publish');
    Route::resource('admin/semester-reports', App\Http\Controllers\Admin\SemesterReportController::class, [
        'names' => [
            'index' => 'admin.semester-reports.index',
            'show' => 'admin.semester-reports.show',
        ],
    ])->only(['index', 'show']);
});

// PWA routes
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('admin/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Employee management (Admin only) - Limited access
    Route::get('employees', [App\Http\Controllers\EmployeeController::class, 'index'])
        ->name('employees.index');
    Route::get('employees/create', [App\Http\Controllers\EmployeeController::class, 'create'])
        ->name('employees.create');
    Route::post('employees', [App\Http\Controllers\EmployeeController::class, 'store'])
        ->name('employees.store');
    Route::get('employees/{employee}/edit', [App\Http\Controllers\EmployeeController::class, 'edit'])
        ->name('employees.edit');
    Route::put('employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'update'])
        ->name('employees.update');
    Route::delete('employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'destroy'])
        ->name('employees.destroy');

    // Removed show route to restrict detailed employee data access

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
    Route::get('admin/attendance/export', [App\Http\Controllers\Admin\AttendanceController::class, 'export'])
        ->name('admin.attendance.export');
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
    Route::delete('admin/leave-requests/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'destroy'])
        ->name('admin.leave-requests.destroy');

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

    // Admin Announcements Management
    Route::resource('admin/announcements', App\Http\Controllers\Admin\AnnouncementController::class, [
        'names' => [
            'index' => 'admin.announcements.index',
            'create' => 'admin.announcements.create',
            'store' => 'admin.announcements.store',
            'show' => 'admin.announcements.show',
            'edit' => 'admin.announcements.edit',
            'update' => 'admin.announcements.update',
            'destroy' => 'admin.announcements.destroy',
        ],
    ]);
    Route::post('admin/announcements/{announcement}/update', [App\Http\Controllers\Admin\AnnouncementController::class, 'updateWithFiles'])
        ->name('admin.announcements.update-with-files');
    Route::patch('admin/announcements/{announcement}/toggle-status', [App\Http\Controllers\Admin\AnnouncementController::class, 'toggleStatus'])
        ->name('admin.announcements.toggle-status');

    // Admin LMS Materials Management
    Route::resource('admin/lms-materials', App\Http\Controllers\Admin\LmsMaterialController::class, [
        'names' => [
            'index' => 'admin.lms-materials.index',
            'create' => 'admin.lms-materials.create',
            'store' => 'admin.lms-materials.store',
            'show' => 'admin.lms-materials.show',
            'edit' => 'admin.lms-materials.edit',
            'update' => 'admin.lms-materials.update',
            'destroy' => 'admin.lms-materials.destroy',
        ],
    ]);
    Route::post('admin/lms-materials/{lms_material}/update', [App\Http\Controllers\Admin\LmsMaterialController::class, 'updateWithFiles'])
        ->name('admin.lms-materials.update-with-files');

    // Admin LMS Categories Management
    Route::resource('admin/lms-categories', App\Http\Controllers\Admin\LmsCategoryController::class, [
        'except' => ['show'],
        'names' => [
            'index' => 'admin.lms-categories.index',
            'create' => 'admin.lms-categories.create',
            'store' => 'admin.lms-categories.store',
            'edit' => 'admin.lms-categories.edit',
            'update' => 'admin.lms-categories.update',
            'destroy' => 'admin.lms-categories.destroy',
        ],
    ]);

    // Admin LMS Quiz Management
    Route::resource('admin/lms-quizzes', App\Http\Controllers\Admin\LmsQuizController::class, [
        'names' => [
            'index' => 'admin.lms-quizzes.index',
            'create' => 'admin.lms-quizzes.create',
            'store' => 'admin.lms-quizzes.store',
            'show' => 'admin.lms-quizzes.show',
            'edit' => 'admin.lms-quizzes.edit',
            'update' => 'admin.lms-quizzes.update',
            'destroy' => 'admin.lms-quizzes.destroy',
        ],
    ]);
    Route::post('admin/lms-quizzes/{lms_quiz}/questions', [App\Http\Controllers\Admin\LmsQuizController::class, 'storeQuestion'])
        ->name('admin.lms-quizzes.questions.store');
    Route::put('admin/lms-quizzes/{lms_quiz}/questions/{question}', [App\Http\Controllers\Admin\LmsQuizController::class, 'updateQuestion'])
        ->name('admin.lms-quizzes.questions.update');
    Route::delete('admin/lms-quizzes/{lms_quiz}/questions/{question}', [App\Http\Controllers\Admin\LmsQuizController::class, 'destroyQuestion'])
        ->name('admin.lms-quizzes.questions.destroy');
    Route::get('admin/lms-quizzes/{lms_quiz}/attempts', [App\Http\Controllers\Admin\LmsQuizController::class, 'attempts'])
        ->name('admin.lms-quizzes.attempts.index');

    // Admin LMS Assignment Management
    Route::resource('admin/lms-assignments', App\Http\Controllers\Admin\LmsAssignmentController::class, [
        'names' => [
            'index' => 'admin.lms-assignments.index',
            'create' => 'admin.lms-assignments.create',
            'store' => 'admin.lms-assignments.store',
            'show' => 'admin.lms-assignments.show',
            'edit' => 'admin.lms-assignments.edit',
            'update' => 'admin.lms-assignments.update',
            'destroy' => 'admin.lms-assignments.destroy',
        ],
    ]);
    Route::get('admin/lms-assignments/{lms_assignment}/submissions', [App\Http\Controllers\Admin\LmsAssignmentController::class, 'submissions'])
        ->name('admin.lms-assignments.submissions.index');

    Route::get('admin/lms-tracking', [App\Http\Controllers\Admin\LmsTrackingController::class, 'index'])
        ->name('admin.lms-tracking.index');

    Route::get('admin/lms-performance-appraisals', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'index'])
        ->name('admin.lms-performance-appraisals.index');
    Route::get('admin/lms-performance-appraisals/create', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'create'])
        ->name('admin.lms-performance-appraisals.create');
    Route::post('admin/lms-performance-appraisals', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'store'])
        ->name('admin.lms-performance-appraisals.store');
    Route::get('admin/lms-performance-appraisals/{lms_performance_appraisal}/edit', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'edit'])
        ->name('admin.lms-performance-appraisals.edit');
    Route::put('admin/lms-performance-appraisals/{lms_performance_appraisal}', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'update'])
        ->name('admin.lms-performance-appraisals.update');
    Route::delete('admin/lms-performance-appraisals/{lms_performance_appraisal}', [App\Http\Controllers\Admin\LmsPerformanceAppraisalController::class, 'destroy'])
        ->name('admin.lms-performance-appraisals.destroy');

    Route::get('admin/lms-performance-appraisal-parameters', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'index'])
        ->name('admin.lms-performance-appraisal-parameters.index');
    Route::get('admin/lms-performance-appraisal-parameters/create', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'create'])
        ->name('admin.lms-performance-appraisal-parameters.create');
    Route::post('admin/lms-performance-appraisal-parameters', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'store'])
        ->name('admin.lms-performance-appraisal-parameters.store');
    Route::get('admin/lms-performance-appraisal-parameters/{parameter}/edit', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'edit'])
        ->name('admin.lms-performance-appraisal-parameters.edit');
    Route::put('admin/lms-performance-appraisal-parameters/{parameter}', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'update'])
        ->name('admin.lms-performance-appraisal-parameters.update');
    Route::delete('admin/lms-performance-appraisal-parameters/{parameter}', [App\Http\Controllers\Admin\LmsPerformanceAppraisalParameterController::class, 'destroy'])
        ->name('admin.lms-performance-appraisal-parameters.destroy');

    // Admin Payroll Management
    Route::get('admin/salary-settings', [App\Http\Controllers\Admin\SalarySettingController::class, 'index'])
        ->name('admin.salary-settings.index');
    Route::get('admin/salary-settings/{user}', [App\Http\Controllers\Admin\SalarySettingController::class, 'show'])
        ->name('admin.salary-settings.show');
    Route::get('admin/salary-settings/{user}/edit', [App\Http\Controllers\Admin\SalarySettingController::class, 'edit'])
        ->name('admin.salary-settings.edit');
    Route::put('admin/salary-settings/{user}', [App\Http\Controllers\Admin\SalarySettingController::class, 'update'])
        ->name('admin.salary-settings.update');

    Route::resource('admin/deduction-rules', App\Http\Controllers\Admin\DeductionRuleController::class, [
        'names' => [
            'index' => 'admin.deduction-rules.index',
            'create' => 'admin.deduction-rules.create',
            'store' => 'admin.deduction-rules.store',
            'edit' => 'admin.deduction-rules.edit',
            'update' => 'admin.deduction-rules.update',
            'destroy' => 'admin.deduction-rules.destroy',
        ],
    ]);

    Route::get('admin/payrolls', [App\Http\Controllers\Admin\PayrollController::class, 'index'])
        ->name('admin.payrolls.index');
    Route::get('admin/payrolls/{payroll}', [App\Http\Controllers\Admin\PayrollController::class, 'show'])
        ->name('admin.payrolls.show');
    Route::post('admin/payrolls/generate', [App\Http\Controllers\Admin\PayrollController::class, 'generate'])
        ->name('admin.payrolls.generate');
    Route::post('admin/payrolls/{payroll}/regenerate', [App\Http\Controllers\Admin\PayrollController::class, 'regenerate'])
        ->name('admin.payrolls.regenerate');

    // Admin Payroll Periods Management
    Route::get('admin/payroll-periods', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'index'])
        ->name('admin.payroll-periods.index');
    Route::get('admin/payroll-periods/create', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'create'])
        ->name('admin.payroll-periods.create');
    Route::post('admin/payroll-periods', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'store'])
        ->name('admin.payroll-periods.store');
    Route::get('admin/payroll-periods/{payrollPeriod}', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'show'])
        ->name('admin.payroll-periods.show');
    Route::get('admin/payroll-periods/{payrollPeriod}/edit', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'edit'])
        ->name('admin.payroll-periods.edit');
    Route::put('admin/payroll-periods/{payrollPeriod}', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'update'])
        ->name('admin.payroll-periods.update');
    Route::delete('admin/payroll-periods/{payrollPeriod}', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'destroy'])
        ->name('admin.payroll-periods.destroy');
    Route::post('admin/payroll-periods/{payrollPeriod}/set-active', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'setActive'])
        ->name('admin.payroll-periods.set-active');
    Route::post('admin/payroll-periods/{payrollPeriod}/generate-payroll', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'generatePayroll'])
        ->name('admin.payroll-periods.generate-payroll');
    Route::post('admin/payroll-periods/generate-yearly', [App\Http\Controllers\Admin\PayrollPeriodController::class, 'generateYearlyPeriods'])
        ->name('admin.payroll-periods.generate-yearly');

    // Admin Document Management
    Route::resource('admin/documents', App\Http\Controllers\Admin\DocumentController::class, [
        'names' => [
            'index' => 'admin.documents.index',
            'create' => 'admin.documents.create',
            'store' => 'admin.documents.store',
            'show' => 'admin.documents.show',
            'edit' => 'admin.documents.edit',
            'update' => 'admin.documents.update',
            'destroy' => 'admin.documents.destroy',
        ],
    ]);
    Route::get('admin/documents/{document}/download', [App\Http\Controllers\Admin\DocumentController::class, 'download'])
        ->name('admin.documents.download');

    // Admin Shift Change Requests Management
    Route::get('admin/shift-change-requests', [App\Http\Controllers\Admin\ShiftChangeRequestController::class, 'index'])
        ->name('admin.shift-change-requests.index');
    Route::get('admin/shift-change-requests/{shiftChangeRequest}', [App\Http\Controllers\Admin\ShiftChangeRequestController::class, 'show'])
        ->name('admin.shift-change-requests.show');
    Route::patch('admin/shift-change-requests/{shiftChangeRequest}/approve', [App\Http\Controllers\Admin\ShiftChangeRequestController::class, 'approve'])
        ->name('admin.shift-change-requests.approve');
    Route::patch('admin/shift-change-requests/{shiftChangeRequest}/reject', [App\Http\Controllers\Admin\ShiftChangeRequestController::class, 'reject'])
        ->name('admin.shift-change-requests.reject');
    Route::delete('admin/shift-change-requests/{shiftChangeRequest}', [App\Http\Controllers\Admin\ShiftChangeRequestController::class, 'destroy'])
        ->name('admin.shift-change-requests.destroy');
});

// PWA routes
Route::get('/manifest.json', function () {
    return response()->file(public_path('manifest.json'))
        ->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');

Route::get('/sw.js', function () {
    return response()->file(public_path('sw.js'))
        ->header('Content-Type', 'application/javascript');
})->name('pwa.service-worker');

Route::get('/offline', function () {
    return \Inertia\Inertia::render('PWA/Offline');
})->name('pwa.offline');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
