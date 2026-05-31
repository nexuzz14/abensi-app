<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\KaryawanShift;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: Buat data karyawan dummy beserta penugasan shift
 * Face descriptor tidak diisi karena harus dilakukan melalui webcam
 */
class KaryawanSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // Ambil semua shift yang tersedia
        $shiftPagi   = Shift::where('nama_shift', 'Shift Pagi')->first();
        $shiftSiang  = Shift::where('nama_shift', 'Shift Siang')->first();
        $shiftMalam  = Shift::where('nama_shift', 'Shift Malam')->first();

        // Data karyawan dummy
        $karyawanData = [
            [
                'nip'          => 'EMP001',
                'nama_lengkap' => 'Budi Santoso',
                'jabatan'      => 'Staff IT',
                'no_hp'        => '081234567001',
                'shift'        => $shiftPagi,
            ],
            [
                'nip'          => 'EMP002',
                'nama_lengkap' => 'Siti Rahayu',
                'jabatan'      => 'HR Specialist',
                'no_hp'        => '081234567002',
                'shift'        => $shiftPagi,
            ],
            [
                'nip'          => 'EMP003',
                'nama_lengkap' => 'Ahmad Fauzi',
                'jabatan'      => 'Finance Officer',
                'no_hp'        => '081234567003',
                'shift'        => $shiftSiang,
            ],
            [
                'nip'          => 'EMP004',
                'nama_lengkap' => 'Dewi Lestari',
                'jabatan'      => 'Marketing Staff',
                'no_hp'        => '081234567004',
                'shift'        => $shiftSiang,
            ],
            [
                'nip'          => 'EMP005',
                'nama_lengkap' => 'Eko Prasetyo',
                'jabatan'      => 'Security Officer',
                'no_hp'        => '081234567005',
                'shift'        => $shiftMalam,
            ],
        ];

        foreach ($karyawanData as $data) {
            // Auto-generate email dari NIP
            $email = strtolower($data['nip']) . '@internal.app';
            $user  = User::where('email', $email)->first();

            if (!$user) {
                $this->command->warn("⚠️  User dengan email {$email} tidak ditemukan, skip.");
                continue;
            }

            // Buat atau perbarui data karyawan
            $karyawan = Karyawan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nip'          => $data['nip'],
                    'nama_lengkap' => $data['nama_lengkap'],
                    'jabatan'      => $data['jabatan'],
                    'no_hp'        => $data['no_hp'],
                    'status_aktif' => true,
                    // face_descriptor tidak diisi — harus registrasi via webcam
                ]
            );

            // Assign shift ke karyawan dengan tanggal berlaku 3 bulan lalu
            if ($data['shift']) {
                KaryawanShift::updateOrCreate(
                    [
                        'karyawan_id'    => $karyawan->id,
                        'tanggal_berlaku' => now()->subMonths(3)->toDateString(),
                    ],
                    [
                        'shift_id'    => $data['shift']->id,
                        'keterangan'  => 'Shift awal masuk kerja',
                    ]
                );
            }
        }

        $this->command->info('✅ KaryawanSeeder: Berhasil membuat 5 karyawan dummy');
    }
}
