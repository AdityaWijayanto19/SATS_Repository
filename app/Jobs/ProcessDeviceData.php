<?php

namespace App\Jobs;

use App\Services\DeviceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessDeviceData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
     public function __construct(
        public array $data
    ) {}

    /**
     * Execute the job.
     */
     public function handle(DeviceService $deviceService): void
    {
        Log::info('Queue: mulai proses device data', $this->data);

        $deviceData = $deviceService->storeSystemStatus($this->data);

        Log::info('Queue: device data berhasil disimpan', [
            'id' => $deviceData->id,
        ]);
    }
}
