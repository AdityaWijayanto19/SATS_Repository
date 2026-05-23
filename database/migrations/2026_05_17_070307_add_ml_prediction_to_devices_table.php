<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->text('ml_prediction')->nullable()->after('status');
            $table->string('ml_condition')->nullable()->after('ml_prediction');
            $table->string('ml_risk_level')->nullable()->after('ml_condition');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['ml_prediction', 'ml_condition', 'ml_risk_level']);
        });
    }
};
