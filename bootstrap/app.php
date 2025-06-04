<?php

// bootstrap/app.php

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Middleware API (stateless)
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
        ]);

        // ← Daftar alias middleware di sini
        $middleware->alias([
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'pelayan'  => \App\Http\Middleware\PelayanMiddleware::class,
            'admin'    => \App\Http\Middleware\AdminMiddleware::class,
            'koki'     => \App\Http\Middleware\KokiMiddleware::class,
        ]);

        // Kecualikan CSRF untuk semua rute API
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->booted(function () {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    })
    ->create();
