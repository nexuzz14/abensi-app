<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\KalenderLibur;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder: Buat data absensi dummy untuk 30 hari terakhir
 * Data ini digunakan untuk demo laporan dan dashboard
 */
class AbsensiSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // Ambil semua karyawan aktif
        $semuaKaryawan = Karyawan::with(['karyawanShift.shift'])->where('status_aktif', true)->get();

        if ($semuaKaryawan->isEmpty()) {
            $this->command->warn('⚠️  Tidak ada karyawan aktif, AbsensiSeeder dilewati.');
            return;
        }

        // Tanggal hari libur nasional untuk seeder (contoh)
        $hariLibur = [
            now()->subDays(10)->toDateString(), // Hari libur acak untuk demo
        ];

        // Isi kalender libur jika belum ada
        foreach ($hariLibur as $tanggal) {
            KalenderLibur::updateOrCreate(
                ['tanggal' => $tanggal],
                ['keterangan' => 'Hari Libur Nasional (Demo)', 'jenis' => 'nasional']
            );
        }

        $jumlahDibuat = 0;

        // Loop 30 hari ke belakang
        for ($i = 30; $i >= 1; $i--) {
            $tanggal    = Carbon::today()->subDays($i);
            $hariInWeek = $tanggal->dayOfWeek; // 0=Minggu, 6=Sabtu

            // Skip hari Sabtu (6) dan Minggu (0) — hari libur mingguan
            if ($hariInWeek === 0 || $hariInWeek === 6) {
                continue;
            }

            // Skip hari libur nasional
            if (in_array($tanggal->toDateString(), $hariLibur)) {
                continue;
            }

            // Buat absensi untuk setiap karyawan
            foreach ($semuaKaryawan as $karyawan) {
                // Tentukan shift karyawan pada tanggal ini
                $shift = $karyawan->getShiftAktif($tanggal);

                if (!$shift) {
                    continue; // Skip jika belum ada shift
                }

                // Simulasikan berbagai skenario kehadiran (realistis)
                $random = rand(1, 100);

                if ($random <= 5) {
                    // 5% alpa (tidak hadir tanpa keterangan)
                    Absensi::updateOrCreate(
                        ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal],
                        [
                            'status_kehadiran' => 'alpa',
                            'status_liveness'  => 'skipped',
                            'status_fake_gps'  => 'clean',
                            'keterangan'       => 'Tidak hadir tanpa keterangan',
                        ]
                    );
                } elseif ($random <= 15) {
                    // 10% terlambat
                    $jamMasuk = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_masuk)
                        ->addMinutes(rand(20, 60)); // Terlambat 20-60 menit

                    $jamKeluar = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_keluar);

                    Absensi::updateOrCreate(
                        ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal],
                        [
                            'jam_masuk'        => $jamMasuk->format('H:i:s'),
                            'jam_keluar'       => $jamKeluar->format('H:i:s'),
                            'lat_masuk'        => -6.200000 + (rand(-100, 100) / 10000),
                            'lng_masuk'        => 106.816666 + (rand(-100, 100) / 10000),
                            'accuracy_masuk'   => rand(5, 20),
                            'lat_keluar'       => -6.200000 + (rand(-100, 100) / 10000),
                            'lng_keluar'       => 106.816666 + (rand(-100, 100) / 10000),
                            'accuracy_keluar'  => rand(5, 20),
                            'status_liveness'  => 'passed',
                            'status_fake_gps'  => 'clean',
                            'status_kehadiran' => 'terlambat',
                        ]
                    );
                } else {
                    // 85% hadir tepat waktu
                    $meniTAwal = rand(-10, $shift->toleransi_menit); // -10 (lebih awal) atau dalam toleransi
                    $jamMasuk  = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_masuk)
                        ->addMinutes($meniTAwal);

                    $jamKeluar = Carbon::parse($tanggal->toDateString() . ' ' . $shift->jam_keluar)
                        ->addMinutes(rand(0, 30)); // Kadang lembur sedikit

                    Absensi::updateOrCreate(
                        ['karyawan_id' => $karyawan->id, 'tanggal' => $tanggal],
                        [
                            'jam_masuk'        => $jamMasuk->format('H:i:s'),
                            'jam_keluar'       => $jamKeluar->format('H:i:s'),
                            'lat_masuk'        => -6.200000 + (rand(-100, 100) / 10000),
                            'lng_masuk'        => 106.816666 + (rand(-100, 100) / 10000),
                            'accuracy_masuk'   => rand(5, 20),
                            'lat_keluar'       => -6.200000 + (rand(-100, 100) / 10000),
                            'lng_keluar'       => 106.816666 + (rand(-100, 100) / 10000),
                            'accuracy_keluar'  => rand(5, 20),
                            'status_liveness'  => 'passed',
                            'status_fake_gps'  => 'clean',
                            'status_kehadiran' => 'hadir',
                        ]
                    );
                }

                $jumlahDibuat++;
            }
        }

        $this->command->info("✅ AbsensiSeeder: Berhasil membuat {$jumlahDibuat} record absensi untuk 30 hari terakhir");
    }
}
