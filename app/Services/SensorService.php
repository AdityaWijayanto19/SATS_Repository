<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\SensorData;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\{Cache, log};
use Carbon\Carbon;

class SensorService
{
    protected Repository $cache;

    public function __construct()
    {
        $this->cache = Cache::store('file');
    }

    public function storeSensorData(array $data): SensorData
    {
        Log::info('Service: mulai store sensor data', $data);
        // Bulk update device status (more efficient than separate update)
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

        // Clear cache untuk latest data device ini
        $this->clearLatestDataCache($data['device_id']);

        return $sensorData;
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
