<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterDeviceManajemenRequest;
use App\Services\DeviceManagementService;

class ManajemenAlatController extends Controller
{
    public function __construct(
        private DeviceManagementService $deviceManagementService
    ) {}

    public function index()
    {
        $devices = $this->deviceManagementService->getAllDevices();

        return view('pages.superadmin.manajemen-alat', compact('devices'));
    }

    public function store(RegisterDeviceManajemenRequest $request)
    {
        $data = $this->deviceManagementService->registerDevice(
            $request->validated('device_id'),
            $request->validated('nama')
        );

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil didaftarkan',
            'data' => $data,
        ], 201);
    }

    public function show($deviceId)
    {
        $data = $this->deviceManagementService->getDeviceDetail($deviceId);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function destroy($deviceId)
    {
        $this->deviceManagementService->deleteDevice($deviceId);

        return response()->json([
            'success' => true,
            'message' => 'Perangkat berhasil dihapus',
        ]);
    }
}
