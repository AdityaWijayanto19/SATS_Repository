<?php

/**
 * Migration: Tambah kolom vital signs lengkap + hasil ML ke sensor_datas.
 *
 * Kolom baru:
 * - systolic_bp: tekanan darah sistolik (dibutuhkan ML API)
 * - diastolic_bp: tekanan darah diastolik (dibutuhkan ML API)
 * - respiratory_rate: laju pernapasan (dibutuhkan ML API)
 * - ml_prediction: teks prediksi dari ML (e.g. "Pasien akan MEMBURUK...")
 * - ml_condition: kondisi dari ML (NORMAL/WARNING/CRITICAL)
 * - ml_risk_level: risk level dari ML (Low/Medium/High Risk)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_datas', function (Blueprint $table) {
            // Vital signs tambahan (dibutuhkan oleh ML API)
            $table->integer('systolic_bp')->nullable()->after('temperature');
            $table->integer('diastolic_bp')->nullable()->after('systolic_bp');
            $table->integer('respiratory_rate')->nullable()->after('diastolic_bp');

            // Hasil prediksi ML
            $table->text('ml_prediction')->nullable()->after('prediction');
            $table->string('ml_condition')->nullable()->after('ml_prediction');
            $table->string('ml_risk_level')->nullable()->after('ml_condition');
        });
    }

    public function down(): void
    {
        Schema::table('sensor_datas', function (Blueprint $table) {
            $table->dropColumn([
                'systolic_bp',
                'diastolic_bp',
                'respiratory_rate',
                'ml_prediction',
                'ml_condition',
                'ml_risk_level',
            ]);
        });
    }
};
