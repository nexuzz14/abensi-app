<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Controller Admin DashboardController
 * Menampilkan halaman utama (dashboard) untuk admin
 * dengan statistik kehadiran dan ringkasan data sistem
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard admin dengan statistik lengkap
     */
    public function index(): View
    {
        $bulanIni  = Carbon::now()->month;
        $tahunIni  = Carbon::now()->year;

        // ========================
        // STATISTIK BULAN INI
        // ========================

        // Total absensi bulan ini berdasarkan status
        $statistikBulanIni = Absensi::whereMonth('tanggal', $bulanIni)
            ->whereYear('tanggal', $tahunIni)
            ->selectRaw('status_kehadiran, COUNT(*) as total')
            ->groupBy('status_kehadiran')
            ->pluck('total', 'status_kehadiran');

        $totalHadir     = $statistikBulanIni->get('hadir', 0);
        $totalTerlambat = $statistikBulanIni->get('terlambat', 0);
        $totalAlpa      = $statistikBulanIni->get('alpa', 0);
        $totalCuti      = $statistikBulanIni->get('cuti', 0);

        // ========================
        // STATISTIK KARYAWAN
        // ========================

        $totalKaryawanAktif = Karyawan::where('status_aktif', true)->count();

        // Karyawan yang belum registrasi wajah
        $belumDaftarWajah = Karyawan::where('status_aktif', true)
            ->whereNull('face_descriptor')
            ->count();

        // ========================
        // STATISTIK CUTI
        // ========================

        $cutiMenungguApproval = Cuti::where('status', 'pending')->count();

        // ========================
        // ABSENSI HARI INI
        // ========================

        $absensiHariIni = Absensi::whereDate('tanggal', today())
            ->with('karyawan')
            ->orderBy('jam_masuk', 'desc')
            ->take(10)
            ->get();

        $sudahAbsenHariIni = Absensi::whereDate('tanggal', today())
            ->whereNotNull('jam_masuk')
            ->count();

        // ========================
        // GRAFIK KEHADIRAN 7 HARI TERAKHIR
        // ========================

        $grafikData = [];
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $grafikData[] = [
                'tanggal'   => $tanggal->format('d/m'),
                'hadir'     => Absensi::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'hadir')->count(),
                'terlambat' => Absensi::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'terlambat')->count(),
                'alpa'      => Absensi::whereDate('tanggal', $tanggal)->where('status_kehadiran', 'alpa')->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'totalHadir',
            'totalTerlambat',
            'totalAlpa',
            'totalCuti',
            'totalKaryawanAktif',
            'belumDaftarWajah',
            'cutiMenungguApproval',
            'absensiHariIni',
            'sudahAbsenHariIni',
            'grafikData'
        ));
    }
}
