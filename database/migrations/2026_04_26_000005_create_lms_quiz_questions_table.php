<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->enum('type', ['multiple_choice', 'true_false', 'short_answer']);
            $table->text('question');
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['lms_quiz_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_questions');
    }
};
