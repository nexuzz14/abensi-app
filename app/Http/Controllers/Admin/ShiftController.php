<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShiftRequest;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller Admin ShiftController
 * Menangani CRUD master data shift kerja
 */
class ShiftController extends Controller
{
    /**
     * Tampilkan daftar semua shift kerja
     */
    public function index(): View
    {
        $shifts = Shift::orderBy('jam_masuk')->get();
        $karyawans = \App\Models\Karyawan::where('status_aktif', true)
            ->with(['karyawanShift' => fn($q) => $q->orderBy('tanggal_berlaku', 'desc')])
            ->get();
        
        // Buat mapping shift_id => Karyawan (hanya shift yang aktif)
        foreach ($shifts as $shift) {
            $shift->active_karyawans = collect();
        }

        foreach ($karyawans as $karyawan) {
            $activeShift = $karyawan->getShiftAktif();
            if ($activeShift) {
                $shift = $shifts->firstWhere('id', $activeShift->id);
                if ($shift) {
                    $shift->active_karyawans->push($karyawan);
                }
            }
        }

        return view('admin.shift.index', compact('shifts'));
    }

    /**
     * Tampilkan form tambah shift baru
     */
    public function create(): View
    {
        return view('admin.shift.create');
    }

    /**
     * Simpan shift baru ke database
     */
    public function store(StoreShiftRequest $request): RedirectResponse
    {
        Shift::create($request->validated());

        return redirect()->route('admin.shift.index')
            ->with('success', "Shift '{$request->nama_shift}' berhasil ditambahkan.");
    }

    /**
     * Tampilkan form edit shift
     */
    public function edit(Shift $shift): View
    {
        return view('admin.shift.edit', compact('shift'));
    }

    /**
     * Update data shift yang sudah ada
     */
    public function update(StoreShiftRequest $request, Shift $shift): RedirectResponse
    {
        $shift->update($request->validated());

        return redirect()->route('admin.shift.index')
            ->with('success', "Shift '{$shift->nama_shift}' berhasil diperbarui.");
    }

    /**
     * Hapus shift dari database
     * Hanya bisa dihapus jika tidak ada karyawan yang menggunakan shift ini
     */
    public function destroy(Shift $shift): RedirectResponse
    {
        // Cek apakah shift masih digunakan oleh karyawan
        if ($shift->karyawanShift()->exists()) {
            return redirect()->back()
                ->with('error', "Shift '{$shift->nama_shift}' tidak bisa dihapus karena masih digunakan oleh karyawan.");
        }

        $namaShift = $shift->nama_shift;
        $shift->delete();

        return redirect()->route('admin.shift.index')
            ->with('success', "Shift '{$namaShift}' berhasil dihapus.");
    }
}
