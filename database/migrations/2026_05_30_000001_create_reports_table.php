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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['kendala_perangkat', 'kendala_aplikasi', 'request_akun', 'lainnya']);
            $table->string('device_id', 50)->nullable();
            $table->enum('role_requested', ['nakes', 'dokter'])->nullable();
            $table->string('institution')->nullable();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 20)->nullable();
            $table->enum('urgency', ['rendah', 'sedang', 'darurat'])->default('sedang');
            $table->text('detail');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->text('admin_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('category');
            $table->index('email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
