<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * DatabaseSeeder: Mengorkestrasi semua seeder
 * Urutan penting: Users → Shifts → Karyawan → Absensi
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan semua seeder dalam urutan yang benar
     */
    public function run(): void
    {
        $this->command->info('🚀 Mulai menjalankan semua seeder...');
        $this->command->newLine();

        // 1. Buat user (admin dan karyawan)
        $this->call(UserSeeder::class);

        // 2. Buat master shift kerja
        $this->call(ShiftSeeder::class);

        // 3. Buat data karyawan dan assign shift (DIHAPUS UNTUK CLIENT)
        // $this->call(KaryawanSeeder::class);

        // 4. Buat data absensi dummy 30 hari terakhir (DIHAPUS UNTUK CLIENT)
        // $this->call(AbsensiSeeder::class);

        $this->command->newLine();
        $this->command->info('🎉 Semua seeder berhasil dijalankan!');
        $this->command->newLine();
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('  Akun Admin  : admin@sistem.com');
        $this->command->line('  Password    : Admin123!');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line('  Akun Karyawan (contoh): budi.santoso@perusahaan.com');
        $this->command->line('  Password Karyawan     : Karyawan123!');
        $this->command->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    }
}
