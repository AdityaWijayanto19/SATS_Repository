<?php

namespace App\Console\Commands;

use App\Events\DeviceStatusChanged;
use App\Events\DeviceStatusChangedGlobal;
use App\Models\ActivityLog;
use App\Models\Devices;
use App\Models\MonitoringSession;
use App\Services\MonitoringSessionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkStaleDevicesOffline extends Command
{
    protected $signature = 'devices:mark-stale-offline
                            {--timeout=30 : Detik tanpa data sebelum device dianggap offline}';

    protected $description = 'Tandai device sebagai offline jika tidak mengirim data dalam periode tertentu, dan finalisasi monitoring session.';

    public function handle(MonitoringSessionService $sessionService): int
    {
        $timeout = (int) $this->option('timeout');
        $cutoff = Carbon::now()->subSeconds($timeout);

        // Cari device yang statusnya online tapi last_seen sudah lewat timeout
        $staleDevices = Devices::where('status', 'online')
            ->where('last_seen', '<', $cutoff)
            ->get();

        if ($staleDevices->isEmpty()) {
            $this->info("Tidak ada device stale (timeout: {$timeout}s).");
            return self::SUCCESS;
        }

        foreach ($staleDevices as $device) {
            $secondsAgo = abs(Carbon::now()->diffInSeconds($device->last_seen));

            // Update status ke offline
            $device->update(['status' => 'offline']);

            // Finalisasi monitoring session yang masih active
            $activeSession = $sessionService->getActiveSession($device->device_id);
            if ($activeSession) {
                $sessionService->finalizeSession($activeSession->id);
                Log::info("Auto-finalized session {$activeSession->medical_record_number} for stale device {$device->device_id}");
            }

            // Broadcast status change
            broadcast(new DeviceStatusChanged($device->device_id, 'offline'));
            broadcast(new DeviceStatusChangedGlobal($device->device_id, 'offline'));

            // Activity log — hanya jika log terakhir BUKAN device.offline (hindari duplikat)
            $lastLog = ActivityLog::where('device_id', $device->device_id)
                ->orderByDesc('created_at')
                ->first();

            if (!$lastLog || $lastLog->type !== 'device.offline') {
                ActivityLog::log(
                    'device.offline',
                    "Perangkat {$device->device_id} otomatis dinonaktifkan (tidak aktif {$secondsAgo} detik)",
                    'System',
                    'system',
                    $device->device_id
                );
            }

            $this->line("✓ <comment>{$device->device_id}</comment> → offline (last seen {$secondsAgo}s ago)");
            Log::info("Device {$device->device_id} marked offline by scheduler (last_seen: {$device->last_seen}, timeout: {$timeout}s)");
        }

        $this->info("Done. {$staleDevices->count()} device(s) marked as offline.");
        return self::SUCCESS;
    }
}
