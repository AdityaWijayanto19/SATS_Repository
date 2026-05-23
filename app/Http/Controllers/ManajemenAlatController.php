<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use App\Models\Devices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ManajemenAlatController extends Controller
{
    public function index()
    {
        $devices = Devices::with(['systemStatus', 'apiKeys:id,device_id,name,is_active', 'sensorData' => function ($q) {
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

        return view('pages.superadmin.manajemen-alat', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|max:50|unique:devices,device_id',
            'nama' => 'required|string|max:255',
        ]);

        // Create device
        $device = Devices::create([
            'device_id' => $request->device_id,
            'status' => 'offline',
        ]);

        // Generate API key (plain text shown once)
        $plainKey = 'sats_' . Str::random(8);

        ApiKey::create([
            'device_id' => $device->device_id,
            'key_hash' => ApiKey::hashKey($plainKey),
            'name' => $request->nama,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil didaftarkan',
            'data' => [
                'device_id' => $device->device_id,
                'nama' => $request->nama,
                'api_key' => $plainKey,
            ],
        ], 201);
    }

    public function show($deviceId)
    {
        $device = Devices::with(['systemStatus', 'apiKeys:id,device_id,name,is_active,last_used'])
            ->where('device_id', $deviceId)
            ->firstOrFail();

        $latestSensor = $device->sensorData()->latest('created_at')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_id,
                'nama' => $device->apiKeys->first()?->name ?? $device->device_id,
                'status' => $device->status,
                'urgensi' => $latestSensor?->status ?? 'normal',
                'terdaftar' => $device->created_at ? Carbon::parse($device->created_at)->format('d M Y') : '-',
                'terakhirAktif' => $device->last_seen ? Carbon::parse($device->last_seen)->format('d M Y, H:i') : '-',
                'battery' => $device->systemStatus?->battery_level,
                'signal' => $device->systemStatus?->signal_strength,
            ],
        ]);
    }

    public function destroy($deviceId)
    {
        $device = Devices::where('device_id', $deviceId)->firstOrFail();
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil dihapus',
        ]);
    }
}
