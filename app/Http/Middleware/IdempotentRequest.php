<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IdempotentRequest
{
    /**
     * Handle an incoming request.
     *
     * Uses Laravel Atomic Locks to prevent race conditions on duplicate packets.
     * Caches final responses for 24 hours (only 2xx or 422).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to POST/PUT/PATCH requests
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }

        // Get idempotency key from header
        $idempotencyKey = $request->header('Idempotency-Key');

        if (!$idempotencyKey) {
            return response()->json([
                'success' => false,
                'message' => 'Idempotency-Key header is required for POST/PUT/PATCH requests',
            ], 400);
        }

        // Validate key format (UUID or similar)
        if (!preg_match('/^[a-zA-Z0-9\-_]{20,50}$/', $idempotencyKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Idempotency-Key format',
            ], 400);
        }

        $cacheKey = "idempotency:{$idempotencyKey}";
        $lockKey  = "idempotency:{$idempotencyKey}:lock";

        // Check if we've already processed this request (cached final response)
        if (Cache::has($cacheKey)) {
            $cachedResponse = Cache::get($cacheKey);

            return response()->json(
                $cachedResponse['data'],
                $cachedResponse['status']
            )->header('X-Idempotency-Cached', 'true');
        }

        // Acquire atomic lock to prevent race conditions on duplicate concurrent packets
        $lock = Cache::lock($lockKey, 30); // 30-second lock TTL

        if (!$lock->get()) {
            // Another identical request is currently being processed
            Log::warning('Idempotent request lock contention', [
                'key' => $idempotencyKey,
                'ip'  => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Request is currently being processed. Please wait.',
            ], 409);
        }

        try {
            $response = $next($request);
        } finally {
            // Always release the lock, even if an unexpected exception occurs
            $lock->release();
        }

        $status = $response->status();

        // Cache final response for 24 hours ONLY if successful (2xx) or validation fails (422)
        if ($status >= 200 && $status < 300 || $status === 422) {
            Cache::put($cacheKey, [
                'data'   => json_decode($response->getContent(), true),
                'status' => $status,
            ], now()->addHours(24));
        }

        return $response;
    }
}
