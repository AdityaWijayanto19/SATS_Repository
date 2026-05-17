<?php

namespace App\Http\Controllers;

use App\Events\DeviceStatusChanged;
use App\Models\ApiKey;
use App\Models\Devices;
use App\Models\NakesDeviceConfig;
use App\Models\SensorData;
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

            return [
                'device_id' => $device->device_id,
                'status' => $device->status,
                'latest' => $latest ? [
                    'heart_rate' => $latest->heart_rate,
                    'spo2' => $latest->spo2,
                    'temperature' => $latest->temperature,
                    'status' => $latest->status,
                    'created_at' => $latest->created_at?->format('H:i'),
                    'ml_prediction' => $device->ml_prediction,
                    'ml_condition' => $device->ml_condition,
                    'ml_risk_level' => $device->ml_risk_level,
                    'ml_probabilities' => json_decode($device->ml_probabilities, true),
                    'ml_predicted_at' => $device->ml_predicted_at?->format('H:i'),
                ] : null,
            ];
        })->filter(fn($d) => $d['latest'] !== null)->values();

        return view($this->getViewByRole('dashboard'), compact('devices'));
    }

    // Menampilkan halaman manajemen user (superadmin)
    public function viewManajemenUserPage(){
        return view($this->getViewByRole('manajemen-user'));
    }

    // Menampilkan halaman input data pasien
    public function viewInputDataPasienPage(){
        return view($this->getViewByRole('inputdata'));
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

            return [
                'device_id' => $device->device_id,
                'status' => $device->status,
                'latest' => $latest ? [
                    'heart_rate' => $latest->heart_rate,
                    'spo2' => $latest->spo2,
                    'temperature' => $latest->temperature,
                    'status' => $latest->status,
                    'created_at' => $latest->created_at?->format('H:i'),
                    'ml_prediction' => $device->ml_prediction,
                    'ml_condition' => $device->ml_condition,
                    'ml_risk_level' => $device->ml_risk_level,
                    'ml_probabilities' => json_decode($device->ml_probabilities, true),
                    'ml_predicted_at' => $device->ml_predicted_at?->format('H:i'),
                ] : null,
                'history' => [
                    'labels' => $history->map(fn($d) => $d->created_at->format('H:i')),
                    'heart_rate' => $history->pluck('heart_rate'),
                    'spo2' => $history->pluck('spo2'),
                    'temperature' => $history->pluck('temperature'),
                ],
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }
}
