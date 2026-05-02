<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceAuthController;
use App\Http\Controllers\Api\SensorDataController;

/**
 * Health check endpoint
 */
Route::get('/', function () {
    return response()->json([
        'message' => 'API SATS running',
        'version' => '1.0.0',
        'timestamp' => now(),
    ]);
});

/**
 * Device Authenticate Endpoint (Public, requires API key only)
 * POST /api/device/{device_id}/authenticate
 */
Route::post('/device/{device_id}/authenticate', [DeviceAuthController::class, 'authenticate'])->middleware('apikey');

/**
 * Device Communication Routes (Protected)
 *
 * All endpoints:
 * - Require X-API-Key header
 * - Require device_id in route parameter
 * - Optimized for minimal DB queries & caching
 */
Route::prefix('device')->middleware('apikey')->group(function () {

    /**
     * Get device configuration
     * GET /api/device/{device_id}/config
     */
    Route::get('/{device_id}/config', [DeviceAuthController::class, 'getDeviceConfig']);

    /**
     * Sensor Data Endpoints
     */
    Route::prefix('/{device_id}/sensor-data')->group(function () {
        // Store sensor data dari device
        Route::post('/', [DeviceAuthController::class, 'storeSensorData']);

        // Get latest sensor data (cached)
        Route::get('/latest', [DeviceAuthController::class, 'getLatestSensorData']);
    });

    /**
     * System Status Endpoints
     */
    Route::prefix('/{device_id}/system-status')->group(function () {
        // Store system status (battery, signal)
        Route::post('/', [DeviceAuthController::class, 'storeSystemStatus']);

        // Get system status (cached)
        Route::get('/', [DeviceAuthController::class, 'getSystemStatus']);
    });
});

/**
 * Legacy SensorData endpoint (for backward compatibility)
 * GET /api/sensor-data/{device_id}/latest
 */
Route::get('/sensor-data/{device_id}/latest', [SensorDataController::class, 'latest']);
