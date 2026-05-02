<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorDataRequest;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;

class SensorDataController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * Store sensor data
     * POST /api/sensor-data
     */
    public function store(StoreSensorDataRequest $request): JsonResponse
    {
        $data = $this->deviceService->storeSensorData($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Sensor data saved',
            'data' => [
                'id' => $data->id,
                'device_id' => $data->device_id,
                'created_at' => $data->created_at,
            ]
        ], 201);
    }

    /**
     * Get latest sensor data (with caching)
     * GET /api/sensor-data/{device_id}/latest
     */
    public function latest(string $deviceId): JsonResponse
    {
        $data = $this->deviceService->getLatestSensorData($deviceId);

        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No sensor data found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }
}
