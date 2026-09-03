<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('employment_contracts')) {
            return;
        }

        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('contract_number', 100)->unique();
            $table->enum('type', ['pkwt', 'pkwtt'])->default('pkwt');
            $table->date('start_date');
            $table->date('end_date')->nullable()->comment('Null = PKWTT / tanpa batas waktu');
            $table->enum('status', ['active', 'expired', 'renewed', 'terminated'])->default('active');
            $table->string('salary_grade', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
