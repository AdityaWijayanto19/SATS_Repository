<?php

namespace App\Jobs;

use App\Models\FailedSensorData;
use App\Services\DeviceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessDeviceData implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 60, 300]; // retry at 10s, 60s, 5min
    public $timeout = 30;

    public function __construct(
        public array $data
    ) {}

    public function handle(DeviceService $deviceService): void
    {
        Log::info('Queue: mulai proses device data', $this->data);

        try {
            $deviceData = $deviceService->storeSystemStatus($this->data);

            Log::info('Queue: device data berhasil disimpan', [
                'id' => $deviceData->id,
                'device_id' => $this->data['device_id'],
            ]);
        } catch (Exception $exception) {
            Log::error('Queue: Error processing device data', [
                'device_id' => $this->data['device_id'],
                'attempt' => $this->attempts(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    /**
     * Called when job fails after all retries
     */
    public function failed(Exception $exception): void
    {
        Log::critical('Queue: Device data job failed permanently', [
            'device_id' => $this->data['device_id'],
            'error' => $exception->getMessage(),
            'payload' => $this->data,
        ]);

        // Store in dead letter queue
        FailedSensorData::create([
            'device_id' => $this->data['device_id'],
            'payload' => $this->data,
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->attempts(),
            'failed_at' => now(),
        ]);

        Log::emergency('Device data permanently failed - requires manual intervention', [
            'device_id' => $this->data['device_id'],
        ]);
    }
}
