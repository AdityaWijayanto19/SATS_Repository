<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceDataController;
use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\InstructionController;


Route::post('/device/{device_id}/authenticate', [DeviceDataController::class, 'authenticate'])->middleware('apikey');
Route::get('/device/{device_id}/status', [DeviceDataController::class, 'getDeviceStatus']);

Route::prefix('device')->middleware('apikey')->group(function () {

    Route::get('/{device_id}/config', [DeviceDataController::class, 'getDeviceConfig']);

    Route::prefix('/{device_id}/sensor-data')->group(function () {
        Route::post('', [SensorDataController::class, 'storeSensorData']);
    });

    Route::prefix('/{device_id}/system-status')->group(function () {
        Route::post('/', [DeviceDataController::class, 'storeSystemStatus']);
        Route::get('/', [DeviceDataController::class, 'getSystemStatus']);
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('instruction')->group(function () {
        Route::get('', [InstructionController::class, 'index']);
        Route::post('', [InstructionController::class, 'store']);
        Route::post('/report', [InstructionController::class, 'storeReport']);
        Route::patch('/{instruction}', [InstructionController::class, 'update']);
        Route::patch('/{instruction}/complete', [InstructionController::class, 'complete']);
    });

    // Jangan lupa route sensor data juga diganti middlewarenya ke 'auth'
    Route::get('/device/{device}/sensor-data/history', [SensorDataController::class, 'getSensorDataHistory']);
    Route::get('/device/{device}/sensor-data/latest', [SensorDataController::class, 'getLatestSensorData']);

    // Endpoint prediksi ML (session auth, dipanggil dashboard frontend)
    Route::get('/device/{device}/prediction', [SensorDataController::class, 'getPrediction']);
});
