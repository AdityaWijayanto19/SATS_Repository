<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('no_rekam_medis', 50)->unique()->after('id');
            $table->string('nik', 20)->nullable()->after('nama');
            $table->date('tanggal_lahir')->nullable()->after('nik');
            $table->string('penyakit_alergi')->nullable()->after('umur');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['no_rekam_medis', 'nik', 'tanggal_lahir', 'penyakit_alergi']);
        });
    }
};
