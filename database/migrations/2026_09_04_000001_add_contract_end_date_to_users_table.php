<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'contract_end_date')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->date('contract_end_date')->nullable()->after('contract_type')->comment('Tanggal akhir kontrak PKWT; null = PKWTT/tetap');
            $table->index(['contract_end_date']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['contract_end_date']);
            $table->dropColumn('contract_end_date');
        });
    }
};
