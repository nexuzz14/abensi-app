<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel karyawan_shift
 * Tabel pivot yang menghubungkan karyawan dengan shift mereka
 * Satu karyawan bisa berganti shift dengan tanggal berlaku yang berbeda
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('karyawan_shift', function (Blueprint $table) {
            $table->id();

            // Referensi ke karyawan
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');

            // Referensi ke shift
            $table->foreignId('shift_id')
                  ->constrained('shifts')
                  ->onDelete('cascade');

            // Tanggal mulai berlakunya penugasan shift ini
            // Sistem akan mencari shift yang paling baru berlaku untuk karyawan
            $table->date('tanggal_berlaku');

            // Catatan tambahan (opsional)
            $table->string('keterangan')->nullable();

            $table->timestamps();

            // Index untuk query yang sering digunakan
            $table->index(['karyawan_id', 'tanggal_berlaku']);
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan_shift');
    }
};
