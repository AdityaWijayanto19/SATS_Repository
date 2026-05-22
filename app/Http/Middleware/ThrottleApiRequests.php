<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->attributes->get('authenticated_api_key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key not authenticated',
            ], 401);
        }

        // Get rate limit from API key (default 60 per minute)
        $rateLimit = $apiKey->rate_limit_per_minute ?? 60;
        $key = "api_key:{$apiKey->id}";

        // Check if request exceeds rate limit
        if (RateLimiter::tooManyAttempts($key, $rateLimit)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'retry_after_seconds' => $retryAfter,
            ], 429)->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($key, 60); // 60 second window

        $response = $next($request);

        // Add remaining requests header
        $remaining = $rateLimit - RateLimiter::attempts($key);
        return $response
            ->header('X-RateLimit-Limit', $rateLimit)
            ->header('X-RateLimit-Remaining', max(0, $remaining))
            ->header('X-RateLimit-Reset', RateLimiter::availableIn($key));
    }
}
