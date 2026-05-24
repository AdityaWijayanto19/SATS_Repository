<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('device_id', 50);
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('medical_record_number', 50)->unique();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->enum('status', ['active', 'pending', 'completed', 'cancelled'])->default('active');
            $table->integer('total_readings')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            $table->index(['device_id', 'status']);
            $table->index('created_by');
            $table->index('medical_record_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_sessions');
    }
};
