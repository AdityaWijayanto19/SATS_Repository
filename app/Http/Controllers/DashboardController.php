<?php

namespace App\Http\Controllers;

use App\Events\DeviceStatusChanged;
use App\Events\DeviceStatusChangedGlobal;
use App\Models\ActivityLog;
use App\Models\ApiKey;
use App\Models\Devices;
use App\Models\MonitoringSession;
use App\Models\NakesDeviceConfig;
use App\Models\SensorData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    private function getViewByRole($page)
    {
        $role = Auth::user()->role;

        if ($role === 'nakes') {
            return "pages.nakes.$page";
        }

        if ($role === 'dokter') {
            return "pages.dokter.$page";
        }

        if ($role === 'superadmin') {
            return "pages.superadmin.$page";
        }

        abort(403);
    }

    // Menampilkan halaman dashboard
    public function viewDashboardPage()
    {
        $user = Auth::user();

        // Nakes harus setup perangkat dulu sebelum lihat dashboard
        if ($user->role === 'nakes') {
            $hasConfig = NakesDeviceConfig::where('user_id', $user->id)->exists();
            if (!$hasConfig) {
                return view('pages.nakes.setup-device');
            }
        }

        $devices = Devices::all()->map(function ($device) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->latest('created_at')
                ->first();

            // Get active session for this device
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
                    'created_at' => $latest->created_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                    'ml_prediction' => $device->ml_prediction,
                    'ml_condition' => $device->ml_condition,
                    'ml_risk_level' => $device->ml_risk_level,
                    'ml_probabilities' => json_decode($device->ml_probabilities, true),
                    'ml_predicted_at' => $device->ml_predicted_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ] : null,
                'active_session' => $activeSession ? [
                    'id' => $activeSession->id,
                    'medical_record_number' => $activeSession->medical_record_number,
                    'patient_name' => $activeSession->patient?->nama ?? '-',
                    'started_at' => $activeSession->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i'),
                ] : null,
            ];
        })->filter(fn($d) => $d['latest'] !== null)->values();

        // Stats untuk superadmin dashboard
        if ($user->role === 'superadmin') {
            $totalDevices = Devices::count();
            $activeDevices = Devices::where('status', 'online')->count();
            $inactiveDevices = Devices::where('status', 'offline')->count();
            $totalUsers = User::count();
            $onlineUsers = \DB::table('sessions')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
                ->count();

            // Perangkat aktif dengan info nakes dan kondisi pasien
            $activeDeviceList = Devices::where('status', 'online')
                ->with(['sensorData' => function ($q) {
                    $q->latest('created_at')->limit(1);
                }])
                ->get()
                ->map(function ($device) {
                    $latestSensor = $device->sensorData->first();
                    $nakesConfig = NakesDeviceConfig::where('device_id', $device->device_id)->first();
                    $nakesName = $nakesConfig?->user?->name ?? '-';

                    // Ambil semua dokter yang memantau dari pivot table
                    $dokterNames = $device->monitoredByDokters()
                        ->pluck('name')
                        ->toArray();
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

            // Activity logs for the log panel
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

            return view('pages.superadmin.dashboard', compact(
                'devices', 'totalDevices', 'activeDevices', 'inactiveDevices', 'totalUsers', 'onlineUsers', 'activeDeviceList', 'activityLogs'
            ));
        }

        return view($this->getViewByRole('dashboard'), compact('devices'));
    }

    // Menampilkan halaman manajemen user (superadmin)
    public function viewManajemenUserPage(){
        return view($this->getViewByRole('manajemen-user'));
    }

    // Menampilkan halaman input data pasien
    public function viewInputDataPasienPage(){
        $devices = Devices::all()->map(function ($device) {
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

        return view($this->getViewByRole('inputdata'), compact('devices'));
    }

    // Menampilkan halaman laporan
    public function viewLaporanPage(){
        return view($this->getViewByRole('laporan'));
    }

    // Menampilkan halaman login
    public function viewLoginPage(){
        return view('pages.login');
    }

    // Hapus konfigurasi perangkat nakes (ganti perangkat)
    public function resetDeviceConfig()
    {
        NakesDeviceConfig::where('user_id', Auth::id())->delete();
        return redirect()->route('dashboard');
    }

    // Toggle status perangkat (online/offline) dari dashboard nakes
    public function toggleDeviceStatus(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,offline',
        ]);

        $config = NakesDeviceConfig::where('user_id', Auth::id())->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Perangkat belum dikonfigurasi.'], 404);
        }

        Devices::where('device_id', $config->device_id)->update([
            'status' => $request->status,
        ]);

        broadcast(new DeviceStatusChanged($config->device_id, $request->status));
        broadcast(new DeviceStatusChangedGlobal($config->device_id, $request->status));

        $user = Auth::user();
        $logType = $request->status === 'online' ? 'device.online' : 'device.offline';
        $logMsg = $request->status === 'online'
            ? "{$user->name} mengaktifkan perangkat"
            : "{$user->name} menonaktifkan perangkat";
        ActivityLog::log($logType, $logMsg, $user->name, $user->role, $config->device_id);

        return response()->json([
            'success' => true,
            'status' => $request->status,
        ]);
    }

    // Simpan konfigurasi perangkat nakes
    public function saveDeviceConfig(Request $request)
    {
        $request->validate([
            'wifi_name' => 'required|string|max:255',
            'wifi_password' => 'required|string|max:255',
            'api_key' => 'required|string',
        ]);

        // Cari API key yang cocok (karena key di-hash, harus iterate)
        $apiKeys = ApiKey::where('is_active', true)->get();
        $matchedKey = null;

        foreach ($apiKeys as $apiKey) {
            if (Hash::check($request->api_key, $apiKey->key_hash)) {
                $matchedKey = $apiKey;
                break;
            }
        }

        if (!$matchedKey) {
            return back()->withErrors([
                'api_key' => 'API Key tidak valid atau tidak aktif.',
            ])->withInput();
        }

        NakesDeviceConfig::create([
            'user_id' => Auth::id(),
            'device_id' => $matchedKey->device_id,
            'wifi_name' => $request->wifi_name,
            'wifi_password' => $request->wifi_password,
        ]);

        return redirect()->route('dashboard')->with('success', 'Perangkat berhasil dikonfigurasi!');
    }

    // Dokter memilih device untuk dipantau
    public function selectDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
        ]);

        // Cek apakah sudah memantau device ini
        $exists = \App\Models\DeviceMonitoring::where('device_id', $request->device_id)
            ->where('dokter_id', Auth::id())
            ->exists();

        if (!$exists) {
            // Tambahkan monitoring baru (bisa lebih dari 1 dokter per device)
            \App\Models\DeviceMonitoring::create([
                'device_id' => $request->device_id,
                'dokter_id' => Auth::id(),
            ]);

            $user = Auth::user();
            ActivityLog::log('monitoring.started', "Dokter {$user->name} mulai memantau perangkat", $user->name, $user->role, $request->device_id);
        }

        // Broadcast update ke superadmin
        $device = Devices::where('device_id', $request->device_id)->first();
        broadcast(new DeviceStatusChangedGlobal($request->device_id, $device->status));

        return response()->json(['success' => true]);
    }

    // Dokter berhenti memantau device tertentu
    public function deselectDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|exists:devices,device_id',
        ]);

        \App\Models\DeviceMonitoring::where('device_id', $request->device_id)
            ->where('dokter_id', Auth::id())
            ->delete();

        // Broadcast update ke superadmin
        $device = Devices::where('device_id', $request->device_id)->first();
        broadcast(new DeviceStatusChangedGlobal($request->device_id, $device->status));

        $user = Auth::user();
        ActivityLog::log('monitoring.stopped', "Dokter {$user->name} berhenti memantau perangkat", $user->name, $user->role, $request->device_id);

        return response()->json(['success' => true]);
    }

    // Dokter berhenti memantau semua device (saat logout)
    public function deselectAllDevices()
    {
        $deviceIds = \App\Models\DeviceMonitoring::where('dokter_id', Auth::id())
            ->pluck('device_id');

        \App\Models\DeviceMonitoring::where('dokter_id', Auth::id())->delete();

        // Broadcast update ke superadmin untuk setiap device
        foreach ($deviceIds as $deviceId) {
            $device = Devices::where('device_id', $deviceId)->first();
            if ($device) {
                broadcast(new DeviceStatusChangedGlobal($deviceId, $device->status));
            }
        }
    }

    // API: daftar semua perangkat + data grafik (untuk polling dashboard)
    public function getDevicesApi()
    {
        $minutes = (int) request('minutes', 10);
        $from = now()->subMinutes($minutes);

        $devices = Devices::all()->map(function ($device) use ($from) {
            $latest = SensorData::where('device_id', $device->device_id)
                ->latest('created_at')
                ->first();

            // Data grafik 10 menit terakhir (satu query, sama dengan data card)
            $history = SensorData::where('device_id', $device->device_id)
                ->where('created_at', '>=', $from)
                ->orderBy('created_at', 'asc')
                ->get();

            // Get active session for this device
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
                    'created_at' => $latest->created_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                    'ml_prediction' => $device->ml_prediction,
                    'ml_condition' => $device->ml_condition,
                    'ml_risk_level' => $device->ml_risk_level,
                    'ml_probabilities' => json_decode($device->ml_probabilities, true),
                    'ml_predicted_at' => $device->ml_predicted_at?->setTimezone('Asia/Jakarta')->format('H:i'),
                ] : null,
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

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }
}
