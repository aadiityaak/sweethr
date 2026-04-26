<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_category_id')->constrained('lms_categories');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->unsignedTinyInteger('passing_score')->default(70);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['lms_category_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_quizzes');
    }
};
