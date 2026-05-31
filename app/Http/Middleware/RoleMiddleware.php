<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware RoleMiddleware
 * Memastikan user yang mengakses route memiliki role yang sesuai
 * Digunakan sebagai pelindung untuk route admin dan karyawan
 *
 * Cara penggunaan di routes/web.php:
 * Route::middleware(['auth', 'role:admin'])->group(function () { ... });
 * Route::middleware(['auth', 'role:karyawan'])->group(function () { ... });
 */
class RoleMiddleware
{
    /**
     * Handle incoming request dan cek role user
     *
     * @param Request $request  — Request yang masuk
     * @param Closure $next     — Fungsi untuk melanjutkan ke handler berikutnya
     * @param string  $role     — Role yang dibutuhkan (admin/karyawan)
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Pastikan user sudah login
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = auth()->user();

        // Cek apakah role user sesuai dengan yang dibutuhkan
        if ($user->role !== $role) {
            // Jika user adalah admin yang mencoba akses halaman karyawan,
            // arahkan ke dashboard admin
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('warning', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            // Jika karyawan mencoba akses halaman admin,
            // arahkan ke dashboard karyawan
            if ($user->role === 'karyawan') {
                return redirect()->route('karyawan.dashboard')
                    ->with('warning', 'Anda tidak memiliki izin untuk mengakses halaman admin.');
            }

            // Jika role tidak dikenali, logout dan redirect ke login
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Role sesuai, lanjutkan ke handler berikutnya
        return $next($request);
    }
}
