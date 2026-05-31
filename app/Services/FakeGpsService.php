<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Service FakeGpsService
 * Mendeteksi dan memvalidasi kemungkinan manipulasi GPS (fake GPS)
 * menggunakan beberapa strategi validasi
 */
class FakeGpsService
{
    // Threshold akurasi GPS yang mencurigakan (dalam meter)
    private const AKURASI_SUSPICIOUS = 100;
    private const AKURASI_BLOCKED    = 150;

    // Jarak maksimum yang masuk akal antara dua lokasi
    // dalam kurun waktu singkat (dalam kilometer)
    private const MAX_JARAK_KM = 50;

    // Jumlah absensi terakhir yang dijadikan pembanding
    private const JUMLAH_RIWAYAT = 3;

    /**
     * Validasi koordinat GPS yang dikirim karyawan
     *
     * @param Karyawan $karyawan  — Karyawan yang melakukan absensi
     * @param float    $latitude  — Koordinat latitude
     * @param float    $longitude — Koordinat longitude
     * @param float    $accuracy  — Akurasi GPS dalam meter
     * @return array              — ['status' => 'clean'|'suspicious'|'blocked', 'alasan' => string]
     */
    public function validasi(
        Karyawan $karyawan,
        float $latitude,
        float $longitude,
        float $accuracy
    ): array {
        // === VALIDASI 1: Cek akurasi GPS ===
        // Akurasi yang sangat buruk (> 150m) sering terjadi pada emulator atau fake GPS
        if ($accuracy > self::AKURASI_BLOCKED) {
            Log::warning("GPS blocked - akurasi terlalu rendah", [
                'karyawan_id' => $karyawan->id,
                'accuracy'    => $accuracy,
            ]);

            return [
                'status' => 'blocked',
                'alasan' => "Akurasi GPS terlalu rendah ({$accuracy}m). Kemungkinan menggunakan fake GPS.",
            ];
        }

        // Akurasi antara 100-150m dianggap mencurigakan
        if ($accuracy > self::AKURASI_SUSPICIOUS) {
            Log::notice("GPS suspicious - akurasi rendah", [
                'karyawan_id' => $karyawan->id,
                'accuracy'    => $accuracy,
            ]);

            return [
                'status' => 'suspicious',
                'alasan' => "Akurasi GPS mencurigakan ({$accuracy}m).",
            ];
        }

        // === VALIDASI 2: Cek konsistensi lokasi dengan riwayat absensi ===
        $riwayat = Absensi::where('karyawan_id', $karyawan->id)
            ->whereNotNull('lat_masuk')
            ->where('status_fake_gps', 'clean') // Hanya bandingkan dengan absensi yang clean
            ->orderBy('created_at', 'desc')
            ->take(self::JUMLAH_RIWAYAT)
            ->get();

        foreach ($riwayat as $absensiLama) {
            // Hitung selisih waktu dalam jam antara absensi ini dan yang lama
            $selisihJam = Carbon::parse($absensiLama->created_at)
                ->diffInHours(Carbon::now());

            if ($selisihJam < 1) {
                // Jika kurang dari 1 jam, hitung jarak dengan lokasi lama
                $jarak = $this->hitungJarak(
                    lat1: $absensiLama->lat_masuk,
                    lng1: $absensiLama->lng_masuk,
                    lat2: $latitude,
                    lng2: $longitude
                );

                if ($jarak > self::MAX_JARAK_KM) {
                    Log::warning("GPS suspicious - perpindahan tidak masuk akal", [
                        'karyawan_id' => $karyawan->id,
                        'jarak_km'    => round($jarak, 2),
                        'selisih_jam' => $selisihJam,
                    ]);

                    return [
                        'status' => 'suspicious',
                        'alasan' => sprintf(
                            "Terdeteksi perpindahan lokasi %.1f km dalam waktu %d jam. Mencurigakan.",
                            $jarak,
                            $selisihJam
                        ),
                    ];
                }
            }
        }

        // === VALIDASI 3: Cek koordinat tidak nol atau tidak valid ===
        if ($latitude === 0.0 && $longitude === 0.0) {
            return [
                'status' => 'blocked',
                'alasan' => 'Koordinat GPS tidak valid (0, 0).',
            ];
        }

        // Semua validasi lulus
        return [
            'status' => 'clean',
            'alasan' => 'Lokasi GPS valid.',
        ];
    }

    /**
     * Hitung jarak antara dua koordinat GPS menggunakan formula Haversine
     * Formula ini memperhitungkan kelengkungan bumi
     *
     * @param float $lat1 — Latitude titik 1
     * @param float $lng1 — Longitude titik 1
     * @param float $lat2 — Latitude titik 2
     * @param float $lng2 — Longitude titik 2
     * @return float      — Jarak dalam kilometer
     */
    public function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $radius = 6371; // Radius bumi dalam kilometer

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $radius * $c;
    }
}
