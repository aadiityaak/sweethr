<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Basic Company Configuration
            CompanySettingSeeder::class,

            // Core HR Structure (Order matters for FK relationships)
            DepartmentSeeder::class,
            PositionSeeder::class,
            WorkShiftSeeder::class,
            LeaveTypeSeeder::class,
            OfficeLocationSeeder::class,

            // Users (depends on departments & positions)
            AdminUserSeeder::class,

            // Department Management (depends on users being created)
            DepartmentManagerSeeder::class,

            // Employee Work Assignment (depends on users & work shifts)
            EmployeeShiftSeeder::class,

            // Document Types (independent)
            DocumentTypeSeeder::class,

            // Salary and Payroll (depends on users)
            DeductionRuleSeeder::class,
            SalarySettingSeeder::class,

            // Announcement System
            AnnouncementCategorySeeder::class,
            AnnouncementSeeder::class,

            // LMS
            LmsCategorySeeder::class,
            LmsMaterialSeeder::class,
            LmsAssignmentSeeder::class,
            LmsQuizSeeder::class,

            // Sample Data (optional for testing)
            LeaveRequestSeeder::class,
            LeaveExchangeSeeder::class,
            ShiftChangeRequestSeeder::class,
            AttendanceSeeder::class,
            EmployeeDocumentSeeder::class,
            ShiftSwapSeeder::class,
            PayrollSeeder::class,
        ]);
    }
}
