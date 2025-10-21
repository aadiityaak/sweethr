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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Period September 2025"
            $table->integer('cut_off_day')->default(26); // Day of month when period starts (e.g., 26)
            $table->date('start_date'); // Start date of this period
            $table->date('end_date'); // End date of this period
            $table->boolean('is_active')->default(true); // Whether this period is currently active
            $table->text('description')->nullable(); // Optional description
            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
