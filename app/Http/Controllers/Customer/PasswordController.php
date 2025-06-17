<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    /**
     * Request a password reset.
     * Generates a temporary identifier (token) and associates it with the user.
     * This token will be passed to the Ionic app, not emailed.
     */
    public function requestReset(Request $request)
    {
        $request->validate([
            'email_or_phone' => 'required|string',
        ]);

        $input = $request->email_or_phone;

        // Cari pengguna berdasarkan email atau nomor HP
        $user = Pengguna::where('email', $input)
                        ->orWhere('nomor_hp', $input)
                        ->where('peran', 'pelanggan') // Pastikan hanya pelanggan
                        ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email_or_phone' => ['Email atau nomor HP tidak terdaftar.'],
            ]);
        }

        // Hapus token lama jika ada (opsional, tergantung kebutuhan keamanan)
        // DB::table('password_resets_tokens')->where('email', $user->email)->delete();

        // Generate token sementara. Kita akan menyimpannya di DB atau cache.
        // Untuk contoh ini, kita akan menyimpan di tabel 'password_resets_tokens' (Anda perlu membuatnya)
        // Atau bisa juga di kolom sementara di tabel 'pengguna' jika tidak sensitif.
        // Karena tidak ada email, kita akan membuat token yang langsung dikirimkan kembali ke Ionic.
        // Token ini harus short-lived.

        // Membuat token sederhana sebagai contoh, sebaiknya gunakan sesuatu yang lebih kuat dan unik.
        $token = Str::random(64); // Ini akan menjadi 'identifier'
        
        // Simpan token dengan user_id dan waktu kedaluwarsa
        // Anda perlu membuat tabel 'password_resets_tokens' dengan kolom:
        // 'user_id', 'token', 'created_at', 'expires_at'
        DB::table('password_resets_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token'      => $token,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10), // Token berlaku 10 menit
            ]
        );

        Log::info('Password reset requested', ['user_id' => $user->id, 'token' => substr($token, 0, 10) . '...']);

        return response()->json([
            'message' => 'Verifikasi berhasil. Silakan lanjutkan dengan memasukkan password baru.',
            'identifier' => $token, // Kirimkan token ini ke frontend
        ]);
    }

    /**
     * Reset the user's password.
     * Receives the identifier (token) and new password from Ionic.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|size:64', // Ukuran token yang Anda buat
            'password' => 'required|string|min:8|confirmed',
        ]);

        $token = $request->identifier;
        $password = $request->password;

        // Cari token di tabel password_resets_tokens
        $resetEntry = DB::table('password_resets_tokens')
                        ->where('token', $token)
                        ->first();

        if (!$resetEntry || now()->isAfter($resetEntry->expires_at)) {
            throw ValidationException::withMessages([
                'identifier' => ['Token tidak valid atau sudah kedaluwarsa. Silakan coba lagi.'],
            ]);
        }

        $user = Pengguna::find($resetEntry->user_id);

        if (!$user || $user->peran !== 'pelanggan') {
            throw ValidationException::withMessages([
                'identifier' => ['Pengguna tidak ditemukan atau tidak diizinkan untuk reset password.'],
            ]);
        }

        // Update password pengguna
        $user->password = Hash::make($password);
        $user->save();

        // Hapus token setelah digunakan
        DB::table('password_resets_tokens')->where('token', $token)->delete();

        Log::info('Password reset successfully', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Password Anda berhasil diubah. Silakan login dengan password baru.',
        ]);
    }
}