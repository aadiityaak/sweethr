<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if we're running in an environment that needs this fix
        // (older MySQL versions with utf8mb4 charset)
        $shouldApplyFix = $this->shouldApplyKeyLengthFix();

        if (!$shouldApplyFix) {
            return;
        }

        // Fix string columns that might be too long for compound indexes
        // Only apply if table exists and has the old structure

        if (Schema::hasTable('work_shifts') && Schema::hasColumn('work_shifts', 'name')) {
            Schema::table('work_shifts', function (Blueprint $table) {
                // Check current column length and only modify if it's 191
                $columnType = Schema::getColumnType('work_shifts', 'name');
                if (strpos($columnType, '191') !== false) {
                    $table->string('name', 100)->change();
                }

                $codeColumnType = Schema::getColumnType('work_shifts', 'code');
                if (strpos($codeColumnType, '191') !== false) {
                    $table->string('code', 50)->unique()->change();
                }
            });
        }

        if (Schema::hasTable('positions') && Schema::hasColumn('positions', 'title')) {
            Schema::table('positions', function (Blueprint $table) {
                $titleColumnType = Schema::getColumnType('positions', 'title');
                if (strpos($titleColumnType, '191') !== false) {
                    $table->string('title', 100)->change();
                }

                $codeColumnType = Schema::getColumnType('positions', 'code');
                if (strpos($codeColumnType, '191') !== false) {
                    $table->string('code', 50)->unique()->change();
                }
            });
        }

        if (Schema::hasTable('departments') && Schema::hasColumn('departments', 'name')) {
            Schema::table('departments', function (Blueprint $table) {
                $nameColumnType = Schema::getColumnType('departments', 'name');
                if (strpos($nameColumnType, '191') !== false) {
                    $table->string('name', 100)->change();
                }

                $codeColumnType = Schema::getColumnType('departments', 'code');
                if (strpos($codeColumnType, '191') !== false) {
                    $table->string('code', 50)->unique()->change();
                }
            });
        }

        if (Schema::hasTable('office_locations') && Schema::hasColumn('office_locations', 'name')) {
            Schema::table('office_locations', function (Blueprint $table) {
                $nameColumnType = Schema::getColumnType('office_locations', 'name');
                if (strpos($nameColumnType, '191') !== false) {
                    $table->string('name', 100)->change();
                }
            });
        }

        if (Schema::hasTable('leave_types') && Schema::hasColumn('leave_types', 'name')) {
            Schema::table('leave_types', function (Blueprint $table) {
                $nameColumnType = Schema::getColumnType('leave_types', 'name');
                if (strpos($nameColumnType, '191') !== false) {
                    $table->string('name', 100)->change();
                }

                $codeColumnType = Schema::getColumnType('leave_types', 'code');
                if (strpos($codeColumnType, '191') !== false) {
                    $table->string('code', 50)->unique()->change();
                }
            });
        }

        // Fix users table employee_id and phone if they're 191 chars
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $employeeIdType = Schema::getColumnType('users', 'employee_id');
                if (strpos($employeeIdType, '191') !== false) {
                    $table->string('employee_id', 50)->unique()->nullable()->change();
                }

                if (Schema::hasColumn('users', 'phone')) {
                    $phoneType = Schema::getColumnType('users', 'phone');
                    if (strpos($phoneType, '191') !== false) {
                        $table->string('phone', 20)->nullable()->change();
                    }
                }
            });
        }

        // Fix attachment filename if it's 191 chars
        if (Schema::hasTable('leave_requests') && Schema::hasColumn('leave_requests', 'attachment_original_name')) {
            Schema::table('leave_requests', function (Blueprint $table) {
                $attachmentType = Schema::getColumnType('leave_requests', 'attachment_original_name');
                if (strpos($attachmentType, '191') !== false) {
                    $table->string('attachment_original_name', 100)->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is designed to be safe and not reversible
        // because reverting could cause data truncation
        // If you need to revert, do it manually after ensuring data safety
    }

    /**
     * Determine if we should apply the key length fix
     *
     * @return bool
     */
    private function shouldApplyKeyLengthFix(): bool
    {
        try {
            // Get MySQL version
            $version = \DB::select("SELECT VERSION() as version")[0]->version;

            // Extract major and minor version numbers
            preg_match('/^(\d+)\.(\d+)/', $version, $matches);
            $majorVersion = (int)($matches[1] ?? 0);
            $minorVersion = (int)($matches[2] ?? 0);

            // Check if it's MySQL 5.7 or earlier, or MariaDB 10.2 or earlier
            if (strpos(strtolower($version), 'mariadb') !== false) {
                // MariaDB
                return $majorVersion < 10 || ($majorVersion == 10 && $minorVersion < 3);
            } else {
                // MySQL
                return $majorVersion < 8 && ($majorVersion < 5 || ($majorVersion == 5 && $minorVersion < 8));
            }
        } catch (\Exception $e) {
            // If we can't determine the version, apply the fix to be safe
            return true;
        }
    }
};