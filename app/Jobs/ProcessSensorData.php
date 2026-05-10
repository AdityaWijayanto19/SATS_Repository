<?php

namespace App\Jobs;

use App\Services\DeviceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessSensorData implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $data
    ) {}

    public function handle(DeviceService $deviceService): void
    {
        Log::info('Queue: mulai proses sensor data', $this->data);

        $sensorData = $deviceService->storeSensorData($this->data);

        Log::info('Queue: sensor data berhasil disimpan', [
            'id' => $sensorData->id,
        ]);
    }
}
