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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis_kelamin');
            $table->string('nik');
            $table->integer('umur')->unsigned();
            $table->string('jenis_penyakit')->nullable();
            $table->text('catatan_tambahan')->nullable();
            $table->timestamps();

            $table->foreignId('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('petugas')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
