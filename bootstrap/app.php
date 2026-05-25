<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\AuthenticateApiKey;
use App\Http\Middleware\ThrottleApiRequests;
use App\Http\Middleware\IdempotentRequest;
use App\Http\Middleware\ValidateRequestSignature;
use App\Http\Middleware\AuthenticateMonitoringAccess;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append(TrackUserActivity::class);
        $middleware->validateCsrfTokens(except: [
            'broadcasting/*',
        ]);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'apikey' => AuthenticateApiKey::class,
            'throttle.api' => ThrottleApiRequests::class,
            'idempotent' => IdempotentRequest::class,
            'sign.verify' => ValidateRequestSignature::class,
            'monitoring.auth' => AuthenticateMonitoringAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
