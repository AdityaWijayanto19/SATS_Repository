<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     *
     * Device harus kirim device_id dari:
     * 1. Route parameter: /api/device/{device_id}/...
     * 2. Request body: {"device_id": "..."}
     * 3. Query string: ?device_id=...
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $apiKey = $request->header('X-API-Key');

            // Get device_id dari route, body, atau query parameter
            $deviceId = $request->route('device_id')
                ?? $request->input('device_id')
                ?? $request->query('device_id');

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'API key missing in X-API-Key header',
                ], 401);
            }

            if (!$deviceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'device_id required (route param, body, or query)',
                ], 401);
            }

            // Validate key belongs to this device
            $key = ApiKey::findValidKey($apiKey, $deviceId);

            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key for this device or key expired',
                ], 401);
            }

            // Update last_used (track device activity)
            $clientIp = $request->ip();
            $key->updateLastUsed($clientIp);

            // Attach key to request for use in controller
            $request->merge(['authenticated_api_key' => $key]);

            return $next($request);
        } catch (\Exception $e) {
            // Catch any error dan return JSON error
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
