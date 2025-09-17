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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('title', 191);
            $table->string('code', 191)->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_id');
            $table->integer('level')->default(1); // 1=staff, 2=supervisor, 3=manager, 4=director
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->json('permissions')->nullable(); // JSON array of permissions
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->index(['department_id', 'level']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
