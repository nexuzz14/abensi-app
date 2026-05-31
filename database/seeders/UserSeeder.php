<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder: Buat akun admin dan user karyawan untuk keperluan demo
 */
class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder
     */
    public function run(): void
    {
        // ========================
        // AKUN ADMIN DEFAULT
        // ========================
        User::updateOrCreate(
            ['email' => 'admin@sistem.com'],
            [
                'name'     => 'Administrator',
                'email'    => 'admin@sistem.com',
                'password' => Hash::make('Admin123!'),
                'role'     => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✅ UserSeeder: Berhasil membuat 1 admin');
    }
}
