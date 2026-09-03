<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('semester_reports')) {
            return;
        }

        Schema::create('semester_reports', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->enum('semester', ['1', '2']);
            $table->decimal('kpi_score', 5, 2)->default(0);
            $table->decimal('lms_score', 5, 2)->default(0);
            $table->decimal('discipline_score', 5, 2)->default(0);
            $table->decimal('final_score', 5, 2)->default(0);
            $table->enum('grade', ['A', 'B', 'C', 'D', 'E'])->default('E');
            $table->unsignedTinyInteger('total_violation_points')->default(0);
            $table->unsignedTinyInteger('attendance_rate')->default(0);
            $table->json('recommendation')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'year', 'semester']);
            $table->index(['year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_reports');
    }
};
