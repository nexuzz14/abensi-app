<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tambahkan kolom role ke tabel users
 * Kolom role digunakan untuk membedakan admin dan karyawan
 */
return new class extends Migration
{
    /**
     * Jalankan migration (buat perubahan ke database)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom role setelah kolom email
            // Nilai default 'karyawan' agar user biasa tidak perlu set manual
            $table->enum('role', ['admin', 'karyawan'])->default('karyawan')->after('email');
        });
    }

    /**
     * Batalkan migration (kembalikan ke kondisi semula)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
