<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

// Middleware classes
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\AdminMiddleware;
// Pastikan Anda juga mengimpor PelayanMiddleware dan KokiMiddleware jika Anda menggunakannya
// use App\Http\Middleware\PelayanMiddleware;
// use App\Http\Middleware\KokiMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API middleware
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
        ]);

        // Alias middleware
        $middleware->alias([
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'admin'    => AdminMiddleware::class,
            // Aktifkan baris berikut jika Anda memiliki dan ingin menggunakan middleware tersebut
            // 'pelayan'  => \App\Http\Middleware\PelayanMiddleware::class,
            // 'koki'     => \App\Http\Middleware\KokiMiddleware::class,
        ]);

        // Exclude CSRF untuk API routes
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