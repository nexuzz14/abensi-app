<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

/**
 * Seeder: Buat data shift kerja default (Pagi, Siang, Malam)
 */
class ShiftSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        $shifts = [
            [
                'nama_shift'      => 'Shift Pagi',
                'jam_masuk'       => '08:00:00',
                'jam_keluar'      => '16:00:00',
                'toleransi_menit' => 15,
            ],
            [
                'nama_shift'      => 'Shift Siang',
                'jam_masuk'       => '12:00:00',
                'jam_keluar'      => '20:00:00',
                'toleransi_menit' => 15,
            ],
            [
                'nama_shift'      => 'Shift Malam',
                'jam_masuk'       => '20:00:00',
                'jam_keluar'      => '04:00:00',
                'toleransi_menit' => 15,
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['nama_shift' => $shift['nama_shift']],
                $shift
            );
        }

        $this->command->info('✅ ShiftSeeder: Berhasil membuat 3 shift (Pagi, Siang, Malam)');
    }
}
