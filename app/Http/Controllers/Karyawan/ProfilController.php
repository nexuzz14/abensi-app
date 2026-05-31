<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller Karyawan ProfilController
 * Menangani perubahan password karyawan
 * Dilengkapi validasi password lama sebelum bisa menggantinya
 */
class ProfilController extends Controller
{
    /**
     * Tampilkan halaman profil dan form ubah password
     */
    public function index(): View
    {
        $user     = Auth::user();
        $karyawan = $user->karyawan;

        return view('karyawan.profil.index', compact('user', 'karyawan'));
    }

    /**
     * Proses perubahan profil karyawan (nama, no_hp, foto)
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;

        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'no_hp'        => ['nullable', 'string', 'max:20'],
            'foto'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        $karyawanData = [
            'nama_lengkap' => $request->nama_lengkap,
            'no_hp'        => $request->no_hp,
        ];

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($karyawan->foto && Storage::disk('public')->exists($karyawan->foto)) {
                Storage::disk('public')->delete($karyawan->foto);
            }
            
            $fotoPath = $request->file('foto')->store('foto-profil', 'public');
            $karyawanData['foto'] = $fotoPath;
        }

        // Update Karyawan
        $karyawan->update($karyawanData);
        
        // Update User name
        $user->update([
            'name' => $request->nama_lengkap
        ]);

        return redirect()->route('karyawan.profil.index')
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Proses perubahan password karyawan
     * Validasi password lama sebelum menyimpan password baru
     */
    public function ubahPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password_lama'         => ['required', 'string'],
            'password_baru'         => ['required', 'string', 'min:8', 'confirmed'],
            'password_baru_confirmation' => ['required'],
        ], [
            'password_lama.required'            => 'Password lama wajib diisi.',
            'password_baru.required'            => 'Password baru wajib diisi.',
            'password_baru.min'                 => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed'           => 'Konfirmasi password baru tidak sesuai.',
            'password_baru_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);

        $user = Auth::user();

        // Verifikasi password lama
        if (!Hash::check($request->password_lama, $user->password)) {
            return redirect()->back()
                ->withErrors(['password_lama' => 'Password lama yang Anda masukkan salah.']);
        }

        // Cek apakah password baru sama dengan password lama
        if (Hash::check($request->password_baru, $user->password)) {
            return redirect()->back()
                ->withErrors(['password_baru' => 'Password baru tidak boleh sama dengan password lama.']);
        }

        // Simpan password baru
        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()->route('karyawan.profil.index')
            ->with('success', 'Password berhasil diubah! Silakan login kembali dengan password baru.');
    }

    /**
     * Tampilkan halaman registrasi wajah mandiri karyawan
     */
    public function faceRegister(): View
    {
        $karyawan = Auth::user()->karyawan;
        $sudahTerdaftar = !empty($karyawan->face_descriptor);
        
        return view('karyawan.profil.face-register', compact('karyawan', 'sudahTerdaftar'));
    }

    /**
     * Simpan data face descriptor karyawan
     */
    public function faceStore(Request $request)
    {
        $request->validate([
            'descriptor' => ['required', 'array'],
        ]);

        $karyawan = Auth::user()->karyawan;

        try {
            $karyawan->update([
                'face_descriptor' => $request->descriptor, // Cast 'array' di model sudah handle json_encode otomatis
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data wajah berhasil didaftarkan.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data wajah: ' . $e->getMessage(),
            ], 500);
        }
    }
}
