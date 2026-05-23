<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle API key authentication with Redis-backed caching.
     *
     * Caches validated key state in Redis for 30 minutes to bypass
     * expensive DB queries and Bcrypt evaluations on every high-frequency request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $apiKeyPlain = $request->header('X-API-Key');

            $deviceId = $request->route('device_id')
                ?? $request->input('device_id')
                ?? $request->query('device_id');

            if (!$apiKeyPlain) {
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

            // Redis-backed validation cache (30 min TTL).
            // Keyed by (deviceId + hashed apiKey) to avoid storing plaintext.
            $hashedInput = hash('sha256', $apiKeyPlain);
            $cacheKey = "api_key_auth:{$deviceId}:{$hashedInput}";
            $redis = Cache::store('redis');

            $cached = $redis->get($cacheKey);

            if ($cached !== null) {
                // Cache hit - check if it was a cached failure
                if ($cached === false) {
                    Log::warning('Auth failed (cached rejection)', [
                        'device_id' => $deviceId,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid API key for this device or key expired',
                    ], 401);
                }

                // Cached success - hydrate model from cached data
                $key = new ApiKey();
                $key->setRawAttributes($cached, true);

                // Verify the key is still valid (may have been deactivated between cache writes)
                if (!$key->isValid()) {
                    $redis->forget($cacheKey);

                    Log::warning('Auth failed (cached key no longer valid)', [
                        'device_id' => $deviceId,
                        'key_id' => $key->id,
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid API key for this device or key expired',
                    ], 401);
                }

                // Update last_used without blocking
                $this->touchLastUsed($key, $request->ip());

                $request->attributes->set('authenticated_api_key', $key);

                return $next($request);
            }

            // Cache miss - perform full DB + Bcrypt validation
            Log::info('Auth Attempt (cache miss)', [
                'id' => $deviceId,
                'key' => $hashedInput,
            ]);

            $key = ApiKey::findValidKey($apiKeyPlain, $deviceId);

            if (!$key) {
                // Cache the failure for 30 minutes to prevent repeated Bcrypt attempts
                $redis->put($cacheKey, false, now()->addMinutes(30));

                Log::warning('Auth Failed for Device: ' . $deviceId);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key for this device or key expired',
                ], 401);
            }

            // Cache the successful key (without key_hash) for 30 minutes
            $keyData = $key->toArray();
            unset($keyData['key_hash']);
            $redis->put($cacheKey, $keyData, now()->addMinutes(30));

            // Update last_used
            $key->updateLastUsed($request->ip());

            $request->attributes->set('authenticated_api_key', $key);

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Authentication error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update last_used timestamp without blocking the request.
     * Uses a fire-and-forget DB update; failures are logged but don't block.
     */
    protected function touchLastUsed(ApiKey $key, ?string $ip): void
    {
        try {
            ApiKey::where('id', $key->id)->update([
                'last_used' => now(),
                'last_used_ip' => $ip,
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to update last_used for API key', [
                'key_id' => $key->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
