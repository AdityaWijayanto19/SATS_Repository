<?php

namespace App\Events;

use App\Models\SensorData;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public SensorData $sensorData) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('device.' . $this->sensorData->device_id)];
    }

    public function broadcastAs(): string
    {
        return 'sensor.received';
    }

    /**
     * Determine what data to broadcast
     */
    public function broadcastWith(): array
    {
        return [
            'sensor' => [
                'id' => $this->sensorData->id,
                'device_id' => $this->sensorData->device_id,
                'heart_rate' => $this->sensorData->heart_rate,
                'spo2' => $this->sensorData->spo2,
                'temperature' => $this->sensorData->temperature,
                'status' => $this->sensorData->status,
                'prediction' => $this->sensorData->prediction,
                'created_at' => $this->sensorData->created_at->toIso8601String(),
                'timestamp' => $this->sensorData->created_at->format('H:i:s'),
            ],
        ];
    }
}
