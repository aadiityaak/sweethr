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
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('face_match_confidence', 5, 2)->nullable()->comment('Face recognition confidence score (0-100)');
            $table->boolean('face_verification_passed')->default(false)->comment('Whether face verification passed');
            $table->boolean('face_verification_skipped')->default(false)->comment('Whether face verification was skipped (fallback)');
            $table->text('face_verification_notes')->nullable()->comment('Notes about face verification (admin use)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'face_match_confidence',
                'face_verification_passed',
                'face_verification_skipped',
                'face_verification_notes',
            ]);
        });
    }
};
