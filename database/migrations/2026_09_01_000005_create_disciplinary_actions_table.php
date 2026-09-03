<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('disciplinary_actions')) {
            return;
        }

        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action_type', ['teguran_lisan', 'sp1', 'sp2', 'sp3', 'phk_eval']);
            $table->unsignedTinyInteger('triggered_points');
            $table->enum('status', ['active', 'resolved', 'revoked'])->default('active');
            $table->dateTime('issued_at');
            $table->date('freeze_until')->nullable()->comment('SP1: pembekuan promosi sampai tanggal ini');
            $table->boolean('required_remediation')->default(false)->comment('SP2: wajib remedial LMS');
            $table->boolean('suspend_incentive')->default(false)->comment('SP3: penangguhan insentif');
            $table->string('document_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'action_type', 'status'], 'disciplinary_actions_unique_active');
            $table->index(['user_id', 'issued_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinary_actions');
    }
};
