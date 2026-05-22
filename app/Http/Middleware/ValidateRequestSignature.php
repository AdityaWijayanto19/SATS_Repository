<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateRequestSignature
{
    /**
     * Validate HMAC-SHA256 signature if X-Signature header is present.
     *
     * - Uses per-device secret_key from ApiKey model (not global app.key).
     * - Caches validated key state in Redis for 30 minutes to bypass
     *   expensive DB queries and Bcrypt evaluations on every high-frequency request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only validate POST/PUT/PATCH with signature header
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        $signature = $request->header('X-Signature');

        // If no signature header, pass through — signature is optional but enforced when present
        if (!$signature) {
            return $next($request);
        }

        $apiKey = $request->attributes->get('authenticated_api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not authenticated',
            ], 401);
        }

        // ---------------------------------------------------------------
        // Cache the validated API key state in Redis for 30 minutes.
        // This prevents expensive DB lookups + Bcrypt evaluations on
        // every single 1-2 second high-frequency sensor request.
        // ---------------------------------------------------------------
        $cacheKey = "api_key_validated:{$apiKey->id}";
        $cachedKey = Cache::store('redis')->get($cacheKey);

        if ($cachedKey) {
            // Use cached secret_key to avoid re-querying the DB
            $secretKey = $cachedKey['secret_key'] ?? null;
        } else {
            // Fresh DB lookup — cache the result for 30 minutes
            $secretKey = $apiKey->secret_key ?? null;

            Cache::store('redis')->put($cacheKey, [
                'id'         => $apiKey->id,
                'device_id'  => $apiKey->device_id,
                'secret_key' => $secretKey,
                'is_active'  => $apiKey->is_active,
            ], now()->addMinutes(30));
        }

        if (!$secretKey) {
            Log::warning('API key has no secret_key configured', [
                'api_key_id' => $apiKey->id,
                'device_id'  => $request->route('device_id'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'API key secret not configured',
            ], 500);
        }

        // Get the raw request body
        $body = $request->getContent();

        // Calculate expected signature using per-device secret_key
        $expectedSignature = hash_hmac(
            'sha256',
            $body,
            $secretKey,
            false // Return hex, not raw
        );

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid request signature', [
                'device_id'  => $request->route('device_id'),
                'api_key_id' => $apiKey->id,
                'ip'         => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid request signature',
            ], 401);
        }

        Log::info('Request signature validated', [
            'device_id'  => $request->route('device_id'),
            'api_key_id' => $apiKey->id,
        ]);

        return $next($request);
    }
}
