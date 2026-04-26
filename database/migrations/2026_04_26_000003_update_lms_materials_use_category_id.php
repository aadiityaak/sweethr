<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_materials', function (Blueprint $table) {
            $table->foreignId('lms_category_id')->nullable()->after('description')->constrained('lms_categories');
        });

        $defaults = [
            'video' => 'Video',
            'pdf' => 'PDF',
            'module' => 'Modul',
        ];

        $categoryIds = [];
        foreach ($defaults as $key => $name) {
            $id = DB::table('lms_categories')->insertGetId([
                'parent_id' => null,
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categoryIds[$key] = $id;
        }

        foreach ($categoryIds as $legacy => $id) {
            DB::table('lms_materials')
                ->where('category', $legacy)
                ->update(['lms_category_id' => $id]);
        }

        Schema::table('lms_materials', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('lms_materials', function (Blueprint $table) {
            $table->string('category')->after('description')->default('module');
        });

        $map = [
            'Video' => 'video',
            'PDF' => 'pdf',
            'Modul' => 'module',
        ];

        foreach ($map as $name => $legacy) {
            $id = DB::table('lms_categories')->where('name', $name)->whereNull('parent_id')->value('id');
            if (! $id) {
                continue;
            }
            DB::table('lms_materials')->where('lms_category_id', $id)->update(['category' => $legacy]);
        }

        Schema::table('lms_materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lms_category_id');
        });

        DB::table('lms_categories')->whereIn('name', ['Video', 'PDF', 'Modul'])->whereNull('parent_id')->delete();
    }
};
