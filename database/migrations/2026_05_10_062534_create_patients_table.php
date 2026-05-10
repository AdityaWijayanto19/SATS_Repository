<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->string('device_id', 50);
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->integer('umur');
            $table->text('catatan_tambahan')->nullable();
            $table->unsignedBigInteger('nakes_id');

            $table->timestamps();

            // Indexes
            $table->index('device_id');
            $table->index('nakes_id');

            // Foreign keys
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('nakes_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
