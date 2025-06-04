<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('sanctum')->check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('sanctum')->user();

        if (!in_array($user->peran ?? $user->role, ['pelanggan', 'customer'])) {
            return response()->json(['message' => 'Akses ditolak. Anda bukan pelanggan.'], 403);
        }

        return $next($request);
    }
}
