<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_performance_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('evaluated_at');

            $table->unsignedTinyInteger('quality_work');
            $table->unsignedTinyInteger('quantity_work');
            $table->unsignedTinyInteger('task_knowledge');

            $table->unsignedTinyInteger('discipline');
            $table->unsignedTinyInteger('teamwork');
            $table->unsignedTinyInteger('communication');
            $table->unsignedTinyInteger('initiative');

            $table->unsignedTinyInteger('target_realization');
            $table->unsignedTinyInteger('time_management');

            $table->unsignedTinyInteger('attitude');
            $table->unsignedTinyInteger('adaptability');

            $table->unsignedTinyInteger('leadership_delegation')->nullable();
            $table->unsignedTinyInteger('leadership_development')->nullable();

            $table->text('feedback')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'evaluated_at']);
            $table->index(['evaluator_id', 'evaluated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_performance_appraisals');
    }
};

