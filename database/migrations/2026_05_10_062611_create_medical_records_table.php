<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('patient_id');
            $table->string('device_id', 50);
            $table->integer('heart_rate');
            $table->integer('spo2');
            $table->float('temperature');
            $table->enum('status', ['normal', 'warning', 'critical']);
            $table->string('prediction')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('patient_id');
            $table->index('device_id');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
