<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller Karyawan DashboardController
 * Menampilkan dashboard karyawan dengan info shift, status absensi hari ini,
 * dan riwayat absensi 30 hari terakhir
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard karyawan
     */
    public function index(): View
    {
        // Ambil data karyawan yang sedang login
        $karyawan = Auth::user()->karyawan;

        if (!$karyawan) {
            return view('karyawan.dashboard', ['karyawan' => null]);
        }

        // Load relasi yang diperlukan
        $karyawan->load('user');

        // ========================
        // INFO HARI INI
        // ========================

        // Shift karyawan hari ini
        $shiftHariIni = $karyawan->getShiftAktif(today());

        // Status absensi hari ini
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // ========================
        // RIWAYAT ABSENSI 30 HARI
        // ========================

        $riwayatAbsensi = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', '>=', Carbon::today()->subDays(30))
            ->orderBy('tanggal', 'desc')
            ->get();

        // ========================
        // STATISTIK BULAN INI
        // ========================

        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;

        $statistikBulanIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->selectRaw('status_kehadiran, COUNT(*) as total')
            ->groupBy('status_kehadiran')
            ->pluck('total', 'status_kehadiran');

        // ========================
        // STATUS CUTI AKTIF
        // ========================

        $cutiAktif = Cuti::where('karyawan_id', $karyawan->id)
            ->where('status', 'approved')
            ->where('tanggal_selesai', '>=', today())
            ->orderBy('tanggal_mulai')
            ->first();

        $cutiPending = Cuti::where('karyawan_id', $karyawan->id)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();

        return view('karyawan.dashboard', compact(
            'karyawan',
            'shiftHariIni',
            'absensiHariIni',
            'riwayatAbsensi',
            'statistikBulanIni',
            'cutiAktif',
            'cutiPending'
        ));
    }
}
