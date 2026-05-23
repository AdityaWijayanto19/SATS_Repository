<?php

namespace App\Services;

use App\Events\SensorDataReceived;
use App\Models\Devices;
use App\Models\SensorData;
use App\Services\PatientMonitoringService;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\{Cache, Log};
use Carbon\Carbon;

class SensorService
{
    protected Repository $cache;

    public function __construct()
    {
        $this->cache = Cache::store('redis');
    }

    /**
     * Store single sensor reading (DB only — no broadcast)
     */
    public function storeSensorData(array $data): SensorData
    {
        Log::info('Service: mulai store sensor data', $data);

        // Update last_seen saja (status diatur oleh nakes via dashboard)
        Devices::where('device_id', $data['device_id'])
            ->update([
                'last_seen' => Carbon::now(),
            ]);

        // Insert sensor data
        $sensorData = SensorData::create($data);

        Log::info('Service: data berhasil disimpan', [
            'id' => $sensorData->id
        ]);

        $this->clearLatestDataCache($data['device_id']);

        // Broadcast data baru ke semua dashboard yang terhubung
        try {
            broadcast(new SensorDataReceived($data['device_id'], $sensorData));
        } catch (\Exception $e) {
            Log::warning('Broadcast sensor data gagal (Reverb mungkin tidak running)', [
                'device_id' => $data['device_id'],
                'error' => $e->getMessage(),
            ]);
        }

        // Trigger prediksi ML di background (tidak blocking response)
        // Hanya di-trigger setiap 5 data baru untuk menghindari API rate limit
        $this->triggerPredictionIfNeeded($data['device_id']);

        return $sensorData;
    }

    /**
     * Store batch sensor readings efficiently (DB only — no broadcast)
     */
    public function storeSensorDataBatch(array $readings): int
    {
        if (empty($readings)) {
            return 0;
        }

        Log::info('Service: mulai store batch sensor data', [
            'count' => count($readings),
            'device_id' => $readings[0]['device_id'] ?? null,
        ]);

        $deviceId = $readings[0]['device_id'] ?? null;

        // Bulk update device status once
        Devices::where('device_id', $deviceId)
            ->update([
                'status' => 'online',
                'last_seen' => Carbon::now(),
            ]);

        // Bulk insert all readings in single query
        $inserted = SensorData::insert($readings);

        Log::info('Service: batch data berhasil disimpan', [
            'count' => count($readings),
            'device_id' => $deviceId,
        ]);

        // Clear cache untuk latest data
        $this->clearLatestDataCache($deviceId);

        return $inserted ? count($readings) : 0;
    }

    /**
     * Trigger prediksi ML jika sudah cukup data baru sejak prediksi terakhir.
     * Cek tabel devices untuk ml_prediction, hitung data baru dari sensor_datas.
     */
    protected function triggerPredictionIfNeeded(string $deviceId): void
    {
        try {
            $device = Devices::where('device_id', $deviceId)->first();

            // Sudah ada prediksi → cek apakah ada 5 data baru sejak prediksi terakhir
            if ($device && $device->ml_prediction) {
                // Gunakan ml_predicted_at (bukan updated_at yang berubah setiap last_seen update)
                $since = $device->ml_predicted_at ?? $device->updated_at;
                $newDataCount = SensorData::where('device_id', $deviceId)
                    ->where('created_at', '>', $since)
                    ->count();

                Log::info('ML trigger check', [
                    'device_id' => $deviceId,
                    'new_data_count' => $newDataCount,
                    'has_prediction' => true,
                ]);

                if ($newDataCount >= 5) {
                    Log::info('ML trigger: running prediction', ['device_id' => $deviceId]);
                    $this->runPrediction($deviceId);
                }
                return;
            }

            // Belum ada prediksi → cek total data
            $totalData = SensorData::where('device_id', $deviceId)->count();

            Log::info('ML trigger check', [
                'device_id' => $deviceId,
                'total_data' => $totalData,
                'has_prediction' => false,
            ]);

            if ($totalData >= 5) {
                Log::info('ML trigger: running prediction (first time)', ['device_id' => $deviceId]);
                $this->runPrediction($deviceId);
            }
        } catch (\Exception $e) {
            Log::error('Trigger prediction error', [
                'device_id' => $deviceId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Jalankan prediksi ML dan simpan hasilnya ke data sensor terbaru.
     */
    protected function runPrediction(string $deviceId): void
    {
        $mlService = app(PatientMonitoringService::class);
        $prediction = $mlService->getPredictionForDevice($deviceId);

        if (!$prediction) {
            Log::warning('ML prediction returned null', ['device_id' => $deviceId]);
            return;
        }

        // Simpan prediksi di tabel devices (selalu tersedia, tidak hilang saat data baru masuk)
        Devices::where('device_id', $deviceId)->update([
            'ml_prediction'     => $prediction['prediction'],
            'ml_condition'      => $prediction['condition'],
            'ml_risk_level'     => $prediction['risk_level'],
            'ml_probabilities'  => json_encode([
                'membaik'  => $prediction['membaik'] ?? null,
                'stabil'   => $prediction['stabil'] ?? null,
                'memburuk' => $prediction['memburuk'] ?? null,
            ]),
            'ml_predicted_at'   => Carbon::now(),
        ]);

        // Clear cache supaya dashboard dapat data terbaru
        $this->clearLatestDataCache($deviceId);
        Cache::forget("ml_prediction_{$deviceId}");

        // Broadcast ulang agar dashboard mendapat data ML terbaru
        $latestSensor = SensorData::where('device_id', $deviceId)
            ->latest('created_at')
            ->first();

        if ($latestSensor) {
            try {
                broadcast(new SensorDataReceived($deviceId, $latestSensor));
            } catch (\Exception $e) {
                Log::warning('Broadcast ML prediction gagal (Reverb mungkin tidak running)', [
                    'device_id' => $deviceId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('ML prediction stored', [
            'device_id' => $deviceId,
            'condition' => $prediction['condition'],
            'risk_level' => $prediction['risk_level'],
        ]);
    }

    public function getLatestSensorData(string $deviceId): ?SensorData
    {
        $cacheKey = "latest_sensor_{$deviceId}";

        return $this->cache->remember(
            $cacheKey,
            300, // 5 minutes
            fn() => SensorData::onlyVitals()
                ->where('device_id', $deviceId)
                ->latest('created_at')
                ->first()
        );
    }

    protected function clearLatestDataCache(string $deviceId): void
    {
        $this->cache->forget("latest_sensor_{$deviceId}");
    }
}
