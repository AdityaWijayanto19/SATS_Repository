<?php

namespace App\Http\Controllers\Api;

use App\Services\SensorService;
use App\Services\PatientMonitoringService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorDataRequest;
use App\Jobs\ProcessSensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    protected SensorService $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    public function storeSensorData(
        string $deviceId,
        StoreSensorDataRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        $start = microtime(true);

        ProcessSensorData::dispatch($data);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::info('API response time', [
            'duration_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensor data queued successfully',
        ], 202);
    }

    public function getLatestSensorData(string $deviceId): JsonResponse
    {
        $sensorData = $this->sensorService->getLatestSensorData($deviceId);

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

    /**
     * GET /api/device/{deviceId}/prediction
     *
     * Ambil prediksi ML untuk device. Menggunakan cache 2 menit.
     * Dipanggil oleh dashboard frontend untuk menampilkan prediksi.
     */
    public function getPrediction(string $deviceId): JsonResponse
    {
        $mlService = app(PatientMonitoringService::class);
        $prediction = $mlService->getPredictionForDevice($deviceId);

        if (!$prediction) {
            return response()->json([
                'success' => false,
                'message' => 'Data prediksi belum tersedia. Minimal 5 data sensor diperlukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $prediction,
        ], 200);
    }
}
