<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'contract_type')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->enum('contract_type', ['pkwt', 'pkwtt'])->nullable()->default('pkwt')->after('employment_status')->comment('Klasifikasi legalitas kerja: PKWT vs PKWTT');
            $table->index(['contract_type']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['contract_type']);
            $table->dropColumn('contract_type');
        });
    }
};
