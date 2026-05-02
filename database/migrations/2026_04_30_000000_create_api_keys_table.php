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
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();

            $table->string('device_id', 50);
            $table->string('key_hash')->unique(); // Hashed key (secure)
            $table->string('name')->comment('Friendly name, e.g. Device SATS #1');

            $table->boolean('is_active')->default(true);
            $table->integer('rate_limit_per_minute')->default(60)->comment('Throttle requests');

            $table->timestamp('last_used')->nullable()->comment('Track device activity');
            $table->string('last_used_ip')->nullable();
            $table->timestamp('expires_at')->nullable()->comment('Auto-expire key');

            $table->timestamps();

            // Indexes untuk performance
            $table->index('device_id');
            $table->index('is_active');
            $table->index('created_at');
            $table->index('last_used');

            // Foreign key
            $table->foreign('device_id')
                ->references('device_id')
                ->on('devices')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
