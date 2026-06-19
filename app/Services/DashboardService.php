<?php

namespace App\Services;

use App\Events\DeviceStatusChanged;
use App\Events\DeviceStatusChangedGlobal;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\DeviceMonitoring;
use App\Models\Devices;
use App\Models\MonitoringSession;
use App\Models\NakesDeviceConfig;
use App\Models\SensorData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardService
{
    /**
     * Data dashboard berdasarkan role user.
     */
    public function getDashboardData(): array
    {
        $user = Auth::user();

        if ($user->role === 'nakes') {
            $hasConfig = NakesDeviceConfig::where('user_id', $user->id)->exists();
            if (!$hasConfig) {
                return ['view' => 'pages.nakes.setup-device', 'data' => []];
            }
        }

        $devices = $this->getDevicesWithLatestData();

        if ($user->role === 'superadmin') {
            return ['view' => 'pages.superadmin.dashboard', 'data' => array_merge(
                compact('devices'),
                $this->getSuperadminStats()
            )];
        }

        return ['view' => "pages.{$user->role}.dashboard", 'data' => compact('devices')];
    }

    /**
     * Data devices dengan active session (untuk halaman input data pasien).
     */
    public function getDevicesWithActiveSession()
    {
        return Devices::all()->map(function ($device) {
            $activeSession = MonitoringSession::where('device_id', $device->device_id)
                ->where('status', 'active')
                ->with('patient')
                ->latest('started_at')
                ->first();

            return [
                'device_id' => $device->device_id,
                'status' => $device->status,
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'medical_record_number' => $activeSession->medical_record_number,
                    'patient_name' => $activeSession->patient?->nama ?? '-',
                    'started_at' => $activeSession->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                ] : null,
            ];
        });
    }

    /**
     * Toggle status perangkat (online/offline) dari dashboard nakes.
     */
    public function toggleDeviceStatus(string $status, NakesDeviceConfig $config): array
    {
        Devices::where('device_id', $config->device_id)->update(['status' => $status]);

        broadcast(new DeviceStatusChanged($config->device_id, $status));
        broadcast(new DeviceStatusChangedGlobal($config->device_id, $status));

        $user = Auth::user();
        $logType = $status === 'online' ? 'device.online' : 'device.offline';
        $logMsg = $status === 'online'
            ? "{$user->name} mengaktifkan perangkat"
            : "{$user->name} menonaktifkan perangkat";
        ActivityLog::log($logType, $logMsg, $user->name, $user->role, $config->device_id);

        return ['success' => true, 'status' => $status];
    }

    /**
     * Simpan konfigurasi perangkat nakes.
     */
    public function saveDeviceConfig(array $data): array
    {
        $apiKeys = ApiKey::where('is_active', true)->get();
        $matchedKey = null;

        foreach ($apiKeys as $apiKey) {
            if (Hash::check($data['api_key'], $apiKey->key_hash)) {
                $matchedKey = $apiKey;
                break;
            }
        }

        if (!$matchedKey) {
            return ['success' => false, 'error' => 'api_key', 'message' => 'API Key tidak valid atau tidak aktif.'];
        }

        NakesDeviceConfig::create([
            'user_id' => Auth::id(),
            'device_id' => $matchedKey->device_id,
        ]);

        return ['success' => true];
    }

    /**
     * Dokter memilih device untuk dipantau.
     */
    public function selectDevice(string $deviceId): void
    {
        $exists = DeviceMonitoring::where('device_id', $deviceId)
            ->where('dokter_id', Auth::id())
            ->exists();

        if (!$exists) {
            DeviceMonitoring::create([
                'device_id' => $deviceId,
                'dokter_id' => Auth::id(),
            ]);

            $user = Auth::user();
            ActivityLog::log('monitoring.started', "Dokter {$user->name} mulai memantau perangkat", $user->name, $user->role, $deviceId);
        }

        $device = Devices::where('device_id', $deviceId)->first();
        broadcast(new DeviceStatusChangedGlobal($deviceId, $device->status));
    }

    /**
     * Dokter berhenti memantau device tertentu.
     */
    public function deselectDevice(string $deviceId): void
    {
        DeviceMonitoring::where('device_id', $deviceId)
            ->where('dokter_id', Auth::id())
            ->delete();

        $device = Devices::where('device_id', $deviceId)->first();
        broadcast(new DeviceStatusChangedGlobal($deviceId, $device->status));

        $user = Auth::user();
        ActivityLog::log('monitoring.stopped', "Dokter {$user->name} berhenti memantau perangkat", $user->name, $user->role, $deviceId);
    }

    /**
     * Dokter berhenti memantau semua device (saat logout).
     */
    public function deselectAllDevices(): void
    {
        $deviceIds = DeviceMonitoring::where('dokter_id', Auth::id())->pluck('device_id');

        DeviceMonitoring::where('dokter_id', Auth::id())->delete();

        foreach ($deviceIds as $deviceId) {
            $device = Devices::where('device_id', $deviceId)->first();
            if ($device) {
                broadcast(new DeviceStatusChangedGlobal($deviceId, $device->status));
            }
        }
    }

    /**
     * Hapus konfigurasi perangkat nakes.
     */
    public function resetDeviceConfig(): void
    {
        NakesDeviceConfig::where('user_id', Auth::id())->delete();
    }

    /**
     * API: daftar perangkat + data grafik (untuk polling dashboard).
     * Nakes hanya melihat device miliknya, dokter/superadmin melihat semua.
     */
    public function getDevicesApi(int $minutes = 10)
    {
        $from = now()->subMinutes($minutes);
        $user = Auth::user();

        // Filter device berdasarkan role
        if ($user && $user->role === 'nakes') {
            $config = NakesDeviceConfig::where('user_id', $user->id)->first();
            $devices = $config ? Devices::where('device_id', $config->device_id)->get() : collect();
        } else {
            $devices = Devices::all();
        }

        return $devices->map(function ($device) use ($from) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->latest('created_at')
                ->first();

            $history = SensorData::where('device_id', $device->device_id)
                ->where('created_at', '>=', $from)
                ->orderBy('created_at', 'asc')
                ->get();

            $activeSession = MonitoringSession::where('device_id', $device->device_id)
                ->where('status', 'active')
                ->with('patient')
                ->latest('started_at')
                ->first();

            return [
                'device_id' => $device->device_id,
                'status' => $device->status,
                'latest' => $latest ? [
                    'heart_rate' => $latest->heart_rate,
                    'spo2' => $latest->spo2,
                    'temperature' => $latest->temperature,
                    'status' => $latest->status,
                    'kategori_usia' => $latest->kategori_usia,
                    'created_at' => $latest->created_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ] : null,
                'ml' => [
                    'prediction' => $device->ml_prediction,
                    'condition' => $device->ml_condition,
                    'risk_level' => $device->ml_risk_level,
                    'probabilities' => json_decode($device->ml_probabilities, true),
                    'predicted_at' => $device->ml_predicted_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ],
                'history' => [
                    'labels' => $history->map(fn($d) => $d->created_at->setTimezone('Asia/Jakarta')->format('H:i')),
                    'heart_rate' => $history->pluck('heart_rate'),
                    'spo2' => $history->pluck('spo2'),
                    'temperature' => $history->pluck('temperature'),
                ],
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'medical_record_number' => $activeSession->medical_record_number,
                    'patient_name' => $activeSession->patient?->nama ?? '-',
                    'started_at' => $activeSession->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                ] : null,
            ];
        })->values();
    }

    // --- Private Helpers ---

    private function getDevicesWithLatestData()
    {
        return Devices::all()->map(function ($device) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->latest('created_at')
                ->first();

            $activeSession = MonitoringSession::where('device_id', $device->device_id)
                ->where('status', 'active')
                ->with('patient')
                ->latest('started_at')
                ->first();

            return [
                'device_id' => $device->device_id,
                'status' => $device->status,
                'latest' => $latest ? [
                    'heart_rate' => $latest->heart_rate,
                    'spo2' => $latest->spo2,
                    'temperature' => $latest->temperature,
                    'status' => $latest->status,
                    'kategori_usia' => $latest->kategori_usia,
                    'created_at' => $latest->created_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ] : null,
                'ml' => [
                    'prediction' => $device->ml_prediction,
                    'condition' => $device->ml_condition,
                    'risk_level' => $device->ml_risk_level,
                    'probabilities' => json_decode($device->ml_probabilities, true),
                    'predicted_at' => $device->ml_predicted_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ],
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'medical_record_number' => $activeSession->medical_record_number,
                    'patient_name' => $activeSession->patient?->nama ?? '-',
                    'started_at' => $activeSession->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                ] : null,
            ];
        })->filter(fn($d) => $d['latest'] !== null)->values();
    }

    private function getSuperadminStats(): array
    {
        $totalDevices = Devices::count();
        $activeDevices = Devices::where('status', 'online')->count();
        $inactiveDevices = Devices::where('status', 'offline')->count();
        $totalUsers = User::count();
        $onlineUsers = \DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->count();

        $activeDeviceList = Devices::where('status', 'online')
            ->with(['sensorData' => function ($q) {
                $q->latest('created_at')->limit(1);
            }])
            ->get()
            ->map(function ($device) {
                $latestSensor = $device->sensorData->first();
                $nakesConfig = NakesDeviceConfig::where('device_id', $device->device_id)->first();
                $nakesName = $nakesConfig?->user?->formatted_name ?? '-';

                $dokterNames = $device->monitoredByDokters->map(fn($d) => $d->formatted_name)->toArray();
                $dokterName = !empty($dokterNames) ? implode(', ', $dokterNames) : '-';

                return [
                    'device_id' => $device->device_id,
                    'status' => $latestSensor?->status ?? 'normal',
                    'heart_rate' => $latestSensor?->heart_rate,
                    'spo2' => $latestSensor?->spo2,
                    'temperature' => $latestSensor?->temperature,
                    'nakes_name' => $nakesName,
                    'dokter_name' => $dokterName,
                    'updated_at' => $latestSensor?->created_at?->setTimezone('Asia/Jakarta')->format('H:i:s'),
                ];
            });

        $activityLogs = ActivityLog::orderByDesc('created_at')->limit(20)->get()->map(fn($log) => [
            'id' => $log->id,
            'type' => $log->type,
            'message' => $log->message,
            'icon' => $log->icon,
            'user_name' => $log->user_name,
            'user_role' => $log->user_role,
            'device_id' => $log->device_id,
            'created_at' => $log->created_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
        ]);

        return compact('totalDevices', 'activeDevices', 'inactiveDevices', 'totalUsers', 'onlineUsers', 'activeDeviceList', 'activityLogs');
    }
}
