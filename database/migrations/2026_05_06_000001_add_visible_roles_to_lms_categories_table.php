<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_categories', function (Blueprint $table) {
            $table->json('visible_roles')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('lms_categories', function (Blueprint $table) {
            $table->dropColumn('visible_roles');
        });
    }
};

