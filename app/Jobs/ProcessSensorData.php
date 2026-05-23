<?php

namespace App\Jobs;

use App\Models\FailedSensorData;
use App\Services\SensorService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessSensorData implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [10, 60, 300]; // retry at 10s, 60s, 5min
    public $timeout = 30;
    public $maxExceptions = 3;

    public function __construct(
        public array $data
    ) {}

    public function handle(SensorService $sensorService): void
    {
        Log::info('Queue: mulai proses sensor data', [
            'is_batch' => $this->data['batch'] ?? false,
            'count' => isset($this->data['readings']) ? count($this->data['readings']) : 1,
        ]);

        try {
            // Check if batch processing
            if ($this->data['batch'] ?? false) {
                $count = $sensorService->storeSensorDataBatch($this->data['readings']);
                Log::info('Queue: batch sensor data berhasil disimpan', [
                    'count' => $count,
                    'device_id' => $this->data['device_id'],
                ]);
            } else {
                $sensorData = $sensorService->storeSensorData($this->data);
                Log::info('Queue: sensor data berhasil disimpan', [
                    'id' => $sensorData->id,
                    'device_id' => $this->data['device_id'],
                ]);
            }
        } catch (Exception $exception) {
            Log::error('Queue: Error processing sensor data', [
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
        Log::critical('Queue: Sensor data job failed permanently', [
            'device_id' => $this->data['device_id'],
            'is_batch' => $this->data['batch'] ?? false,
            'error' => $exception->getMessage(),
        ]);

        // Store in dead letter queue
        FailedSensorData::create([
            'device_id' => $this->data['device_id'],
            'payload' => $this->data,
            'error_message' => $exception->getMessage(),
            'retry_count' => $this->attempts(),
            'failed_at' => now(),
        ]);

        Log::emergency('Sensor data permanently failed - requires manual intervention', [
            'device_id' => $this->data['device_id'],
        ]);
    }
}
