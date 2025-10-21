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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('period_month'); // Start date of payroll period
            $table->date('end_date')->nullable()->after('start_date'); // End date of payroll period
            $table->foreignId('payroll_period_id')->nullable()->after('user_id')->constrained()->onDelete('set null');

            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropForeign(['payroll_period_id']);
            $table->dropColumn(['start_date', 'end_date', 'payroll_period_id']);
        });
    }
};
