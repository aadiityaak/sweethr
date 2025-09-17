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
            $table->string('key', 191)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('text'); // text, textarea, image, file, boolean
            $table->string('group', 100)->default('general'); // general, branding, contact, etc
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Whether setting can be accessed publicly
            $table->timestamps();

            $table->index(['key', 'group']);
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
