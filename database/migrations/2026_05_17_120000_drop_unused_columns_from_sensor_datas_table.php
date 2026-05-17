<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sensor_datas', function (Blueprint $table) {
            $table->dropColumn([
                'systolic_bp',
                'diastolic_bp',
                'respiratory_rate',
                'prediction',
                'ml_prediction',
                'ml_condition',
                'ml_risk_level',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('sensor_datas', function (Blueprint $table) {
            $table->integer('systolic_bp')->nullable()->after('temperature');
            $table->integer('diastolic_bp')->nullable()->after('systolic_bp');
            $table->integer('respiratory_rate')->nullable()->after('diastolic_bp');
            $table->string('prediction')->nullable()->after('status');
            $table->text('ml_prediction')->nullable()->after('prediction');
            $table->string('ml_condition')->nullable()->after('ml_prediction');
            $table->string('ml_risk_level')->nullable()->after('ml_condition');
        });
    }
};
