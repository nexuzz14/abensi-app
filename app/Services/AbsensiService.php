<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KalenderLibur;
use App\Models\Shift;
use App\Models\LokasiKantor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Service AbsensiService
 * Menangani semua logika bisnis terkait absensi karyawan
 * Controller hanya memanggil method dari service ini, tidak menangani logika sendiri
 */
class AbsensiService
{
    /**
     * Injeksi dependency FakeGpsService untuk validasi GPS
     */
    public function __construct(
        private FakeGpsService $fakeGpsService
    ) {}

    // ========================
    // CLOCK IN
    // ========================

    /**
     * Proses clock-in karyawan
     *
     * @param Karyawan $karyawan      — Karyawan yang akan clock-in
     * @param array    $payload       — Data dari request (foto, koordinat, dll)
     * @return array                  — Array berisi status dan pesan hasil
     */
    public function prosesClockIn(Karyawan $karyawan, array $payload): array
    {
        $sekarang = Carbon::now();
        $tanggal  = $sekarang->toDateString();

        // === VALIDASI 1: Cek apakah sudah clock-in hari ini ===
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggal)
            ->first();

        if ($absensiHariIni && $absensiHariIni->jam_masuk) {
            return [
                'success' => false,
                'message' => 'Anda sudah melakukan clock-in hari ini pada pukul ' .
                             substr($absensiHariIni->jam_masuk, 0, 5) . '.',
            ];
        }

        // === VALIDASI 2: Cek apakah hari ini adalah hari libur ===
        if (KalenderLibur::isHariLibur($tanggal)) {
            return [
                'success' => false,
                'message' => 'Hari ini adalah hari libur. Tidak perlu melakukan absensi.',
            ];
        }

        // === VALIDASI 3: Cek liveness detection ===
        if ($payload['status_liveness'] !== 'passed') {
            return [
                'success' => false,
                'message' => 'Verifikasi liveness gagal. Silakan kedip mata 2x untuk verifikasi.',
            ];
        }

        // === VALIDASI 4: Validasi GPS (anti-fake GPS) ===
        $validasiGps = $this->fakeGpsService->validasi(
            karyawan:  $karyawan,
            latitude:  $payload['latitude'],
            longitude: $payload['longitude'],
            accuracy:  $payload['accuracy'],
        );

        if ($validasiGps['status'] === 'blocked') {
            return [
                'success' => false,
                'message' => 'Lokasi GPS Anda terdeteksi tidak valid (fake GPS). Absensi ditolak.',
            ];
        }

