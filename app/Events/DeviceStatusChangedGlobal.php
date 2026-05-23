<?php

namespace App\Events;

use App\Models\Devices;
use App\Models\NakesDeviceConfig;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusChangedGlobal implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $deviceId,
        public string $status,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('superadmin.dashboard')];
    }

    public function broadcastAs(): string
    {
        return 'device.status.changed.global';
    }

    public function broadcastWith(): array
    {
        $device = Devices::where('device_id', $this->deviceId)->first();
        $latestSensor = $device?->sensorData()->latest('created_at')->first();
        $nakesConfig = NakesDeviceConfig::where('device_id', $this->deviceId)->first();

        // Ambil semua dokter yang memantau dari pivot table
        $dokterNames = $device?->monitoredByDokters()
            ->pluck('name')
            ->toArray() ?? [];
        $dokterName = !empty($dokterNames) ? implode(', ', $dokterNames) : '-';

        return [
            'device_id' => $this->deviceId,
            'status' => $this->status,
            'device_data' => $this->status === 'online' ? [
                'device_id' => $this->deviceId,
                'status' => $latestSensor?->status ?? 'normal',
                'heart_rate' => $latestSensor?->heart_rate,
                'spo2' => $latestSensor?->spo2,
                'temperature' => $latestSensor?->temperature,
                'nakes_name' => $nakesConfig?->user?->name ?? '-',
                'dokter_name' => $dokterName,
                'updated_at' => $latestSensor?->created_at?->setTimezone('Asia/Jakarta')->format('H:i:s'),
            ] : null,
        ];
    }
}
