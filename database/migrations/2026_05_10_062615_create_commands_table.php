<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commands', function (Blueprint $table) {
            $table->id();

            $table->string('device_id', 50);
            $table->enum('command', ['start', 'stop']);
            $table->enum('status', ['pending', 'done'])->default('pending');

            $table->timestamps();

            // Indexes
            $table->index('device_id');
            $table->index('status');

            // Foreign key
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commands');
    }
};
