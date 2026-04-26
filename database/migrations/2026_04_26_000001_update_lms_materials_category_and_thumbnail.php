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
            $table->string('category')->after('description')->default('module');
            $table->string('thumbnail_path')->nullable()->after('file_path');
        });

        DB::table('lms_materials')->update([
            'category' => DB::raw('type'),
        ]);

        Schema::table('lms_materials', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    public function down(): void
    {
        Schema::table('lms_materials', function (Blueprint $table) {
            $table->enum('type', ['video', 'pdf', 'module'])->after('description')->default('module');
        });

        DB::table('lms_materials')->update([
            'type' => DB::raw('category'),
        ]);

        Schema::table('lms_materials', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->dropColumn('thumbnail_path');
        });
    }
};
