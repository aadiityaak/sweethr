<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_performance_appraisal_parameters', function (Blueprint $table) {
            $table->json('visible_position_ids')->nullable()->after('managerial_only');
        });

        $managerPositionIds = DB::table('positions')
            ->where('is_active', true)
            ->where('level', '>=', 3)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (count($managerPositionIds) > 0) {
            DB::table('lms_performance_appraisal_parameters')
                ->whereIn('key', ['leadership_delegation', 'leadership_development'])
                ->update([
                    'visible_position_ids' => json_encode($managerPositionIds),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('lms_performance_appraisal_parameters', function (Blueprint $table) {
            $table->dropColumn('visible_position_ids');
        });
    }
};

