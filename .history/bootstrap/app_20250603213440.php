<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

// Middleware classes
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register API middleware - REMOVE sessions and cookies for mobile API
        $middleware->api([
            // Do NOT include session middleware for mobile API
            // StartSession::class,
            // EncryptCookies::class,
            // AddQueuedCookiesToResponse::class,
            
            // Only include Sanctum for token-based auth
            EnsureFrontendRequestsAreStateful::class,
        ]);

        // Alias custom middleware
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
        ]);

        // Configure CORS for API
        $middleware->web([
            \Illuminate\Foundation\Http\Middleware\HandleCors::class,
        ]);
        
        $middleware->api([
            \Illuminate\Foundation\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();