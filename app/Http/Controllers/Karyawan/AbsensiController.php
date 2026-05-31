<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Karyawan\ClockInRequest;
use App\Http\Requests\Karyawan\ClockOutRequest;
use App\Models\Absensi;
use App\Models\LokasiKantor;
use App\Services\AbsensiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller Karyawan AbsensiController
 * Menangani proses clock-in dan clock-out berbasis face recognition
 * Semua validasi (liveness, GPS, face match) dilakukan sebelum menyimpan
 */
class AbsensiController extends Controller
{
    public function __construct(
        private AbsensiService $absensiService
    ) {}

    /**
     * Tampilkan halaman absensi (clock-in / clock-out)
     * Halaman ini berisi feed webcam, status absensi hari ini, dan tombol clock-in/out
     */
    public function index(): View
    {
        $karyawan = Auth::user()->karyawan;

        // Ambil status absensi hari ini
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        // Tentukan mode: clock-in atau clock-out
        $mode = 'clock-in';
        if ($absensiHariIni && $absensiHariIni->jam_masuk && !$absensiHariIni->jam_keluar) {
            $mode = 'clock-out';
        } elseif ($absensiHariIni && $absensiHariIni->jam_masuk && $absensiHariIni->jam_keluar) {
            $mode = 'selesai'; // Sudah clock-in dan clock-out
        }

        $shiftHariIni = $karyawan->getShiftAktif(today());

        $sudahTerdaftar = !empty($karyawan->face_descriptor);
        $lokasiKantor   = LokasiKantor::first();

        return view('karyawan.absensi.index', compact(
            'karyawan',
            'absensiHariIni',
            'shiftHariIni',
            'mode',
            'sudahTerdaftar',
            'lokasiKantor'
        ));
    }

    /**
     * Proses clock-in karyawan via AJAX
     * Menerima data foto (base64), koordinat GPS, dan status validasi dari frontend
     *
     * Response JSON:
     * { success: true|false, message: string, data: object }
     */
    public function clockIn(ClockInRequest $request): JsonResponse
    {
        $karyawan = Auth::user()->karyawan;

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        // Siapkan payload yang akan dikirim ke service
        $payload = [
            'foto_base64'     => $request->foto_base64,
            'latitude'        => (float) $request->latitude,
            'longitude'       => (float) $request->longitude,
            'accuracy'        => (float) $request->accuracy,
            'status_liveness' => $request->status_liveness,
            'face_match_id'   => $request->face_match_id,
        ];

        // Delegasikan proses ke AbsensiService
        $hasil = $this->absensiService->prosesClockIn($karyawan, $payload);

        $statusCode = $hasil['success'] ? 200 : 422;

        return response()->json($hasil, $statusCode);
    }

    /**
     * Proses clock-out karyawan via AJAX
     * Alur sama dengan clock-in tapi hanya memperbarui jam_keluar
     */
    public function clockOut(ClockOutRequest $request): JsonResponse
    {
        $karyawan = Auth::user()->karyawan;

        if (!$karyawan) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
            ], 404);
        }

        $payload = [
            'foto_base64'     => $request->foto_base64,
            'latitude'        => (float) $request->latitude,
            'longitude'       => (float) $request->longitude,
            'accuracy'        => (float) $request->accuracy,
            'status_liveness' => $request->status_liveness,
            'face_match_id'   => $request->face_match_id,
        ];

        $hasil = $this->absensiService->prosesClockOut($karyawan, $payload);

        $statusCode = $hasil['success'] ? 200 : 422;

        return response()->json($hasil, $statusCode);
    }
}
