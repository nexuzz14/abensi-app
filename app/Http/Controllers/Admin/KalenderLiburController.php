<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKalenderLiburRequest;
use App\Models\KalenderLibur;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller Admin KalenderLiburController
 * Menangani input dan manajemen hari libur (single date dan range date)
 */
class KalenderLiburController extends Controller
{
    /**
     * Tampilkan daftar hari libur beserta form input
     */
    public function index(): View
    {
        // Tampilkan libur bulan ini dan 3 bulan ke depan
        $libur = KalenderLibur::where('tanggal', '>=', Carbon::now()->startOfMonth())
            ->orderBy('tanggal')
            ->get()
            ->groupBy(fn ($item) => $item->tanggal->format('Y-m')); // Kelompokkan per bulan

        // Libur yang sudah lewat (tahun ini) untuk referensi
        $liburLalu = KalenderLibur::whereYear('tanggal', now()->year)
            ->where('tanggal', '<', Carbon::now()->startOfMonth())
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.kalender-libur.index', compact('libur', 'liburLalu'));
    }

    /**
     * Simpan satu atau beberapa hari libur (mendukung range date)
     * Admin bisa input satu tanggal atau range dari-sampai
     */
    public function store(StoreKalenderLiburRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $jumlahDibuat = 0;

        if ($request->tipe === 'range') {
            // Input range date: tambahkan setiap hari dalam rentang
            $tanggalMulai   = Carbon::parse($validated['tanggal_mulai']);
            $tanggalSelesai = Carbon::parse($validated['tanggal_selesai']);

            $current = $tanggalMulai->copy();

            while ($current->lte($tanggalSelesai)) {
                KalenderLibur::updateOrCreate(
                    ['tanggal' => $current->toDateString()],
                    [
                        'keterangan' => $validated['keterangan'],
                        'jenis'      => $validated['jenis'],
                    ]
                );
                $jumlahDibuat++;
                $current->addDay();
            }

            $pesan = "Berhasil menambahkan {$jumlahDibuat} hari libur dari {$tanggalMulai->format('d/m/Y')} sampai {$tanggalSelesai->format('d/m/Y')}.";
        } else {
            // Input single date
            KalenderLibur::updateOrCreate(
                ['tanggal' => $validated['tanggal']],
                [
                    'keterangan' => $validated['keterangan'],
                    'jenis'      => $validated['jenis'],
                ]
            );

            $pesan = "Hari libur tanggal " . Carbon::parse($validated['tanggal'])->format('d/m/Y') . " berhasil ditambahkan.";
        }

        return redirect()->route('admin.kalender-libur.index')
            ->with('success', $pesan);
    }

    /**
     * Hapus hari libur dari database
     */
    public function destroy(KalenderLibur $kalenderLibur): RedirectResponse
    {
        $tanggal = $kalenderLibur->tanggal->format('d/m/Y');
        $kalenderLibur->delete();

        return redirect()->route('admin.kalender-libur.index')
            ->with('success', "Hari libur tanggal {$tanggal} berhasil dihapus.");
    }
}
