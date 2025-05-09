<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login
        if (Auth::attempt($credentials)) {
            // Regenerate session untuk keamanan
            $request->session()->regenerate();

            // Redirect berdasarkan peran
            $user = Auth::user();
            
            if ($user->peran === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->peran === 'pelayan') {
                return redirect()->route('pelayan.dashboard');
            } elseif ($user->peran === 'koki') {
                return redirect()->route('koki.dashboard');
            } else {
                return redirect('/');
            }
        }

        // Kembali ke halaman login dengan pesan error
        return back()->withErrors([
            'email' => 'Email atau password salah',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
}
}