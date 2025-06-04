<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; // Ditambahkan jika belum ada
use Illuminate\Support\Facades\RateLimiter; // Ditambahkan jika belum ada
use Illuminate\Cache\RateLimiting\Limit; // Ditambahkan jika belum ada

// Middleware classes
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\AdminMiddleware; // Tambahkan ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API middleware - TIDAK TERMASUK session middleware untuk stateless API
        $middleware->api([
            EnsureFrontendRequestsAreStateful::class, // Sanctum middleware 
            'throttle:api', [cite: 8]
        ]);

        // Alias middleware
        $middleware->alias([
            'customer' => \App\Http\Middleware\CustomerMiddleware::class, [cite: 8]
            'admin' => AdminMiddleware::class, // Daftarkan alias 'admin' di sini
            // Anda juga bisa mendaftarkan alias untuk 'pelayan' dan 'koki' jika diperlukan dengan cara yang sama
            // 'pelayan' => \App\Http\Middleware\PelayanMiddleware::class, // Contoh jika ada PelayanMiddleware
            // 'koki' => \App\Http\Middleware\KokiMiddleware::class,       // Contoh jika ada KokiMiddleware
        ]);

        // PENTING: Exclude CSRF untuk API routes
        $middleware->validateCsrfTokens(except: [
            'api/*', [cite: 8]
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 
    })
    ->booted(function () {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()); [cite: 10]
        }); [cite: 10]
    })
    ->create();