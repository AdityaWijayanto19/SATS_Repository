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
        $this->cache = Cache::store('file'); 
    }

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

    protected function clearSystemStatusCache(string $deviceId): void
    {
        $this->cache->forget("system_status_{$deviceId}");
    }
}
