<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanctum sudah mengautentikasi user sebelumnya via middleware 'auth:sanctum'
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Tidak terautentikasi. Token tidak valid atau tidak diberikan.'
            ], 401);
        }

        if ($user->peran !== 'pelanggan') {
            return response()->json([
                'message' => 'Akses ditolak. Anda tidak memiliki izin sebagai pelanggan.'
            ], 403);
        }

        return $next($request);
    }
}
