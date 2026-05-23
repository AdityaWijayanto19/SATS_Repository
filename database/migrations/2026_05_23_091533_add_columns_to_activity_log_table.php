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
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('type', 50)->after('id');
            $table->string('user_name', 255)->nullable()->after('message');
            $table->string('user_role', 20)->nullable()->after('user_name');
            $table->string('icon', 20)->after('user_role');
            $table->string('device_id', 50)->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn(['type', 'user_name', 'user_role', 'icon', 'device_id']);
        });
    }
};
