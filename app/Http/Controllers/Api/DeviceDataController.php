<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSystemStatusRequest;
use App\Jobs\ProcessDeviceData;
use App\Models\Devices;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class DeviceDataController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function authenticate(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Device authenticated successfully',
            'data' => [
                'device_id' => request('device_id'),
                'authenticated_at' => now(),
            ],
        ], 200);
    }

    public function storeSystemStatus(
        string $deviceId,
        StoreSystemStatusRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        $start = microtime(true);

        ProcessDeviceData::dispatch($data);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::info('API response time', [
            'duration_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'System status stored successfully',
        ], 202);
    }

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

    public function getDeviceStatus(string $deviceId): JsonResponse
    {
        $device = Devices::where('device_id', $deviceId)->first();

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
                'status' => $device->status,
            ],
        ], 200);
    }
}
