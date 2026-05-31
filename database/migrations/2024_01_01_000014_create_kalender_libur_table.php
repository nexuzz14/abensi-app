<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel kalender_libur
 * Menyimpan daftar hari libur nasional dan cuti bersama
 * Data ini digunakan untuk validasi pengajuan cuti dan laporan kehadiran
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('kalender_libur', function (Blueprint $table) {
            $table->id();

            // Tanggal hari libur
            $table->date('tanggal')->unique();

            // Keterangan nama hari libur, misal: "Hari Raya Idul Fitri"
            $table->string('keterangan', 200);

            // Jenis libur: nasional (libur resmi) atau bersama (himbauan pemerintah)
            $table->enum('jenis', ['nasional', 'bersama'])->default('nasional');

            $table->timestamps();

            // Index untuk query berdasarkan tanggal
            $table->index('tanggal');
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('kalender_libur');
    }
};
