<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeviceStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $deviceId,
        public string $status,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('device.' . $this->deviceId)];
    }

    public function broadcastAs(): string
    {
        return 'device.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'device_id' => $this->deviceId,
            'status' => $this->status,
        ];
    }
}
