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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique(); // Reduced from 191 to 100
            $table->text('value')->nullable();
            $table->string('type', 50)->default('text'); // text, textarea, image, file, boolean
            $table->string('group', 50)->default('general'); // Reduced from 100 to 50
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Whether setting can be accessed publicly
            $table->timestamps();

            // Create separate indexes to avoid MySQL key length limit
            $table->index('key', 'company_settings_key_index');
            $table->index('group', 'company_settings_group_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
