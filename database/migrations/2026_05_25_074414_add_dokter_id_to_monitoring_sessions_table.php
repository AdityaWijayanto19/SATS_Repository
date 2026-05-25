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
        Schema::table('monitoring_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('dokter_id')->nullable()->after('created_by');
            $table->foreign('dokter_id')->references('id')->on('users')->onDelete('set null');
            $table->index('dokter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monitoring_sessions', function (Blueprint $table) {
            $table->dropForeign(['dokter_id']);
            $table->dropIndex(['dokter_id']);
            $table->dropColumn('dokter_id');
        });
    }
};
