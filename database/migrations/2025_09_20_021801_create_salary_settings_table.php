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
        Schema::create('salary_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->json('allowances')->nullable(); // Transport, makan, jabatan, dll
            $table->decimal('overtime_rate', 3, 2)->default(1.5); // 1.5x gaji normal
            $table->date('effective_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_settings');
    }
};
