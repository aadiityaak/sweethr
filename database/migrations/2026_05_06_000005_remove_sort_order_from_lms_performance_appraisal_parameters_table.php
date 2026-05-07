<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_performance_appraisal_parameters', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('lms_performance_appraisal_parameters', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('label');
            $table->index(['is_active', 'sort_order']);
        });
    }
};

