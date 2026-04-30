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
        Schema::create('sensor_datas', function (Blueprint $table) {
            $table->id();

            $table->string('device_id', 50);

            $table->integer('heart_rate')->nullable();
            $table->integer('spo2')->nullable();
            $table->float('temperature')->nullable();

            $table->enum('status', ['normal', 'warning', 'critical'])->nullable();
            $table->string('prediction')->nullable();

            $table->timestamps();

            // index
            $table->index('device_id');
            $table->index('created_at');

            // foreign key
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sensor_datas');
    }
};
