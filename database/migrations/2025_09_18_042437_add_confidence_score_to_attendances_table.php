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
            $table->decimal('face_confidence_score', 5, 4)->nullable()->after('face_photo_path')
                ->comment('Face recognition confidence score (0.0000 to 1.0000)');
            $table->boolean('face_detected', false)->default(false)->after('face_confidence_score')
                ->comment('Whether any face was detected during attendance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['face_confidence_score', 'face_detected']);
        });
    }
};
