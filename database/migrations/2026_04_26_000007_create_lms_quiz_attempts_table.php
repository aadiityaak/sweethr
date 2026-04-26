<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_quiz_id')->constrained('lms_quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('max_score')->nullable();
            $table->boolean('is_passed')->nullable();
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->index(['lms_quiz_id', 'user_id']);
            $table->index(['lms_quiz_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_attempts');
    }
};

