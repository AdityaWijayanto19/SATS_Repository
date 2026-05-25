<?php

namespace App\Events;

use App\Models\Devices;
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

    public function __construct(
        public string $deviceId,
        public SensorData $sensorData,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('device.' . $this->deviceId)];
    }

    public function broadcastAs(): string
    {
        return 'sensor.data.received';
    }

    public function broadcastWith(): array
    {
        // Ambil history 10 menit terakhir untuk grafik
        $from = now()->subMinutes(10);
        $history = SensorData::where('device_id', $this->deviceId)
            ->where('created_at', '>=', $from)
            ->orderBy('created_at', 'asc')
            ->get();

        // Ambil data ML dari tabel devices
        $device = Devices::where('device_id', $this->deviceId)->first();

        return [
            'device_id' => $this->deviceId,
            'latest' => [
                'heart_rate' => $this->sensorData->heart_rate,
                'spo2' => $this->sensorData->spo2,
                'temperature' => $this->sensorData->temperature,
                'status' => $this->sensorData->status,
                'created_at' => $this->sensorData->created_at->setTimezone('Asia/Jakarta')->format('H:i'),
                'ml_prediction' => $device?->ml_prediction,
                'ml_condition' => $device?->ml_condition,
                'ml_risk_level' => $device?->ml_risk_level,
                'ml_probabilities' => json_decode($device?->ml_probabilities, true),
                'ml_predicted_at' => $device?->ml_predicted_at?->setTimezone('Asia/Jakarta')->format('H:i'),
            ],
            'history' => [
                'labels' => $history->map(fn($d) => $d->created_at->setTimezone('Asia/Jakarta')->format('H:i')),
                'heart_rate' => $history->pluck('heart_rate'),
                'spo2' => $history->pluck('spo2'),
                'temperature' => $history->pluck('temperature'),
            ],
        ];
    }
}
