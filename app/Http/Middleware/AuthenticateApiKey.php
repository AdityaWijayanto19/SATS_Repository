<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $apiKey = $request->header('X-API-Key');

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

            $key = ApiKey::findValidKey($apiKey, $deviceId);

            if (!$key) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key for this device or key expired',
                ], 401);
            }

            $clientIp = $request->ip();
            $key->updateLastUsed($clientIp);

            $request->merge(['authenticated_api_key' => $key]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
