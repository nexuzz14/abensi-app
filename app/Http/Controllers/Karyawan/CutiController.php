<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\StoreCutiRequest;
use App\Models\Cuti;
use App\Models\KalenderLibur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Karyawan CutiController
 * Menangani pengajuan cuti oleh karyawan
 * Dilengkapi validasi overlap, validasi hari libur, dan upload surat
 */
class CutiController extends Controller
{
    /**
     * Tampilkan riwayat cuti karyawan yang sedang login
     */
    public function index(): View
    {
        $karyawan = Auth::user()->karyawan;

        $riwayatCuti = Cuti::where('karyawan_id', $karyawan->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('karyawan.cuti.index', compact('riwayatCuti'));
    }

    /**
     * Tampilkan form pengajuan cuti baru
     */
    public function create(): View
    {
        return view('karyawan.cuti.create');
    }

    /**
     * Simpan pengajuan cuti ke database
     * Dengan validasi overlap dan validasi hari libur
     */
    public function store(StoreCutiRequest $request): RedirectResponse
    {
        $karyawan = Auth::user()->karyawan;
        $validated = $request->validated();

        // === VALIDASI TAMBAHAN: Cek apakah semua hari adalah hari libur ===
        $tanggalMulai   = \Carbon\Carbon::parse($validated['tanggal_mulai']);
        $tanggalSelesai = \Carbon\Carbon::parse($validated['tanggal_selesai']);

        // Cek apakah ada hari kerja dalam rentang tersebut
        $adaHariKerja = false;
        $current      = $tanggalMulai->copy();

        while ($current->lte($tanggalSelesai)) {
            $isWeekend = in_array($current->dayOfWeek, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
            $isHoliday = KalenderLibur::isHariLibur($current->toDateString());

            if (!$isWeekend && !$isHoliday) {
                $adaHariKerja = true;
                break;
            }
            $current->addDay();
        }

        if (!$adaHariKerja) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tidak ada hari kerja dalam rentang tanggal yang dipilih. Semua hari adalah libur.');
        }

        // === VALIDASI: Cek overlap dengan cuti yang sudah approved ===
        $overlap = Cuti::where('karyawan_id', $karyawan->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('tanggal_mulai', '<=', $validated['tanggal_mulai'])
                         ->where('tanggal_selesai', '>=', $validated['tanggal_selesai']);
                  });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tanggal yang dipilih bertabrakan dengan cuti yang sudah disetujui.');
        }

        // Upload surat pendukung jika ada
        $fileSurat = null;
        if ($request->hasFile('file_surat')) {
            $fileSurat = $request->file('file_surat')
                ->store("surat-cuti/{$karyawan->id}", 'local');
        }

        // Simpan pengajuan cuti
        Cuti::create([
            'karyawan_id'    => $karyawan->id,
            'tanggal_mulai'  => $validated['tanggal_mulai'],
            'tanggal_selesai'=> $validated['tanggal_selesai'],
            'jenis_cuti'     => $validated['jenis_cuti'],
            'alasan'         => $validated['alasan'],
            'file_surat'     => $fileSurat,
            'status'         => 'pending',
        ]);

        return redirect()->route('karyawan.cuti.index')
            ->with('success', 'Pengajuan cuti berhasil dikirim! Menunggu persetujuan admin.');
    }
}
