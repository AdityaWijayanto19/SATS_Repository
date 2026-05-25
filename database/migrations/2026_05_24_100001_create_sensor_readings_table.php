<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->integer('heart_rate')->nullable();
            $table->integer('spo2')->nullable();
            $table->float('temperature')->nullable();
            $table->enum('status', ['normal', 'warning', 'critical'])->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('monitoring_sessions')->onDelete('cascade');
            $table->index('session_id');
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
