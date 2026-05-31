<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKaryawanRequest;
use App\Http\Requests\Admin\UpdateKaryawanRequest;
use App\Models\Karyawan;
use App\Models\KaryawanShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Admin KaryawanController
 * Menangani CRUD data karyawan beserta upload foto dan assign shift
 */
class KaryawanController extends Controller
{
    /**
     * Tampilkan daftar semua karyawan dengan fitur search dan filter no_hp
     */
    public function index(Request $request): View
    {
        $karyawan = Karyawan::with('user')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('nip', 'like', "%{$search}%")
                          ->orWhere('jabatan', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status_aktif', $status === 'aktif');
            })
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        return view('admin.karyawan.index', compact('karyawan'));
    }

    /**
     * Tampilkan form tambah karyawan baru
     */
    public function create(): View
    {
        $shifts = Shift::orderBy('jam_masuk')->get();
        
        $latestKaryawan = Karyawan::latest('id')->first();
        $nextId = $latestKaryawan ? $latestKaryawan->id + 1 : 1;
        $nip_baru = 'KRY-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('admin.karyawan.create', compact('shifts', 'nip_baru'));
    }

    /**
     * Simpan data karyawan baru ke database
     * Juga membuat akun user untuk karyawan tersebut dan mengassign shift
     */
    public function store(StoreKaryawanRequest $request): RedirectResponse
    {
        // Gunakan database transaction agar jika ada error, semua rollback
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->nama_lengkap,
                'email'    => strtolower($request->nip) . '@internal.app',
                'password' => Hash::make($request->password),
                'role'     => 'karyawan',
            ]);

            $pathFoto = null;
            if ($request->hasFile('foto')) {
                $pathFoto = $request->file('foto')->store('foto-profil', 'public');
            }

            $karyawan = Karyawan::create([
                'user_id'      => $user->id,
                'nip'          => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'jabatan'      => $request->jabatan,
                'no_hp'        => $request->no_hp,
                'foto'         => $pathFoto,
                'status_aktif' => true,
            ]);

            // 4. Assign shift ke karyawan
            if ($request->shift_id) {
                KaryawanShift::create([
                    'karyawan_id'    => $karyawan->id,
                    'shift_id'       => $request->shift_id,
                    'tanggal_berlaku' => $request->tanggal_berlaku ?? today(),
                    'keterangan'     => 'Shift awal masuk kerja',
                ]);
            }
        });

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan. Jangan lupa daftarkan wajah karyawan!');
    }

    /**
     * Tampilkan detail karyawan
     */
    public function show(Karyawan $karyawan): View
    {
        $karyawan->load(['user', 'karyawanShift.shift', 'absensi' => function ($q) {
            $q->orderBy('tanggal', 'desc')->take(30);
        }]);

        return view('admin.karyawan.show', compact('karyawan'));
    }

    /**
     * Tampilkan form edit data karyawan
     */
    public function edit(Karyawan $karyawan): View
    {
        $karyawan->load('user');
        $shifts        = Shift::orderBy('jam_masuk')->get();
        $shiftAktif    = $karyawan->getShiftAktif();

        return view('admin.karyawan.edit', compact('karyawan', 'shifts', 'shiftAktif'));
    }

    /**
     * Update data karyawan yang sudah ada
     * Face descriptor tidak ikut diubah kecuali admin memilih reset
     */
    public function update(UpdateKaryawanRequest $request, Karyawan $karyawan): RedirectResponse
    {
        DB::transaction(function () use ($request, $karyawan) {
            // 1. Update data user (name dan email-internal)
            $karyawan->user->update([
                'name'  => $request->nama_lengkap,
                'email' => strtolower($request->nip) . '@internal.app',
            ]);

            // Update password hanya jika diisi
            if ($request->filled('password')) {
                $karyawan->user->update([
                    'password' => Hash::make($request->password),
                ]);
            }

            // 2. Upload foto baru jika ada
            if ($request->hasFile('foto')) {
                // Hapus foto lama jika ada
                if ($karyawan->foto) {
                    Storage::disk('public')->delete($karyawan->foto);
                }
                $pathFoto = $request->file('foto')->store('foto-profil', 'public');
            }

            $karyawan->update([
                'nip'          => $request->nip,
                'nama_lengkap' => $request->nama_lengkap,
                'jabatan'      => $request->jabatan,
                'no_hp'        => $request->no_hp,
                'foto'         => $pathFoto ?? $karyawan->foto,
                'status_aktif' => $request->boolean('status_aktif'),
            ]);

            // 4. Update shift jika diubah
            if ($request->shift_id) {
                $shiftAktif = $karyawan->getShiftAktif();

                // Hanya buat record baru jika shift berbeda
                if (!$shiftAktif || $shiftAktif->id != $request->shift_id) {
                    KaryawanShift::create([
                        'karyawan_id'    => $karyawan->id,
                        'shift_id'       => $request->shift_id,
                        'tanggal_berlaku' => today(),
                        'keterangan'     => 'Perubahan shift',
                    ]);
                }
            }

            // 5. Reset face descriptor jika diminta admin
            if ($request->boolean('reset_face')) {
                $karyawan->update(['face_descriptor' => null]);
            }
        });

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    /**
     * Nonaktifkan karyawan (soft delete — ubah status_aktif menjadi false)
     * Data tetap tersimpan di database untuk keperluan riwayat
     */
    public function destroy(Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update(['status_aktif' => false]);

        // Juga nonaktifkan akun user agar tidak bisa login
        $karyawan->user->update(['email' => 'nonaktif_' . time() . '_' . $karyawan->user->email]);

        return redirect()->route('admin.karyawan.index')
            ->with('success', "Karyawan {$karyawan->nama_lengkap} telah dinonaktifkan.");
    }

    /**
     * Aktifkan kembali karyawan yang sudah dinonaktifkan
     */
    public function aktifkan(Karyawan $karyawan): RedirectResponse
    {
        $karyawan->update(['status_aktif' => true]);

        return redirect()->back()
            ->with('success', "Karyawan {$karyawan->nama_lengkap} berhasil diaktifkan kembali.");
    }
}
