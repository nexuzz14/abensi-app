<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveCutiRequest;
use App\Models\Absensi;
use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Admin CutiController
 * Menangani approval dan penolakan pengajuan cuti karyawan
 * Saat diapprove, status absensi di hari-hari cuti otomatis diubah menjadi 'cuti'
 */
class CutiController extends Controller
{
    /**
     * Tampilkan daftar semua pengajuan cuti dengan filter status
     */
    public function index(Request $request): View
    {
        $cuti = Cuti::with(['karyawan', 'approvedBy'])
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->bulan, function ($q, $bulan) use ($request) {
                $tahun = $request->tahun ?? now()->year;
                $q->whereMonth('tanggal_mulai', $bulan)->whereYear('tanggal_mulai', $tahun);
            })
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected')")
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalPending  = Cuti::where('status', 'pending')->count();
        $totalApproved = Cuti::where('status', 'approved')->count();
        $totalRejected = Cuti::where('status', 'rejected')->count();

        return view('admin.cuti.index', compact(
            'cuti', 'totalPending', 'totalApproved', 'totalRejected'
        ));
    }

    /**
     * Tampilkan detail pengajuan cuti dan form approve/reject
     */
    public function show(Cuti $cuti): View
    {
        $cuti->load(['karyawan.user', 'approvedBy']);

        return view('admin.cuti.show', compact('cuti'));
    }

    /**
     * Approve pengajuan cuti
     * Setelah diapprove, secara otomatis buat/update record absensi
     * dengan status 'cuti' untuk setiap hari dalam periode cuti tersebut
     */
    public function approve(ApproveCutiRequest $request, Cuti $cuti): RedirectResponse
    {
        // Cuti bisa diupdate kapan saja

        DB::transaction(function () use ($request, $cuti) {
            // 1. Update status cuti menjadi approved
            $cuti->update([
                'status'           => 'approved',
                'approved_by'      => Auth::id(),
                'tanggal_diproses' => now(),
                'catatan_admin'    => $request->catatan_admin,
            ]);

            // 2. Buat record absensi 'cuti' untuk setiap hari dalam periode cuti
            $tanggal = $cuti->tanggal_mulai->copy();

            while ($tanggal->lte($cuti->tanggal_selesai)) {
                // Skip hari Sabtu dan Minggu (libur mingguan)
                if (!in_array($tanggal->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    Absensi::updateOrCreate(
                        [
                            'karyawan_id' => $cuti->karyawan_id,
                            'tanggal'     => $tanggal->toDateString(),
                        ],
                        [
                            'status_kehadiran' => 'cuti',
                            'status_liveness'  => 'skipped',
                            'status_fake_gps'  => 'clean',
                            'keterangan'       => "Cuti {$cuti->jenis_cuti_label}: {$cuti->alasan}",
                        ]
                    );
                }

                $tanggal->addDay();
            }
        });

        return redirect()->route('admin.cuti.index')
            ->with('success', "Pengajuan cuti {$cuti->karyawan->nama_lengkap} berhasil diapprove.");
    }

    /**
     * Tolak pengajuan cuti dengan catatan alasan penolakan
     */
    public function reject(ApproveCutiRequest $request, Cuti $cuti): RedirectResponse
    {
        // Cuti bisa diupdate kapan saja

        $cuti->update([
            'status'           => 'rejected',
            'approved_by'      => Auth::id(),
            'tanggal_diproses' => now(),
            'catatan_admin'    => $request->catatan_admin,
        ]);

        return redirect()->route('admin.cuti.index')
            ->with('success', "Pengajuan cuti {$cuti->karyawan->nama_lengkap} telah ditolak.");
    }

    /**
     * Download surat pendukung cuti
     */
    public function downloadSurat(Cuti $cuti)
    {
        $cuti->load('karyawan');
        if (!$cuti->file_surat) {
            return back()->with('error', 'File surat tidak ditemukan.');
        }

        // Cek file di disk local
        if (!Storage::disk('local')->exists($cuti->file_surat)) {
            return back()->with('error', 'File surat tidak ditemukan di server. Mungkin sudah dihapus.');
        }

        // Buat nama file yang readable untuk download
        $extension = pathinfo($cuti->file_surat, PATHINFO_EXTENSION);
        $namaFile = 'surat-cuti-' . ($cuti->karyawan->nama_lengkap ?? 'karyawan') . '-' . $cuti->tanggal_mulai->format('Y-m-d') . '.' . $extension;
        $namaFile = str_replace(' ', '-', $namaFile);

        return Storage::disk('local')->download($cuti->file_surat, $namaFile);
    }

    /**
     * Export laporan cuti ke PDF
     */
    public function exportPdf(Request $request)
    {
        $cutiList = Cuti::with('karyawan')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->bulan, fn($q, $b) => $q->whereMonth('tanggal_mulai', $b))
            ->orderBy('tanggal_mulai', 'desc')
            ->get();

        $filters = $request->only(['status', 'bulan']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.cuti.pdf', compact('cutiList', 'filters'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('laporan-cuti-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export laporan cuti ke Excel
     */
    public function exportExcel(Request $request)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CutiExport($request->all()),
            'laporan-cuti-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
