<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructions', function (Blueprint $table) {
            $table->id();

            // Device reference
            $table->string('device_id', 50);

            // Users: dokter yang memberi instruksi, nakes yang melaksanakan
            $table->foreignId('dokter_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('nakes_id')->nullable()->constrained('users')->onDelete('set null');

            // Instruction content & response
            $table->text('instruksi_dokter')->nullable();
            $table->text('respon_nakes')->nullable();
            $table->text('laporan_nakes')->nullable();

            // Status tracking
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Indexes for better query performance
            $table->index(['device_id', 'is_completed']);
            $table->index(['dokter_id']);
            $table->index(['nakes_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructions');
    }
};
