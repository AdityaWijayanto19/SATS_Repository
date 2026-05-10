<?php

namespace App\Http\Controllers;

use App\Models\Devices;
use App\Models\SensorData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // API: daftar semua perangkat (untuk polling dashboard)
    public function getDevicesApi()
    {
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
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $devices,
        ]);
    }
}
