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
        Schema::table('attendances', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (! Schema::hasColumn('attendances', 'work_shift_id')) {
                $table->foreignId('work_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
                $table->index('work_shift_id'); // Add index for better performance
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['work_shift_id']);
            $table->dropIndex(['work_shift_id']);
            $table->dropColumn('work_shift_id');
        });
    }
};
