<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\SensorData;
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

        // Bulk update device status
        Devices::where('device_id', $data['device_id'])
            ->update([
                'status' => 'online',
                'last_seen' => Carbon::now(),
            ]);

        // Insert sensor data
        $sensorData = SensorData::create($data);

        Log::info('Service: data berhasil disimpan', [
            'id' => $sensorData->id
        ]);

        $this->clearLatestDataCache($data['device_id']);

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
