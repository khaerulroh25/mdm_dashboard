<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function loginAction(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login-attempt:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam {$seconds} detik.");
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password']], $remember)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            AuditLog::log('USER_LOGIN', 'User', (string)Auth::id(), ['username' => Auth::user()->username]);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, <strong>' . htmlspecialchars(Auth::user()->name ?? Auth::user()->username) . '</strong>!');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()
            ->withInput($request->only('username', 'remember'))
            ->withErrors(['username' => 'Username atau password yang Anda masukkan salah.'])
            ->with('error', 'Username atau password yang Anda masukkan salah.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLog::log('USER_LOGOUT', 'User', (string)Auth::id(), ['username' => Auth::user()->username]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
