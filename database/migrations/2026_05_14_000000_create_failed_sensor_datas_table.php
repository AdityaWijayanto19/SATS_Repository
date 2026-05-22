<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_sensor_datas', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->json('payload');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->foreign('device_id')->references('device_id')->on('devices')->onDelete('cascade');
            $table->index(['device_id', 'failed_at']);
            $table->index('retry_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_sensor_datas');
    }
};
