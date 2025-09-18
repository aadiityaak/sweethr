<?php

/**
 * MySQL Troubleshooting Script for SweetHR
 *
 * This script helps diagnose and fix common MySQL issues with Laravel migrations
 * Run with: php database/troubleshoot-mysql.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

class MySQLTroubleshooter
{
    public function run()
    {
        echo "🔍 MySQL Troubleshooting for SweetHR\n";
        echo "==================================\n\n";

        $this->checkConnection();
        $this->checkVersion();
        $this->checkCharset();
        $this->checkKeyLengthLimits();
        $this->checkProblematicTables();
        $this->provideSolutions();
    }

    private function checkConnection()
    {
        echo "📡 Testing Database Connection...\n";
        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            echo "✅ Database connection successful\n";
            echo "   Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
            echo "   Server: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n\n";
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n\n";
            exit(1);
        }
    }

    private function checkVersion()
    {
        echo "🔢 Checking MySQL/MariaDB Version...\n";
        try {
            $version = DB::select("SELECT VERSION() as version")[0]->version;
            echo "   Version: $version\n";

            $isMariaDB = stripos($version, 'mariadb') !== false;
            $isOldVersion = $this->isOldVersion($version, $isMariaDB);

            if ($isOldVersion) {
                echo "⚠️  OLD VERSION DETECTED - May have utf8mb4 key length issues\n";
            } else {
                echo "✅ Version should support longer keys\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "❌ Could not determine version: " . $e->getMessage() . "\n\n";
        }
    }

    private function checkCharset()
    {
        echo "🔤 Checking Character Set Configuration...\n";
        try {
            $charset = DB::select("SELECT @@character_set_database as charset")[0]->charset;
            $collation = DB::select("SELECT @@collation_database as collation")[0]->collation;

            echo "   Database Charset: $charset\n";
            echo "   Database Collation: $collation\n";

            if ($charset === 'utf8mb4') {
                echo "✅ Using utf8mb4 (recommended for full Unicode support)\n";
                echo "⚠️  Note: utf8mb4 requires shorter key lengths on older MySQL\n";
            } else {
                echo "⚠️  Using $charset (consider upgrading to utf8mb4)\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "❌ Could not check charset: " . $e->getMessage() . "\n\n";
        }
    }

    private function checkKeyLengthLimits()
    {
        echo "🔑 Checking Key Length Limits...\n";
        try {
            $innodbPageSize = DB::select("SELECT @@innodb_page_size as size")[0]->size;
            $maxKeyLength = ($innodbPageSize / 4); // Rough calculation

            echo "   InnoDB Page Size: $innodbPageSize bytes\n";
            echo "   Estimated Max Key Length: ~$maxKeyLength bytes\n";
            echo "   utf8mb4 uses 4 bytes per character\n";
            echo "   Safe string length for utf8mb4 keys: ~" . floor($maxKeyLength / 4 / 2) . " chars\n\n";
        } catch (Exception $e) {
            echo "❌ Could not check key limits: " . $e->getMessage() . "\n\n";
        }
    }

    private function checkProblematicTables()
    {
        echo "🔍 Checking for Problematic Table Structures...\n";

        $tables = [
            'company_settings' => ['key', 'group'],
            'work_shifts' => ['name', 'code'],
            'positions' => ['title', 'code'],
            'departments' => ['name', 'code'],
            'office_locations' => ['name'],
            'leave_types' => ['name', 'code'],
            'users' => ['employee_id', 'phone', 'email'],
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) {
                echo "   ⏭️  Table '$table' doesn't exist - skipping\n";
                continue;
            }

            echo "   📋 Checking table: $table\n";

            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }

                try {
                    $columnInfo = DB::select("SHOW COLUMNS FROM $table LIKE '$column'")[0];
                    $type = $columnInfo->Type;

                    if (preg_match('/varchar\((\d+)\)/', $type, $matches)) {
                        $length = (int)$matches[1];
                        if ($length > 100) {
                            echo "      ⚠️  Column '$column': $type (may cause key length issues)\n";
                        } else {
                            echo "      ✅ Column '$column': $type (OK)\n";
                        }
                    } else {
                        echo "      ✅ Column '$column': $type (OK)\n";
                    }
                } catch (Exception $e) {
                    echo "      ❌ Could not check column '$column': " . $e->getMessage() . "\n";
                }
            }
        }
        echo "\n";
    }

    private function provideSolutions()
    {
        echo "💡 Solutions and Recommendations:\n";
        echo "================================\n\n";

        echo "1. 🔧 If you get 'Specified key was too long' errors:\n";
        echo "   - Run the fix migration: php artisan migrate\n";
        echo "   - This will automatically detect and fix string length issues\n\n";

        echo "2. 🗄️  For new installations on older MySQL (5.7 or earlier):\n";
        echo "   - Ensure you're using the updated migrations with shorter string lengths\n";
        echo "   - Consider upgrading to MySQL 8.0+ for better utf8mb4 support\n\n";

        echo "3. 🔄 If migration fails with table already exists:\n";
        echo "   - Check migration status: php artisan migrate:status\n";
        echo "   - Clear cache: php artisan cache:clear && php artisan config:clear\n";
        echo "   - If needed, manually mark as ran: (see Laravel docs)\n\n";

        echo "4. 🎯 Best Practices:\n";
        echo "   - Keep string columns for keys/indexes under 100 characters\n";
        echo "   - Use separate indexes instead of compound indexes when possible\n";
        echo "   - Test migrations on staging environment first\n";
        echo "   - Always backup database before running migrations\n\n";

        echo "5. 🚀 For production deployment:\n";
        echo "   - Run migrations with --force flag\n";
        echo "   - Monitor for any schema-related errors\n";
        echo "   - Have rollback plan ready\n\n";
    }

    private function isOldVersion($version, $isMariaDB)
    {
        preg_match('/^(\d+)\.(\d+)/', $version, $matches);
        $majorVersion = (int)($matches[1] ?? 0);
        $minorVersion = (int)($matches[2] ?? 0);

        if ($isMariaDB) {
            return $majorVersion < 10 || ($majorVersion == 10 && $minorVersion < 3);
        } else {
            return $majorVersion < 8;
        }
    }
}

// Run the troubleshooter
try {
    $troubleshooter = new MySQLTroubleshooter();
    $troubleshooter->run();
} catch (Exception $e) {
    echo "❌ Script failed: " . $e->getMessage() . "\n";
    echo "Make sure you're running this from the Laravel project root.\n";
}