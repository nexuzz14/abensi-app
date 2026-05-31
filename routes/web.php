<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Karyawan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — Sistem Absensi Karyawan
|--------------------------------------------------------------------------
|
| Terdapat 3 kelompok route:
| 1. Route publik (tanpa auth): Login, Logout
| 2. Route Admin: Dilindungi middleware auth + role:admin
| 3. Route Karyawan: Dilindungi middleware auth + role:karyawan
|
*/

// ========================
// ROOT REDIRECT
// ========================

// Redirect dari halaman utama ke halaman login
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('karyawan.dashboard');
    }
    return redirect()->route('login');
});

// ========================
// AUTENTIKASI
// ========================

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ========================
// ADMIN ROUTES
// ========================

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {

    // Dashboard Admin
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])
        ->name('dashboard');

    // ========================
    // MASTER DATA KARYAWAN
    // ========================
    Route::resource('karyawan', Admin\KaryawanController::class);

    // Route tambahan untuk karyawan (di luar resource standar)
    Route::patch('/karyawan/{karyawan}/aktifkan', [Admin\KaryawanController::class, 'aktifkan'])
        ->name('karyawan.aktifkan');

    // ========================
    // REGISTRASI WAJAH
    // ========================
    Route::get('/karyawan/{karyawan}/face-register', [Admin\FaceDescriptorController::class, 'show'])
        ->name('karyawan.face-register');

    Route::post('/karyawan/{karyawan}/face-descriptor', [Admin\FaceDescriptorController::class, 'store'])
        ->name('karyawan.face-descriptor.store');

    Route::delete('/karyawan/{karyawan}/face-descriptor', [Admin\FaceDescriptorController::class, 'reset'])
        ->name('karyawan.face-descriptor.reset');

    // ========================
    // MANAJEMEN SHIFT
    // ========================
    Route::resource('shift', Admin\ShiftController::class)->except(['show']);

    // ========================
    // KALENDER LIBUR
    // ========================
    Route::get('/kalender-libur', [Admin\KalenderLiburController::class, 'index'])
        ->name('kalender-libur.index');

    // ========================
    // LOKASI KANTOR
    // ========================
    Route::get('/lokasi-kantor', [Admin\LokasiKantorController::class, 'index'])
        ->name('lokasi-kantor.index');
    Route::put('/lokasi-kantor', [Admin\LokasiKantorController::class, 'update'])
        ->name('lokasi-kantor.update');

    Route::post('/kalender-libur', [Admin\KalenderLiburController::class, 'store'])
        ->name('kalender-libur.store');

    Route::delete('/kalender-libur/{kalenderLibur}', [Admin\KalenderLiburController::class, 'destroy'])
        ->name('kalender-libur.destroy');

    // ========================
    // APPROVAL CUTI
    // ========================
    Route::get('/cuti', [Admin\CutiController::class, 'index'])->name('cuti.index');
    Route::get('/cuti/export-pdf', [Admin\CutiController::class, 'exportPdf'])->name('cuti.export-pdf');
    Route::get('/cuti/export-excel', [Admin\CutiController::class, 'exportExcel'])->name('cuti.export-excel');
    Route::get('/cuti/{cuti}', [Admin\CutiController::class, 'show'])->name('cuti.show');
    Route::get('/cuti/{cuti}/download-surat', [Admin\CutiController::class, 'downloadSurat'])->name('cuti.download-surat');
    Route::post('/cuti/{cuti}/approve', [Admin\CutiController::class, 'approve'])->name('cuti.approve');
    Route::post('/cuti/{cuti}/reject', [Admin\CutiController::class, 'reject'])->name('cuti.reject');

    // ========================
    // LAPORAN ABSENSI
    // ========================
    Route::get('/laporan', [Admin\LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/reset', [Admin\LaporanController::class, 'reset'])->name('laporan.reset');
    Route::put('/laporan/absensi/{absensi}/status', [Admin\LaporanController::class, 'updateStatus'])->name('laporan.update-status');
    Route::get('/laporan/export-pdf', [Admin\LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/export-excel', [Admin\LaporanController::class, 'exportExcel'])->name('laporan.excel');
});

// ========================
// KARYAWAN ROUTES
// ========================

Route::prefix('karyawan')
    ->name('karyawan.')
    ->middleware(['auth', 'role:karyawan'])
    ->group(function () {

    // Dashboard Karyawan
    Route::get('/dashboard', [Karyawan\DashboardController::class, 'index'])
        ->name('dashboard');

    // ========================
    // ABSENSI (CLOCK IN / OUT)
    // ========================

    Route::get('/absensi', [Karyawan\AbsensiController::class, 'index'])
        ->name('absensi.index');

    // Endpoint clock-in dengan rate limiting: max 5 request/menit per user
    Route::post('/absensi/clock-in', [Karyawan\AbsensiController::class, 'clockIn'])
        ->name('absensi.clock-in')
        ->middleware('throttle:5,1');

    // Endpoint clock-out dengan rate limiting: max 5 request/menit per user
    Route::post('/absensi/clock-out', [Karyawan\AbsensiController::class, 'clockOut'])
        ->name('absensi.clock-out')
        ->middleware('throttle:5,1');

    // ========================
    // PENGAJUAN CUTI
    // ========================
    Route::get('/cuti', [Karyawan\CutiController::class, 'index'])->name('cuti.index');
    Route::get('/cuti/ajukan', [Karyawan\CutiController::class, 'create'])->name('cuti.create');
    Route::post('/cuti', [Karyawan\CutiController::class, 'store'])->name('cuti.store');

    // ========================
    // PROFIL & PASSWORD
    // ========================
    Route::get('/profil', [Karyawan\ProfilController::class, 'index'])->name('profil.index');
    Route::put('/profil', [Karyawan\ProfilController::class, 'updateProfil'])->name('profil.update');
    Route::post('/profil/ubah-password', [Karyawan\ProfilController::class, 'ubahPassword'])
        ->name('profil.ubah-password');

    // ========================
    // REGISTRASI WAJAH KARYAWAN
    // ========================
    Route::get('/profil/face-register', [Karyawan\ProfilController::class, 'faceRegister'])
        ->name('profil.face-register');
    Route::post('/profil/face-store', [Karyawan\ProfilController::class, 'faceStore'])
        ->name('profil.face-store');
});

// ========================
// API ROUTE (untuk face-api.js)
// ========================

// Endpoint untuk mengambil face descriptor semua karyawan
// Hanya bisa diakses user yang sudah login
Route::get('/api/face-descriptors', [Admin\FaceDescriptorController::class, 'getDescriptors'])
    ->middleware('auth')
    ->name('api.face-descriptors');
