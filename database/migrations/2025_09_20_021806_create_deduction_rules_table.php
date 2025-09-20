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
        Schema::create('deduction_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Telat, Alfa, Cuti Berlebih, BPJS, dll
            $table->enum('type', ['late', 'absent', 'excess_leave', 'other']);
            $table->enum('calculation_method', ['fixed', 'per_minute', 'per_hour', 'per_day', 'percentage']);
            $table->decimal('amount', 12, 2); // Amount atau percentage
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_rules');
    }
};
