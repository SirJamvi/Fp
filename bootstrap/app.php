<?php

// bootstrap/app.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

// Middleware classes
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\PelayanMiddleware;
use App\Http\Middleware\KokiMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // ✅ GUNAKAN CARA INI UNTUK MENGAKTIFKAN CORS
        // Menambahkan middleware CORS ke paling awal (global) agar
        // berjalan untuk semua jenis request, termasuk API.
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);

        // API middleware
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
        ]);

        // Alias middleware
        $middleware->alias([
            'customer' => \App\Http\Middleware\CustomerMiddleware::class,
            'pelayan'  => PelayanMiddleware::class,
            'admin'    => AdminMiddleware::class,
            'koki'     => KokiMiddleware::class,
        ]);

        // Exclude CSRF for API routes
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