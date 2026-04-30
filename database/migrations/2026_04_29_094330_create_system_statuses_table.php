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
        Schema::create('system_statuses', function (Blueprint $table) {
            $table->string('device_id', 50)->primary();

            $table->enum('monitoring_status', ['active', 'inactive'])->nullable();

            $table->integer('battery_level')->nullable();   // 0 - 100
            $table->integer('signal_strength')->nullable(); // RSSI

            $table->timestamp('updated_at')->nullable();

            // FOREIGN KEY
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
        Schema::dropIfExists('system_statuses');
    }
};
