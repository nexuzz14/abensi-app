<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller AuthController
 * Menangani proses login dan logout untuk semua role (admin dan karyawan)
 * Menggunakan session-based authentication bawaan Laravel
 */
class AuthController extends Controller
{
    /**
     * Tampilkan halaman login
     * Jika user sudah login, redirect ke dashboard sesuai role
     */
    public function showLoginForm(): View|RedirectResponse
    {
        // Jika sudah login, redirect ke dashboard yang sesuai
        if (Auth::check()) {
            return $this->redirectByRole();
        }

        return view('auth.login');
    }

    /**
     * Proses login: validasi kredensial dan buat sesi
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $identifier = $request->identifier;
        $password   = $request->password;
        $remember   = $request->boolean('remember');

        // Jika identifier mengandung '@', login sebagai Admin via email
        if (str_contains($identifier, '@')) {
            if (Auth::attempt(['email' => $identifier, 'password' => $password], $remember)) {
                $request->session()->regenerate();
                return $this->redirectByRole()
                    ->with('success', 'Selamat datang, ' . Auth::user()->name . '! 👋');
            }

            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['identifier' => 'Email atau password salah.']);
        }

        // Jika tidak ada '@', login sebagai Karyawan via NIP
        // Gunakan strtolower/strtoupper atau LIKE untuk case-insensitive NIP
        $karyawan = Karyawan::where('nip', $identifier)
            ->orWhere('nip', strtoupper($identifier))
            ->orWhere('nip', strtolower($identifier))
            ->first();

        if (!$karyawan || !$karyawan->status_aktif) {
            return back()
                ->withInput($request->only('identifier'))
                ->withErrors(['identifier' => 'NIP tidak ditemukan atau akun sudah dinonaktifkan.']);
        }

        if (Auth::attempt(['email' => $karyawan->user->email, 'password' => $password], $remember)) {
            $request->session()->regenerate();
            return $this->redirectByRole()
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '! 👋');
        }

        return back()
            ->withInput($request->only('identifier'))
            ->withErrors(['identifier' => 'NIP atau password yang Anda masukkan salah.']);
    }

    /**
     * Proses logout: hapus sesi dan redirect ke halaman login
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidasi sesi saat ini
        $request->session()->invalidate();

        // Regenerasi CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Helper: redirect ke dashboard sesuai role user yang sedang login
     */
    private function redirectByRole(): RedirectResponse
    {
        return match (Auth::user()->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'karyawan' => redirect()->route('karyawan.dashboard'),
            default    => redirect()->route('login'),
        };
    }
}
