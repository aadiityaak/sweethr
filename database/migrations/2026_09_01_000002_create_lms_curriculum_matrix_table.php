<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lms_curriculum_matrix')) {
            return;
        }

        Schema::create('lms_curriculum_matrix', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('lms_category_id')->nullable()->constrained('lms_categories')->nullOnDelete();
            $table->foreignId('lms_material_id')->nullable()->constrained('lms_materials')->nullOnDelete();
            $table->foreignId('lms_quiz_id')->nullable()->constrained('lms_quizzes')->nullOnDelete();
            $table->foreignId('lms_assignment_id')->nullable()->constrained('lms_assignments')->nullOnDelete();
            $table->enum('item_type', ['material', 'quiz', 'assignment'])->default('material');
            $table->boolean('is_mandatory')->default(true);
            $table->unsignedTinyInteger('deadline_days')->nullable()->comment('Batas hari sejak assignment ke karyawan');
            $table->timestamps();

            $table->index(['position_id', 'item_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_curriculum_matrix');
    }
};
