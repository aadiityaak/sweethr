<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('disciplinary_violations')) {
            return;
        }

        Schema::create('disciplinary_violations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 191);
            $table->enum('category', ['ringan', 'sedang', 'berat'])->default('ringan');
            $table->unsignedTinyInteger('points')->default(5);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_violations');
    }
};
