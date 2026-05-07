<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_performance_appraisal_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('managerial_only')->default(false);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        DB::table('lms_performance_appraisal_parameters')->insert([
            [
                'key' => 'quality_work',
                'group' => 'Kompetensi Teknis (Hard Skills)',
                'label' => 'Kualitas Kerja',
                'sort_order' => 10,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'quantity_work',
                'group' => 'Kompetensi Teknis (Hard Skills)',
                'label' => 'Kuantitas Kerja',
                'sort_order' => 20,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'task_knowledge',
                'group' => 'Kompetensi Teknis (Hard Skills)',
                'label' => 'Pengetahuan Tugas',
                'sort_order' => 30,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'discipline',
                'group' => 'Perilaku Kerja (Soft Skills)',
                'label' => 'Kedisiplinan',
                'sort_order' => 40,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'teamwork',
                'group' => 'Perilaku Kerja (Soft Skills)',
                'label' => 'Kerja Sama Tim',
                'sort_order' => 50,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'communication',
                'group' => 'Perilaku Kerja (Soft Skills)',
                'label' => 'Komunikasi',
                'sort_order' => 60,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'initiative',
                'group' => 'Perilaku Kerja (Soft Skills)',
                'label' => 'Inisiatif',
                'sort_order' => 70,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'target_realization',
                'group' => 'Pencapaian Target (KPI)',
                'label' => 'Realisasi Target',
                'sort_order' => 80,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'time_management',
                'group' => 'Pencapaian Target (KPI)',
                'label' => 'Manajemen Waktu',
                'sort_order' => 90,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'attitude',
                'group' => 'Sikap dan Adaptabilitas',
                'label' => 'Sikap (Attitude)',
                'sort_order' => 100,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'adaptability',
                'group' => 'Sikap dan Adaptabilitas',
                'label' => 'Adaptabilitas',
                'sort_order' => 110,
                'is_active' => true,
                'managerial_only' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'leadership_delegation',
                'group' => 'Kepemimpinan (Khusus Level Manajerial)',
                'label' => 'Delegasi',
                'sort_order' => 120,
                'is_active' => true,
                'managerial_only' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'leadership_development',
                'group' => 'Kepemimpinan (Khusus Level Manajerial)',
                'label' => 'Pengembangan Anggota',
                'sort_order' => 130,
                'is_active' => true,
                'managerial_only' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_performance_appraisal_parameters');
    }
};