        // === VALIDASI 5: Geofencing Lokasi Kantor ===
        $lokasiKantor = LokasiKantor::first();
        if ($lokasiKantor) {
            $jarak = $this->fakeGpsService->hitungJarak(
                $lokasiKantor->latitude,
                $lokasiKantor->longitude,
                $payload['latitude'],
                $payload['longitude']
            );
            
            // Convert to meters
            $jarakMeter = $jarak * 1000;

            if ($jarakMeter > $lokasiKantor->radius_meter) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Anda berada di luar jangkauan area absen. Jarak Anda: %d meter (Maks: %d meter).',
                        round($jarakMeter),
                        $lokasiKantor->radius_meter
                    ),
                ];
            }
        }

        // === PROSES: Hitung status kehadiran ===
        $statusKehadiran = $this->hitungStatusKehadiran($karyawan, $sekarang);

        // === PROSES: Simpan foto wajah ke storage private ===
        $pathFoto = $this->simpanFotoWajah($payload['foto_base64'], $karyawan->id, 'masuk', $tanggal);

        // === PROSES: Buat atau perbarui record absensi ===
        $absensi = Absensi::updateOrCreate(
            ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal],
            [
                'jam_masuk'        => $sekarang->format('H:i:s'),
                'foto_masuk'       => $pathFoto,
                'lat_masuk'        => $payload['latitude'],
                'lng_masuk'        => $payload['longitude'],
                'accuracy_masuk'   => $payload['accuracy'],
                'status_liveness'  => $payload['status_liveness'],
                'status_fake_gps'  => $validasiGps['status'],
                'status_kehadiran' => $statusKehadiran,
            ]
        );

        Log::info("Clock-in berhasil", [
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $tanggal,
            'jam'         => $sekarang->format('H:i:s'),
            'status'      => $statusKehadiran,
        ]);

        return [
            'success'         => true,
            'message'         => 'Clock-in berhasil! ' . $this->pesanStatusKehadiran($statusKehadiran),
            'status_kehadiran' => $statusKehadiran,
            'jam_masuk'       => $sekarang->format('H:i'),
        ];
    }

    // ========================
    // CLOCK OUT
    // ========================

    /**
     * Proses clock-out karyawan
     *
     * @param Karyawan $karyawan  — Karyawan yang akan clock-out
     * @param array    $payload   — Data dari request (foto, koordinat, dll)
     * @return array              — Array berisi status dan pesan hasil
     */
    public function prosesClockOut(Karyawan $karyawan, array $payload): array
    {
        $sekarang = Carbon::now();
        $tanggal  = $sekarang->toDateString();

        // === VALIDASI 1: Cek apakah sudah clock-in hari ini ===
        $absensiHariIni = Absensi::where('karyawan_id', $karyawan->id)
            ->where('tanggal', $tanggal)
            ->first();

        if (!$absensiHariIni || !$absensiHariIni->jam_masuk) {
            return [
                'success' => false,
                'message' => 'Anda belum melakukan clock-in hari ini. Silakan clock-in terlebih dahulu.',
            ];
        }

        // VALIDASI 2: Cek apakah sudah clock-out (Dihapus sesuai permintaan revisi agar bisa ditumpuk)
        // Karyawan diperbolehkan clock-out berkali-kali jika terjadi error/kesalahan, data lama akan tertimpa.

        // === VALIDASI 3: Cek liveness detection ===
        if ($payload['status_liveness'] !== 'passed') {
            return [
                'success' => false,
                'message' => 'Verifikasi liveness gagal. Silakan kedip mata 2x untuk verifikasi.',
            ];
        }

        // === VALIDASI 4: Validasi GPS ===
        $validasiGps = $this->fakeGpsService->validasi(
            karyawan:  $karyawan,
            latitude:  $payload['latitude'],
            longitude: $payload['longitude'],
            accuracy:  $payload['accuracy'],
        );

        // === VALIDASI 5: Geofencing Lokasi Kantor ===
        $lokasiKantor = LokasiKantor::first();
        if ($lokasiKantor) {
            $jarak = $this->fakeGpsService->hitungJarak(
                $lokasiKantor->latitude,
                $lokasiKantor->longitude,
                $payload['latitude'],
                $payload['longitude']
            );
            
            // Convert to meters
            $jarakMeter = $jarak * 1000;

            if ($jarakMeter > $lokasiKantor->radius_meter) {
                return [
                    'success' => false,
                    'message' => sprintf(
                        'Anda berada di luar jangkauan area absen. Jarak Anda: %d meter (Maks: %d meter).',
                        round($jarakMeter),
                        $lokasiKantor->radius_meter
                    ),
                ];
            }
        }

        // === PROSES: Simpan foto wajah ===
        $pathFoto = $this->simpanFotoWajah($payload['foto_base64'], $karyawan->id, 'keluar', $tanggal);

        // === PROSES: Update record absensi ===
        $absensiHariIni->update([
            'jam_keluar'      => $sekarang->format('H:i:s'),
            'foto_keluar'     => $pathFoto,
            'lat_keluar'      => $payload['latitude'],
            'lng_keluar'      => $payload['longitude'],
            'accuracy_keluar' => $payload['accuracy'],
        ]);

        Log::info("Clock-out berhasil", [
            'karyawan_id' => $karyawan->id,
            'tanggal'     => $tanggal,
            'jam'         => $sekarang->format('H:i:s'),
        ]);

        return [
            'success'    => true,
            'message'    => 'Clock-out berhasil! Selamat beristirahat.',
            'jam_keluar' => $sekarang->format('H:i'),
        ];
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Hitung status kehadiran berdasarkan jam masuk vs shift karyawan
     *
     * @param Karyawan $karyawan  — Karyawan yang diabsen
     * @param Carbon   $jamMasuk  — Waktu aktual clock-in
     * @return string             — 'hadir', 'terlambat'
     */
    public function hitungStatusKehadiran(Karyawan $karyawan, Carbon $jamMasuk): string
    {
        $shift = $karyawan->getShiftAktif($jamMasuk->toDateString());

        if (!$shift) {
            // Jika tidak ada shift terdaftar, anggap hadir
            return 'hadir';
        }

        // Batas waktu masuk = jam_masuk shift + toleransi
        $batasWaktu = Carbon::parse($jamMasuk->toDateString() . ' ' . $shift->jam_masuk)
            ->addMinutes($shift->toleransi_menit);

        return $jamMasuk->lte($batasWaktu) ? 'hadir' : 'terlambat';
    }

    /**
     * Simpan foto wajah dari base64 ke storage private
     *
     * @param string $base64    — Data foto dalam format base64
     * @param int    $karyawanId
     * @param string $tipe      — 'masuk' atau 'keluar'
     * @param string $tanggal   — Format YYYY-MM-DD
     * @return string|null      — Path file yang disimpan, atau null jika gagal
     */
    private function simpanFotoWajah(string $base64, int $karyawanId, string $tipe, string $tanggal): ?string
    {
        try {
            // Hapus prefix "data:image/jpeg;base64," dari string base64
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return null;
            }

            // Path: faces/karyawan_id/tanggal/tipe_timestamp.jpg
            $fileName = "faces/{$karyawanId}/{$tanggal}/{$tipe}_" . time() . '.jpg';

            // Simpan ke storage private (tidak dapat diakses langsung via URL)
            Storage::disk('local')->put($fileName, $imageData);

            return $fileName;
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan foto wajah: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Dapatkan pesan status kehadiran yang informatif untuk karyawan
     */
    private function pesanStatusKehadiran(string $status): string
    {
        return match ($status) {
            'hadir'     => 'Status: Tepat Waktu ✓',
            'terlambat' => 'Catatan: Anda tercatat terlambat.',
            default     => '',
        };
    }

    /**
     * Ambil data absensi karyawan berdasarkan filter
     * Digunakan oleh LaporanController
     *
     * @param array $filter — ['bulan', 'tahun', 'departemen', 'karyawan_id', 'status']
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDataLaporan(array $filter)
    {
        $query = Absensi::with(['karyawan.user'])
            ->when($filter['bulan'] ?? null, function ($q, $bulan) use ($filter) {
                $tahun = $filter['tahun'] ?? now()->year;
                $q->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
            })
            ->when($filter['tanggal_dari'] ?? null, function ($q, $dari) {
                $q->whereDate('tanggal', '>=', $dari);
            })
            ->when($filter['tanggal_sampai'] ?? null, function ($q, $sampai) {
                $q->whereDate('tanggal', '<=', $sampai);
            })
            ->when($filter['karyawan_id'] ?? null, function ($q, $id) {
                $q->where('karyawan_id', $id);
            })
            ->when($filter['status'] ?? null, function ($q, $status) {
                $q->where('status_kehadiran', $status);
            })
            ->orderBy('tanggal', 'desc')
            ->orderBy('karyawan_id');

        return $query->get();
    }
}
