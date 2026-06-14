<?php

namespace App\Http\Controllers\Api;

use App\Events\DeviceStatusChanged;
use App\Events\DeviceStatusChangedGlobal;
use App\Events\SensorDataReceived;
use App\Models\ActivityLog;
use App\Models\Devices;
use App\Models\NakesDeviceConfig;
use App\Models\SensorData;
use App\Services\MonitoringSessionService;
use App\Services\SensorService;
use App\Services\PatientMonitoringService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSensorDataRequest;
use App\Http\Requests\StoreSensorDataBatchRequest;
use App\Jobs\ProcessSensorData;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    protected SensorService $sensorService;

    public function __construct(SensorService $sensorService)
    {
        $this->sensorService = $sensorService;
    }

    /**
     * Build a dry/fake SensorData model from validated array for immediate broadcast.
     * This avoids waiting for the DB write to trigger the websocket event.
     */
    protected function buildDryModel(array $data): SensorData
    {
        $model = new SensorData();
        $model->id = 0; // Temporary ID — real ID assigned after DB write
        $model->device_id = $data['device_id'];
        $model->heart_rate = $data['heart_rate'] ?? null;
        $model->spo2 = $data['spo2'] ?? null;
        $model->temperature = $data['temperature'] ?? null;
        $model->status = $data['status'] ?? 'unknown';
        $model->prediction = $data['prediction'] ?? null;
        $model->created_at = now();
        $model->updated_at = now();

        return $model;
    }

    /**
     * Auto-reactivate device jika status=offline saat data masuk.
     * Dipanggil di request context (bukan queued job) agar broadcast sampai ke browser.
     */
    protected function reactivateIfNeeded(string $deviceId): void
    {
        try {
            $device = Devices::where('device_id', $deviceId)->first();
            if (!$device) {
                return;
            }

            $sessionService = app(MonitoringSessionService::class);
            $activeSession = $sessionService->getActiveSession($deviceId);
            $needsSession = !$activeSession;

            // Case 1: Device offline → set online + broadcast + session
            if ($device->status === 'offline') {
                $device->update(['status' => 'online', 'last_seen' => Carbon::now()]);

                if ($needsSession) {
                    $nakesConfig = NakesDeviceConfig::where('device_id', $deviceId)->first();
                    $userId = $nakesConfig?->user_id ?? 1;
                    $sessionService->createSession($deviceId, $userId);
                }

                broadcast(new DeviceStatusChanged($deviceId, 'online'));
                broadcast(new DeviceStatusChangedGlobal($deviceId, 'online'));
                ActivityLog::log('device.online', "Perangkat {$deviceId} aktif kembali (data diterima)", 'System', 'system', $deviceId);
                Log::info("Device {$deviceId} auto-reactivated (was offline)");
                return;
            }

            // Case 2: Device online tapi tidak ada session aktif → buat session
            if ($needsSession) {
                $nakesConfig = NakesDeviceConfig::where('device_id', $deviceId)->first();
                $userId = $nakesConfig?->user_id ?? 1;
                $sessionService->createSession($deviceId, $userId);
                broadcast(new DeviceStatusChanged($deviceId, 'online'));
                broadcast(new DeviceStatusChangedGlobal($deviceId, 'online'));
                Log::info("Session created for already-online device {$deviceId}");
            }
        } catch (\Exception $e) {
            Log::warning("reactivateIfNeeded error for {$deviceId}: " . $e->getMessage());
        }
    }

    public function storeSensorData(
        string $deviceId,
        StoreSensorDataRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        $start = microtime(true);

        Log::info('Controller storeSensorData called', ['device_id' => $deviceId, 'status' => Devices::where('device_id', $deviceId)->first()?->status]);

        // 0. Auto-reactivate: jika device offline, set online & buat session
        $this->reactivateIfNeeded($deviceId);

        // Update last_seen langsung di controller (jangan tunggu queued job)
        Devices::where('device_id', $deviceId)->update(['last_seen' => Carbon::now()]);

        // 1. IMMEDIATE real-time broadcast via WebSocket (near-zero latency)
        //    Uses a dry model instance so the event fires NOW, not after DB write.
        $dryModel = $this->buildDryModel($data);
        SensorDataReceived::dispatch($deviceId, $dryModel);

        // 2. Dispatch background DB write job (non-blocking)
        ProcessSensorData::dispatch($data);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('device-audit')->info('Sensor data received', [
            'device_id' => $deviceId,
            'api_key_id' => $request->attributes->get('authenticated_api_key')?->id,
            'ip' => $request->ip(),
            'status' => 'queued',
            'response_time_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sensor data queued successfully',
        ], 202);
    }

    /**
     * Batch insert multiple sensor readings
     * More efficient for high-frequency data
     */
    public function storeSensorDataBatch(
        string $deviceId,
        StoreSensorDataBatchRequest $request
    ): JsonResponse {
        $readings = $request->validated()['readings'];

        // Add device_id to each reading
        foreach ($readings as &$reading) {
            $reading['device_id'] = $deviceId;
        }

        $start = microtime(true);

        // 1. IMMEDIATE real-time broadcast for each reading
        foreach ($readings as $reading) {
            $dryModel = $this->buildDryModel($reading);
            SensorDataReceived::dispatch($deviceId, $dryModel);
        }

        // 2. Dispatch single background DB job for batch processing
        ProcessSensorData::dispatch([
            'batch' => true,
            'readings' => $readings,
            'device_id' => $deviceId,
        ]);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('device-audit')->info('Batch sensor data received', [
            'device_id' => $deviceId,
            'reading_count' => count($readings),
            'api_key_id' => $request->attributes->get('authenticated_api_key')?->id,
            'ip' => $request->ip(),
            'response_time_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Batch sensor data queued successfully',
            'count' => count($readings),
        ], 202);
    }

    public function getLatestSensorData(string $deviceId): JsonResponse
    {
        // Authentication is fully handled by the middleware pipeline (AuthenticateApiKey).
        // No need for in-controller Hash::check loops.

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
        // Authentication is fully handled by the middleware pipeline.

        $minutes = (int) request('minutes', 60);
        $page = (int) request('page', 1);
        $perPage = (int) request('per_page', 100);

        // Limit maximum query range
        if ($minutes > 1440) { // 24 hours max
            $minutes = 1440;
        }

        if ($perPage > 500) { // Max 500 per page
            $perPage = 500;
        }

        $from = now()->subMinutes($minutes);

        $query = SensorData::where('device_id', $deviceId)
            ->where('created_at', '>=', $from)
            ->orderBy('created_at', 'asc');

        $total = $query->count();
        $data = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        Log::channel('device-audit')->info('Sensor history requested', [
            'device_id' => $deviceId,
            'minutes' => $minutes,
            'page' => $page,
            'per_page' => $perPage,
            'total_records' => $total,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'labels' => $data->map(fn($d) => $d->created_at->setTimezone('Asia/Jakarta')->format('H:i')),
                'heart_rate' => $data->pluck('heart_rate'),
                'spo2' => $data->pluck('spo2'),
                'temperature' => $data->pluck('temperature'),
                'status' => $data->pluck('status'),
            ],
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage),
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
