<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\LoginRequest;
use App\Http\Requests\Customer\RegisterRequest;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new customer.
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Debug log
            Log::info('Register attempt', $request->all());
            
            $pengguna = Pengguna::create([
                'nama'      => $request->nama,
                'email'     => $request->email,
                'nomor_hp'  => $request->nomor_hp,
                'password'  => Hash::make($request->password),
                'peran'     => 'pelanggan',
            ]);

            $token = $pengguna->createToken('customer-api-token')->plainTextToken;

            Log::info('Register success', ['user_id' => $pengguna->id]);

            return response()->json([
                'message' => 'Registrasi pelanggan berhasil.',
                'user'    => $pengguna,
                'token'   => $token,
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Register error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Terjadi kesalahan saat registrasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Authenticate a customer and issue a token.
     */
    public function login(LoginRequest $request)
    {
        try {
            // Debug log
            Log::info('Login attempt', ['email' => $request->email]);
            
            // Cari berdasarkan email atau nomor_hp
            $pengguna = Pengguna::where(function ($q) use ($request) {
                $q->where('email', $request->email)
                  ->orWhere('nomor_hp', $request->email);
            })->first();

            if (! $pengguna || ! Hash::check($request->password, $pengguna->password)) {
                return response()->json([
                    'message' => 'Email/Nomor HP atau password tidak valid.'
                ], 401);
            }

            // Pastikan pengguna adalah 'pelanggan'
            if ($pengguna->peran !== 'pelanggan') {
                return response()->json([
                    'message' => 'Akses ditolak. Anda bukan pelanggan.'
                ], 403);
            }

            // Hapus token lama (jika ada)
            $pengguna->tokens()->delete();

            // Buat token baru
            $token = $pengguna->createToken('customer-api-token')->plainTextToken;

            Log::info('Login success', ['user_id' => $pengguna->id]);

            return response()->json([
                'message' => 'Login berhasil.',
                'user'    => $pengguna,
                'token'   => $token,
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'message' => 'Terjadi kesalahan saat login.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log out the authenticated customer (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ], 200);
    }
}