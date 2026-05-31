<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel cuti
 * Menyimpan semua pengajuan cuti karyawan beserta status persetujuannya
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('cuti', function (Blueprint $table) {
            $table->id();

            // Karyawan yang mengajukan cuti
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');

            // Periode cuti
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');

            // Jenis cuti yang diajukan
            $table->enum('jenis_cuti', ['sakit', 'tahunan', 'izin', 'melahirkan', 'darurat']);

            // Alasan pengajuan cuti (wajib diisi)
            $table->text('alasan');

            // File surat pendukung (opsional), disimpan di storage private
            $table->string('file_surat')->nullable();

            // ========================
            // STATUS PENGAJUAN
            // ========================

            // Status approval cuti
            // 'pending' = menunggu persetujuan, 'approved' = disetujui, 'rejected' = ditolak
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // Admin yang memproses (approve/reject) pengajuan ini
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            // Tanggal diproses
            $table->timestamp('tanggal_diproses')->nullable();

            // Catatan dari admin saat approve atau reject
            $table->text('catatan_admin')->nullable();

            $table->timestamps();

            // Index untuk query berdasarkan status
            $table->index('status');
            $table->index(['karyawan_id', 'tanggal_mulai']);
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('cuti');
    }
};
