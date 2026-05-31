<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Services\AbsensiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AbsensiExport;

/**
 * Controller Admin LaporanController
 * Menampilkan dan mengekspor laporan absensi karyawan
 * Mendukung filter berdasarkan tanggal, bulan, no_hp, karyawan, dan status
 */
class LaporanController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService
    ) {}

    /**
     * Tampilkan halaman laporan dengan filter dan tabel data
     */


    public function index(Request $request): View
    {
        // Siapkan filter dari request
        $filter = [
            'bulan'          => $request->bulan ?? now()->month,
            'tahun'          => $request->tahun ?? now()->year,
            'tanggal_dari'   => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai,
            'karyawan_id'    => $request->karyawan_id,
            'status'         => $request->status,
        ];

        // Ambil data absensi sesuai filter
        $absensi = $this->absensiService->getDataLaporan($filter);

        // Hitung summary card
        $summary = [
            'hadir'     => $absensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
            'alpa'      => $absensi->where('status_kehadiran', 'alpa')->count(),
            'cuti'      => $absensi->where('status_kehadiran', 'cuti')->count(),
        ];

        $karyawanList = Karyawan::where('status_aktif', true)->orderBy('nama_lengkap')->get();

        // Daftar bulan untuk dropdown
        $bulanList = [];
        for ($i = 1; $i <= 12; $i++) {
            $bulanList[$i] = Carbon::create(null, $i)->translatedFormat('F');
        }

        return view('admin.laporan.index', compact(
            'absensi',
            'summary',
            'filter',
            'karyawanList',
            'bulanList'
        ));
    }

    /**
     * Export laporan absensi ke format PDF menggunakan DomPDF
     */
    public function exportPdf(Request $request): Response
    {
        $filter = [
            'bulan'          => $request->bulan ?? now()->month,
            'tahun'          => $request->tahun ?? now()->year,
            'tanggal_dari'   => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai,
            'karyawan_id'    => $request->karyawan_id,
            'status'         => $request->status,
        ];

        $absensi = $this->absensiService->getDataLaporan($filter);

        $summary = [
            'hadir'     => $absensi->where('status_kehadiran', 'hadir')->count(),
            'terlambat' => $absensi->where('status_kehadiran', 'terlambat')->count(),
            'alpa'      => $absensi->where('status_kehadiran', 'alpa')->count(),
            'cuti'      => $absensi->where('status_kehadiran', 'cuti')->count(),
        ];

        $namaFile = 'laporan-absensi-' . now()->format('Y-m-d') . '.pdf';

        // Generate PDF dari view khusus untuk PDF (tanpa navbar/sidebar)
        $pdf = Pdf::loadView('admin.laporan.pdf', compact('absensi', 'summary', 'filter'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream($namaFile);
    }

    /**
     * Export laporan absensi ke format Excel menggunakan Maatwebsite/Excel
     */
    public function exportExcel(Request $request)
    {
        $filter = [
            'bulan'          => $request->bulan ?? now()->month,
            'tahun'          => $request->tahun ?? now()->year,
            'tanggal_dari'   => $request->tanggal_dari,
            'tanggal_sampai' => $request->tanggal_sampai,
            'karyawan_id'    => $request->karyawan_id,
            'status'         => $request->status,
        ];

        $namaFile = 'laporan-absensi-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new AbsensiExport($filter, $this->absensiService), $namaFile);
    }

    /**
     * Update status kehadiran secara manual oleh admin
     */
    public function updateStatus(Request $request, Absensi $absensi)
    {
        $request->validate([
            'status_kehadiran' => 'required|in:hadir,terlambat,alpa,izin,sakit,cuti'
        ]);

        $absensi->update([
            'status_kehadiran' => $request->status_kehadiran
        ]);

        return redirect()->back()->with('success', 'Status kehadiran berhasil diperbarui.');
    }
}
