<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lms_category_id')->constrained('lms_categories');
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->unsignedInteger('max_score')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['lms_category_id', 'is_active']);
            $table->index(['due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
