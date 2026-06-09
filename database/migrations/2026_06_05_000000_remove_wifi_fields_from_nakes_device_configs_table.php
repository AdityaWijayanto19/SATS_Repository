<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nakes_device_configs', function (Blueprint $table) {
            $table->dropColumn(['wifi_name', 'wifi_password']);
        });
    }

    public function down(): void
    {
        Schema::table('nakes_device_configs', function (Blueprint $table) {
            $table->string('wifi_name')->after('device_id');
            $table->string('wifi_password')->after('wifi_name');
        });
    }
};
