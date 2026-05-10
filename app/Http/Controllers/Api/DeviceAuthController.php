<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorDataRequest;
use App\Http\Requests\StoreSystemStatusRequest;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceAuthController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Device authenticate dengan API key
     * POST /api/device/authenticate
     *
     * Body:
     * {
     *   "device_id": "DEVICE_01",
     *   "api_key": "your_key_here"
     * }
     */
    public function authenticate(): JsonResponse
    {
        // Middleware sudah validate key, just return success
        return response()->json([
            'success' => true,
            'message' => 'Device authenticated successfully',
            'data' => [
                'device_id' => request('device_id'),
                'authenticated_at' => now(),
            ],
        ], 200);
    }

    /**
     * Device store sensor data
     * POST /api/device/{device_id}/sensor-data
     */
    public function storeSensorData(
        string $deviceId,
        StoreSensorDataRequest $request
    ): JsonResponse {
        // Add device_id dari route param
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        Log::info('Data masuk ke device: ' . $deviceId, $data);

        $sensorData = $this->deviceService->storeSensorData($data);


        return response()->json([
            'success' => true,
            'message' => 'Sensor data stored successfully',
            'data' => [
                'id' => $sensorData->id,
                'device_id' => $sensorData->device_id,
                'created_at' => $sensorData->created_at,
            ],
        ], 201);
    }

    /**
     * Device get latest sensor data
     * GET /api/device/{device_id}/sensor-data/latest
     *
     * Response:
     * {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "heart_rate": 85,
     *     "spo2": 98,
     *     "temperature": 36.5,
     *     "status": "normal",
     *     "created_at": "2026-04-30T10:30:00Z"
     *   }
     * }
     */
    public function getLatestSensorData(string $deviceId): JsonResponse
    {
        $sensorData = $this->deviceService->getLatestSensorData($deviceId);

        if (!$sensorData) {
            return response()->json([
                'success' => false,
                'message' => 'No sensor data found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sensorData,
        ], 200);
    }

    /**
     * Device store system status (battery, signal)
     * POST /api/device/{device_id}/system-status
     *
     * Body:
     * {
     *   "monitoring_status": "active",
     *   "battery_level": 85,
     *   "signal_strength": 75
     * }
     */
    public function storeSystemStatus(
        string $deviceId,
        StoreSystemStatusRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        $status = $this->deviceService->storeSystemStatus($data);

        return response()->json([
            'success' => true,
            'message' => 'System status stored successfully',
            'data' => [
                'device_id' => $status->device_id,
                'battery_level' => $status->battery_level,
                'signal_strength' => $status->signal_strength,
                'updated_at' => $status->updated_at,
            ],
        ], 201);
    }

    /**
     * Device get system status
     * GET /api/device/{device_id}/system-status
     */
    public function getSystemStatus(string $deviceId): JsonResponse
    {
        $status = $this->deviceService->getSystemStatus($deviceId);

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'System status not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $status,
        ], 200);
    }

    /**
     * Device get configuration
     * GET /api/device/{device_id}/config
     *
     * Device bisa baca config seperti:
     * - Sampling interval (berapa detik kirim data?)
     * - Alert thresholds (kapan alert?)
     * - Enabled sensors (sensor mana yang aktif?)
     */
    public function getDeviceConfig(string $deviceId): JsonResponse
    {
        $device = $this->deviceService->getDeviceDetail($deviceId);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_id,
                'sampling_interval' => 5, // seconds
                'enabled_sensors' => ['heart_rate', 'spo2', 'temperature'],
                'alert_thresholds' => [
                    'heart_rate' => ['min' => 40, 'max' => 140],
                    'spo2' => ['min' => 90, 'max' => 100],
                    'temperature' => ['min' => 35, 'max' => 39],
                ],
                'status' => $device->status,
                'battery_level' => $device->systemStatus?->battery_level,
            ],
        ], 200);
    }

    /**
     * Get sensor data history for charts
     * GET /api/device/{device_id}/sensor-data/history?minutes=10
     */
    public function getSensorDataHistory(string $deviceId): JsonResponse
    {
        $minutes = (int) request('minutes', 10);
        $from = now()->subMinutes($minutes);

        $data = \App\Models\SensorData::where('device_id', $deviceId)
            ->where('created_at', '>=', $from)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $data->map(fn($d) => $d->created_at->format('H:i')),
                'heart_rate' => $data->pluck('heart_rate'),
                'spo2' => $data->pluck('spo2'),
                'temperature' => $data->pluck('temperature'),
                'status' => $data->pluck('status'),
            ],
        ], 200);
    }
}
