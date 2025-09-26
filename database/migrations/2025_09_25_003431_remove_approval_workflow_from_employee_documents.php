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
        // Use raw SQL to safely drop foreign key if it exists
        $foreignKeyExists = \Illuminate\Support\Facades\DB::select("
            SELECT COUNT(*) as count
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE CONSTRAINT_NAME = 'employee_documents_approved_by_foreign'
            AND TABLE_SCHEMA = DATABASE()
        ");

        if ($foreignKeyExists[0]->count > 0) {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE employee_documents DROP FOREIGN KEY employee_documents_approved_by_foreign');
        }

        Schema::table('employee_documents', function (Blueprint $table) {
            // Drop columns if they exist
            $columnsToRemove = ['status', 'approved_by', 'approved_at', 'rejection_reason'];
            $existingColumns = [];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('employee_documents', $column)) {
                    $existingColumns[] = $column;
                }
            }

            if (! empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
        });
    }
};
