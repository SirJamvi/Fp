<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

// Middleware classes
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register global middleware for API (sessions, cookies, Sanctum)
        $middleware->api([
            // If you ever need sessions for "stateful" SPA, uncomment StartSession:
            StartSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            EnsureFrontendRequestsAreStateful::class, // for Sanctum
        ]);

        // Alias custom middleware so you can use 'customer' and 'auth:sanctum' in routes
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'auth:sanctum' => EnsureFrontendRequestsAreStateful::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // (you can customize exception handlers here)
    })
    ->withProviders([
        // (add any extra service providers here)
    ])
    ->booted(function () {
        RateLimiter::for('api', function (Request $request) {
            // Use Limit so this line won't error
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->create();
