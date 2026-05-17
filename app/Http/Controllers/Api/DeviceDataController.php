<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSystemStatusRequest;
use App\Http\Requests\RegisterDeviceRequest;
use App\Jobs\ProcessDeviceData;
use App\Models\ApiKey;
use App\Models\Devices;
use App\Services\DeviceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DeviceDataController extends Controller
{
    protected DeviceService $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    /**
     * List all devices for monitoring
     * Endpoint: GET /api/device
     * Accept both session auth (dokter) and API Key (nakes)
     */
    public function listDevices(): JsonResponse
    {
        $apiKey = request()->header('X-API-Key');
        $isAuthenticatedUser = Auth::check();

        // Check authentication: either session auth OR valid API Key
        if (!$apiKey && !$isAuthenticatedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Validate API Key if provided
        if ($apiKey) {
            $validKeys = ApiKey::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->get();

            if ($validKeys->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired API Key',
                ], 401);
            }

            $matchedKey = null;

            foreach ($validKeys as $key) {
                /** @var ApiKey $key */
                if ($key->verifyKey($apiKey)) {
                    $matchedKey = $key;
                    break;
                }
            }

            if (!$matchedKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API Key',
                ], 401);
            }

            // Update last used
            $matchedKey->update([
                'last_used' => now(),
                'last_used_ip' => request()->ip(),
            ]);
        }

        $limit = request('limit', 100);
        $devices = Devices::select('device_id', 'status', 'last_seen')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $devices,
        ], 200);
    }

    /**
     * Register a new device
     * Endpoint: POST /api/device/register
     */
    public function registerDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Check if device already exists
        $existingDevice = Devices::where('device_id', $data['device_id'])->first();

        if ($existingDevice) {
            Log::warning('Device registration attempt for existing device', [
                'device_id' => $data['device_id'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Device already registered',
            ], 409);
        }

        // Create device
        $device = Devices::create([
            'device_id' => $data['device_id'],
            'status' => 'offline',
            'last_seen' => null,
        ]);

        // Generate API key for device
        $plainKey = 'dev_' . Str::random(40);
        $hashedKey = ApiKey::hashKey($plainKey);

        $apiKey = ApiKey::create([
            'device_id' => $data['device_id'],
            'key_hash' => $hashedKey,
            'name' => $data['name'] ?? "Device {$data['device_id']}",
            'is_active' => true,
            'rate_limit_per_minute' => $data['rate_limit_per_minute'] ?? 60,
            'expires_at' => now()->addYears(1),
        ]);

        Log::info('Device registered successfully', [
            'device_id' => $data['device_id'],
            'api_key_id' => $apiKey->id,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'data' => [
                'device_id' => $device->device_id,
                'name' => $apiKey->name,
                'api_key' => $plainKey, // Only shown once at registration
                'rate_limit_per_minute' => $apiKey->rate_limit_per_minute,
                'expires_at' => $apiKey->expires_at,
                'note' => 'Store the API key securely. It will not be shown again.',
            ],
        ], 201);
    }

    public function authenticate(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Device authenticated successfully',
            'data' => [
                'device_id' => request('device_id'),
                'authenticated_at' => now(),
            ],
        ], 200);
    }

    public function storeSystemStatus(
        string $deviceId,
        StoreSystemStatusRequest $request
    ): JsonResponse {
        $data = $request->validated();
        $data['device_id'] = $deviceId;

        $start = microtime(true);

        ProcessDeviceData::dispatch($data);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::channel('device-audit')->info('System status received', [
            'device_id' => $deviceId,
            'api_key_id' => $request->attributes->get('authenticated_api_key')?->id,
            'ip' => $request->ip(),
            'status' => $data['monitoring_status'],
            'battery_level' => $data['battery_level'] ?? null,
            'response_time_ms' => $duration,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'System status stored successfully',
        ], 202);
    }

    public function getSystemStatus(string $deviceId): JsonResponse
    {
        $status = $this->deviceService->getSystemStatus($deviceId);

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'System status not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $status,
        ], 200);
    }

    public function getDeviceConfig(string $deviceId): JsonResponse
    {
        $device = $this->deviceService->getDeviceDetail($deviceId);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'device_id' => $device->device_id,
                'sampling_interval' => 5, // seconds
                'enabled_sensors' => ['heart_rate', 'spo2', 'temperature'],
                'alert_thresholds' => [
                    'heart_rate' => ['min' => 40, 'max' => 140],
                    'spo2' => ['min' => 90, 'max' => 100],
                    'temperature' => ['min' => 35, 'max' => 39],
                ],
                'status' => $device->status,
                'battery_level' => $device->systemStatus?->battery_level,
            ],
        ], 200);
    }
}
