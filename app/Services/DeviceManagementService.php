<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\Devices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DeviceManagementService
{
    /**
     * Semua device untuk tabel manajemen alat.
     */
    public function getAllDevices()
    {
        return Devices::with(['systemStatus', 'apiKeys:id,device_id,name,is_active', 'sensorData' => function ($q) {
            $q->latest('created_at')->limit(1);
        }])->get()->map(function ($device) {
            $latestSensor = $device->sensorData->first();
            return [
                'id' => $device->device_id,
                'nama' => $device->apiKeys->first()?->name ?? $device->device_id,
                'status' => $device->status,
                'urgensi' => $latestSensor?->status ?? 'normal',
                'terdaftar' => $device->created_at ? Carbon::parse($device->created_at)->format('d M Y') : '-',
                'terakhirAktif' => $device->last_seen ? Carbon::parse($device->last_seen)->format('d M Y, H:i') : '-',
                'keterangan' => $device->systemStatus?->monitoring_status ?? 'Tidak diketahui',
                'battery' => $device->systemStatus?->battery_level,
                'signal' => $device->systemStatus?->signal_strength,
            ];
        });
    }

    /**
     * Registrasi device baru + generate API key.
     */
    public function registerDevice(string $deviceId, string $nama): array
    {
        $device = Devices::create([
            'device_id' => $deviceId,
            'status' => 'offline',
        ]);

        $plainKey = 'sats_' . Str::random(8);

        ApiKey::create([
            'device_id' => $device->device_id,
            'key_hash' => ApiKey::hashKey($plainKey),
            'name' => $nama,
            'is_active' => true,
        ]);

        $user = Auth::user();
        ActivityLog::log('device.added', "Admin {$user->name} menambahkan alat baru", $user->name, $user->role, $device->device_id);

        return [
            'device_id' => $device->device_id,
            'nama' => $nama,
            'api_key' => $plainKey,
        ];
    }

    /**
     * Detail satu device.
     */
    public function getDeviceDetail(string $deviceId): array
    {
        $device = Devices::with(['systemStatus', 'apiKeys:id,device_id,name,is_active,last_used'])
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $latestSensor = $device->sensorData()->latest('created_at')->first();

        return [
            'device_id' => $device->device_id,
            'nama' => $device->apiKeys->first()?->name ?? $device->device_id,
            'status' => $device->status,
            'urgensi' => $latestSensor?->status ?? 'normal',
            'terdaftar' => $device->created_at ? Carbon::parse($device->created_at)->format('d M Y') : '-',
            'terakhirAktif' => $device->last_seen ? Carbon::parse($device->last_seen)->format('d M Y, H:i') : '-',
            'battery' => $device->systemStatus?->battery_level,
            'signal' => $device->systemStatus?->signal_strength,
        ];
    }

    /**
     * Hapus device.
     */
    public function deleteDevice(string $deviceId): void
    {
        $device = Devices::where('device_id', $deviceId)->firstOrFail();
        $device->delete();

        $user = Auth::user();
        ActivityLog::log('device.deleted', "Admin {$user->name} menghapus alat", $user->name, $user->role, $deviceId);
    }
}
