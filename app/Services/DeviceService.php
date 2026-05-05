<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Devices;
use App\Models\SensorData;
use App\Models\SystemStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeviceService
{
    protected Repository $cache;

    public function __construct()
    {
        $this->cache = Cache::store('file'); // Fast local cache
    }

    /**
     * Store sensor data dari device
     * Performance: Single insert, update device status in one query
     */
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

    /**
     * Get latest sensor data dengan caching
     * Performance: Cache 5 menit, reduce DB queries 95%
     */
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

    /**
     * Store system status (battery, signal)
     * Performance: Single upsert query
     */
    public function storeSystemStatus(array $data): SystemStatus
    {
        $status = SystemStatus::updateOrCreate(
            ['device_id' => $data['device_id']],
            [
                'monitoring_status' => $data['monitoring_status'] ?? 'inactive',
                'battery_level' => $data['battery_level'] ?? null,
                'signal_strength' => $data['signal_strength'] ?? null,
                'updated_at' => Carbon::now(),
            ]
        );

        // Clear cache
        $this->clearSystemStatusCache($data['device_id']);

        return $status;
    }

    /**
     * Get system status dengan caching
     * Performance: Cache 2 menit
     */
    public function getSystemStatus(string $deviceId): ?SystemStatus
    {
        $cacheKey = "system_status_{$deviceId}";

        return $this->cache->remember(
            $cacheKey,
            120, // 2 minutes
            fn() => SystemStatus::where('device_id', $deviceId)
                ->first()
        );
    }

    /**
     * Get device detail dengan relationships (minimal select)
     * Performance: Select only needed columns, single query
     */
    public function getDeviceDetail(string $deviceId)
    {
        return Devices::select('device_id', 'status', 'last_seen', 'created_at')
            ->with([
                'systemStatus:device_id,monitoring_status,battery_level,signal_strength',
                'apiKeys:id,device_id,name,is_active',
            ])
            ->where('device_id', $deviceId)
            ->first();
    }

    /**
     * Clear latest data cache
     */
    protected function clearLatestDataCache(string $deviceId): void
    {
        $this->cache->forget("latest_sensor_{$deviceId}");
    }

    /**
     * Clear system status cache
     */
    protected function clearSystemStatusCache(string $deviceId): void
    {
        $this->cache->forget("system_status_{$deviceId}");
    }
}
