<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveDeviceConfigRequest;
use App\Http\Requests\SelectDeviceRequest;
use App\Http\Requests\ToggleDeviceStatusRequest;
use App\Models\NakesDeviceConfig;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    private function getViewByRole($page)
    {
        $role = Auth::user()->role;

        if (in_array($role, ['nakes', 'dokter', 'superadmin'])) {
            return "pages.{$role}.{$page}";
        }

        abort(403);
    }

    // Menampilkan halaman dashboard
    public function viewDashboardPage()
    {
        $result = $this->dashboardService->getDashboardData();

        if ($result['view'] === 'pages.nakes.setup-device') {
            return view('pages.nakes.setup-device');
        }

        return view($result['view'], $result['data']);
    }

    // Menampilkan halaman manajemen user (superadmin)
    public function viewManajemenUserPage()
    {
        return view($this->getViewByRole('manajemen-user'));
    }

    // Menampilkan halaman input data pasien
    public function viewInputDataPasienPage()
    {
        $devices = $this->dashboardService->getDevicesWithActiveSession();
        $dokters = User::where('role', 'dokter')->select('id', 'name')->get();

        return view($this->getViewByRole('inputdata'), compact('devices', 'dokters'));
    }

    // Menampilkan halaman laporan
    public function viewLaporanPage()
    {
        return view($this->getViewByRole('laporan'));
    }

    // Menampilkan halaman login
    public function viewLoginPage()
    {
        return view('pages.login');
    }

    // Hapus konfigurasi perangkat nakes (ganti perangkat)
    public function resetDeviceConfig()
    {
        $this->dashboardService->resetDeviceConfig();

        return redirect()->route('dashboard');
    }

    // Toggle status perangkat (online/offline) dari dashboard nakes
    public function toggleDeviceStatus(ToggleDeviceStatusRequest $request)
    {
        $config = NakesDeviceConfig::where('user_id', Auth::id())->first();
        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Perangkat belum dikonfigurasi.'], 404);
        }

        $result = $this->dashboardService->toggleDeviceStatus($request->validated('status'), $config);

        return response()->json($result);
    }

    // Simpan konfigurasi perangkat nakes
    public function saveDeviceConfig(SaveDeviceConfigRequest $request)
    {
        $result = $this->dashboardService->saveDeviceConfig($request->validated());

        if (!$result['success']) {
            return back()->withErrors([
                $result['error'] => $result['message'],
            ])->withInput();
        }

        return redirect()->route('dashboard')->with('success', 'Perangkat berhasil dikonfigurasi!');
    }

    // Dokter memilih device untuk dipantau
    public function selectDevice(SelectDeviceRequest $request)
    {
        $this->dashboardService->selectDevice($request->validated('device_id'));

        return response()->json(['success' => true]);
    }

    // Dokter berhenti memantau device tertentu
    public function deselectDevice(SelectDeviceRequest $request)
    {
        $this->dashboardService->deselectDevice($request->validated('device_id'));

        return response()->json(['success' => true]);
    }

    // Dokter berhenti memantau semua device (saat logout)
    public function deselectAllDevices()
    {
        $this->dashboardService->deselectAllDevices();
    }

    // API: daftar semua perangkat + data grafik (untuk polling dashboard)
    public function getDevicesApi()
    {
        $minutes = (int) request('minutes', 10);
        $devices = $this->dashboardService->getDevicesApi($minutes);

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }
}
