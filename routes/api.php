<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceDataController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\InstructionController;

// Device endpoints
Route::post('/device/{device_id}/authenticate', [DeviceDataController::class, 'authenticate'])->middleware('apikey');
Route::get('/device/{device_id}/status', [DeviceDataController::class, 'getDeviceStatus']);

// Device onboarding endpoint (unprotected initially)
Route::post('/device/register', [DeviceDataController::class, 'registerDevice']);

Route::prefix('device')->middleware(['apikey', 'throttle.api', /* 'sign.verify' */])->group(function () {

    Route::get('/{device_id}/config', [DeviceDataController::class, 'getDeviceConfig'])/* ->withoutMiddleware('sign.verify') */;

    Route::prefix('/{device_id}/sensor-data')->group(function () {
        Route::post('', [SensorDataController::class, 'storeSensorData'])->middleware('idempotent');
        Route::post('/batch', [SensorDataController::class, 'storeSensorDataBatch'])->middleware('idempotent');
    });

    Route::prefix('/{device_id}/system-status')->group(function () {
        Route::post('/', [DeviceDataController::class, 'storeSystemStatus'])->middleware('idempotent');
    });
});

Route::middleware(['web'])->group(function () {
    // Monitoring endpoints - accept both session auth (dokter) and API Key (nakes)
    Route::middleware('monitoring.auth')->group(function () {
        Route::get('/device', [DeviceDataController::class, 'listDevices']);
        Route::get('/device/{device_id}/sensor-data/latest', [SensorDataController::class, 'getLatestSensorData']);
        Route::get('/device/{device_id}/sensor-data/history', [SensorDataController::class, 'getSensorDataHistory']);
    });

    // Endpoint prediksi ML (session auth, dipanggil dashboard frontend)
    Route::middleware('auth')->group(function () {
        Route::get('/device/{device}/prediction', [SensorDataController::class, 'getPrediction']);

        Route::prefix('instruction')->group(function () {
            Route::get('', [InstructionController::class, 'index']);
            Route::post('', [InstructionController::class, 'store']);
            Route::post('/report', [InstructionController::class, 'storeReport']);
            Route::patch('/{instruction}', [InstructionController::class, 'update']);
            Route::patch('/{instruction}/complete', [InstructionController::class, 'complete']);
        });
    });
});
