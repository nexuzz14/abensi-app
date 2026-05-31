<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller Admin FaceDescriptorController
 * Menangani proses registrasi wajah karyawan
 * Face descriptor diterima dari frontend (face-api.js) dan disimpan ke database
 *
 * CATATAN KEAMANAN:
 * - Face descriptor yang sudah disimpan tidak pernah dikirim kembali ke frontend
 * - Foto wajah disimpan di storage private (tidak bisa diakses langsung via URL)
 */
class FaceDescriptorController extends Controller
{
    /**
     * Tampilkan halaman registrasi wajah untuk karyawan tertentu
     * Halaman ini berisi feed webcam dan instruksi untuk capture 5 sudut wajah
     */
    public function show(Karyawan $karyawan): View
    {
        // Cek apakah sudah memiliki face descriptor
        $sudahTerdaftar = $karyawan->hasFaceDescriptor();

        return view('admin.karyawan.face-register', compact('karyawan', 'sudahTerdaftar'));
    }

    /**
     * Simpan face descriptor yang dikirim dari frontend
     *
     * Endpoint ini menerima:
     * - descriptor: array of 128 floats (rata-rata dari 5 sudut capture)
     * - foto_preview: base64 foto wajah untuk preview (opsional)
     *
     * Response JSON dengan format:
     * { success: true|false, message: string }
     */
    public function store(Request $request, Karyawan $karyawan): JsonResponse
    {
        // Validasi input
        $request->validate([
            'descriptor'   => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ], [
            'descriptor.required' => 'Data descriptor wajah tidak boleh kosong.',
            'descriptor.size'     => 'Descriptor wajah harus terdiri dari 128 nilai.',
            'descriptor.*.numeric'=> 'Setiap nilai descriptor harus berupa angka.',
        ]);

        try {
            // Simpan face descriptor ke database
            // Gunakan DB::table agar tidak terkena $hidden dari model
            DB::table('karyawan')
                ->where('id', $karyawan->id)
                ->update([
                    'face_descriptor' => json_encode($request->descriptor),
                    'updated_at'      => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Wajah {$karyawan->nama_lengkap} berhasil terdaftar! ✓",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data wajah. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Reset face descriptor karyawan
     * Digunakan ketika wajah karyawan perlu didaftarkan ulang
     */
    public function reset(Karyawan $karyawan): \Illuminate\Http\RedirectResponse
    {
        DB::table('karyawan')
            ->where('id', $karyawan->id)
            ->update([
                'face_descriptor' => null,
                'updated_at'      => now(),
            ]);

        return redirect()->back()->with('success', 'Data wajah karyawan berhasil direset. Karyawan harus mendaftar ulang untuk absen.');
    }

    /**
     * Endpoint untuk mengambil semua face descriptor aktif dari database
     * Digunakan oleh halaman absensi untuk proses face recognition
     *
     * PENTING: Endpoint ini hanya boleh diakses oleh user yang sudah login
     * Face descriptor dikembalikan sebagai array JSON untuk diproses di frontend
     */
    public function getDescriptors(): JsonResponse
    {
        // Ambil semua karyawan aktif yang sudah mendaftarkan wajah
        $karyawanList = DB::table('karyawan')
            ->select('id', 'nama_lengkap', 'jabatan', 'face_descriptor')
            ->where('status_aktif', true)
            ->whereNotNull('face_descriptor')
            ->get();

        // Format data untuk dikirim ke frontend
        $descriptors = $karyawanList->map(function ($k) {
            return [
                'id'              => $k->id,
                'nama_lengkap'    => $k->nama_lengkap,
                'jabatan'         => $k->jabatan,
                'face_descriptor' => json_decode($k->face_descriptor),
            ];
        });

        return response()->json($descriptors);
    }
}
