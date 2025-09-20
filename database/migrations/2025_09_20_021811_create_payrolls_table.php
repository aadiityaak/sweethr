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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->year('period_year');
            $table->tinyInteger('period_month'); // 1-12
            $table->decimal('base_salary', 12, 2);
            $table->json('allowances')->nullable(); // Breakdown tunjangan
            $table->decimal('gross_salary', 12, 2); // Sebelum potongan
            $table->json('deductions')->nullable(); // Breakdown potongan
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2); // Setelah potongan
            $table->integer('working_days')->default(22);
            $table->integer('actual_working_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('late_minutes', 8, 2)->default(0);
            $table->integer('absent_days')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'period_year', 'period_month']);
            $table->index(['period_year', 'period_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
