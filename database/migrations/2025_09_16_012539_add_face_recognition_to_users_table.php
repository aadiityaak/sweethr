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
        Schema::table('users', function (Blueprint $table) {
            $table->json('face_descriptors')->nullable()->comment('Face recognition descriptors (encrypted)');
            $table->boolean('face_recognition_enabled')->default(false)->comment('Whether face recognition is enabled for this user');
            $table->timestamp('face_setup_at')->nullable()->comment('When face recognition was set up');
            $table->integer('face_recognition_attempts')->default(0)->comment('Failed face recognition attempts today');
            $table->date('face_attempts_date')->nullable()->comment('Date of failed attempts tracking');
            $table->boolean('face_recognition_mandatory')->default(true)->comment('Whether face recognition is mandatory for this user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'face_descriptors',
                'face_recognition_enabled',
                'face_setup_at',
                'face_recognition_attempts',
                'face_attempts_date',
                'face_recognition_mandatory',
            ]);
        });
    }
};
