<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employee_violations')) {
            return;
        }

        Schema::create('employee_violations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('disciplinary_violation_id')->constrained('disciplinary_violations')->cascadeOnDelete();
            $table->dateTime('occurred_at');
            $table->unsignedTinyInteger('points');
            $table->text('notes')->nullable();
            $table->string('evidence_path', 255)->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['manual', 'auto_attendance'])->default('manual');
            $table->timestamps();

            $table->index(['user_id', 'occurred_at']);
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_violations');
    }
};
