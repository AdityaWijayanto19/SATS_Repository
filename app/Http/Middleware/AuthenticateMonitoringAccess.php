<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMonitoringAccess
{
    /**
     * Handle monitoring endpoint authentication.
     *
     * Accepts two authentication methods:
     * 1. Session auth (dokter/nakes logged in via web)
     * 2. API Key via X-API-Key header (nakes monitoring via frontend)
     *
     * This allows both dokter (session-based) and nakes (API key-based)
     * to access the same monitoring GET endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Method 1: Already authenticated via session (dokter)
        if (Auth::check()) {
            return $next($request);
        }

        // Method 2: API Key authentication (nakes)
        $apiKeyPlain = $request->header('X-API-Key');

        if ($apiKeyPlain) {
            return $this->authenticateWithApiKey($request, $next, $apiKeyPlain);
        }

        // No authentication found
        return response()->json([
            'success' => false,
            'message' => 'Authentication required. Please log in or provide X-API-Key header.',
        ], 401);
    }

    protected function authenticateWithApiKey(Request $request, Closure $next, string $apiKeyPlain): Response
    {
        try {
            $deviceId = $request->route('device_id')
                ?? $request->input('device_id')
                ?? $request->query('device_id');

            // For device list endpoint (no device_id), validate key without device restriction
            $hashedInput = hash('sha256', $apiKeyPlain);
            $cacheKey = $deviceId
                ? "monitoring_auth:{$deviceId}:{$hashedInput}"
                : "monitoring_auth:list:{$hashedInput}";

            $redis = Cache::store('redis');
            $cached = $redis->get($cacheKey);

            if ($cached !== null) {
                if ($cached === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'API Key tidak valid atau sudah expired.',
                    ], 401);
                }

                $key = new ApiKey();
                $key->setRawAttributes($cached, true);

                if (!$key->isValid()) {
                    $redis->forget($cacheKey);
                    return response()->json([
                        'success' => false,
                        'message' => 'API Key tidak valid atau sudah expired.',
                    ], 401);
                }

                $request->attributes->set('authenticated_api_key', $key);
                return $next($request);
            }

            // Cache miss - full DB validation
            if ($deviceId) {
                $key = ApiKey::findValidKey($apiKeyPlain, $deviceId);
            } else {
                // For device list, find any active key matching the plain key
                $key = ApiKey::where('is_active', true)
                    ->get()
                    ->first(fn($k) => (!$k->expires_at || $k->expires_at->isFuture()) && Hash::check($apiKeyPlain, $k->key_hash));
            }

            if (!$key) {
                $redis->put($cacheKey, false, now()->addMinutes(30));
                return response()->json([
                    'success' => false,
                    'message' => 'API Key tidak valid atau sudah expired.',
                ], 401);
            }

            $keyData = $key->toArray();
            unset($keyData['key_hash']);
            $redis->put($cacheKey, $keyData, now()->addMinutes(30));

            $request->attributes->set('authenticated_api_key', $key);
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Monitoring auth error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication error.',
            ], 500);
        }
    }
}
