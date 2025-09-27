<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shift_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('original_date'); // tanggal libur asli
            $table->date('requested_date'); // tanggal ganti kerja
            $table->text('reason')->nullable(); // alasan optional
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable(); // catatan admin saat review
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable(); // kapan di-review
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // admin yang review
            $table->timestamps();

            // Index untuk performa
            $table->index(['user_id', 'status']);
            $table->index(['requested_at']);
            $table->index(['original_date', 'requested_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_change_requests');
    }
};
