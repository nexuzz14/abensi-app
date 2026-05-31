<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel absensi
 * Tabel utama yang menyimpan semua rekam jejak kehadiran karyawan
 * Termasuk data GPS, foto wajah, dan hasil validasi liveness + fake GPS
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();

            // Referensi ke karyawan yang absen
            $table->foreignId('karyawan_id')
                  ->constrained('karyawan')
                  ->onDelete('cascade');

            // Tanggal absensi (bukan datetime, karena satu record per hari)
            $table->date('tanggal');

            // ========================
            // DATA CLOCK-IN
            // ========================

            // Jam masuk kerja (waktu aktual karyawan absen)
            $table->time('jam_masuk')->nullable();

            // Path foto selfie saat clock-in (disimpan di storage private)
            $table->string('foto_masuk')->nullable();

            // Koordinat GPS saat clock-in
            $table->decimal('lat_masuk', 10, 8)->nullable();
            $table->decimal('lng_masuk', 11, 8)->nullable();

            // Akurasi GPS saat clock-in dalam meter
            $table->decimal('accuracy_masuk', 8, 2)->nullable();

            // ========================
            // DATA CLOCK-OUT
            // ========================

            // Jam keluar kerja
            $table->time('jam_keluar')->nullable();

            // Path foto selfie saat clock-out
            $table->string('foto_keluar')->nullable();

            // Koordinat GPS saat clock-out
            $table->decimal('lat_keluar', 10, 8)->nullable();
            $table->decimal('lng_keluar', 11, 8)->nullable();

            // Akurasi GPS saat clock-out dalam meter
            $table->decimal('accuracy_keluar', 8, 2)->nullable();

            // ========================
            // STATUS VALIDASI
            // ========================

            // Hasil liveness detection (kedip mata)
            // 'passed' = lolos, 'failed' = tidak lolos, 'skipped' = dilewati
            $table->enum('status_liveness', ['passed', 'failed', 'skipped'])->default('skipped');

            // Hasil validasi anti-fake GPS
            // 'clean' = aman, 'suspicious' = mencurigakan, 'blocked' = diblokir
            $table->enum('status_fake_gps', ['clean', 'suspicious', 'blocked'])->default('clean');

            // ========================
            // STATUS KEHADIRAN
            // ========================

            // Status akhir kehadiran karyawan pada hari tersebut
            $table->enum('status_kehadiran', ['hadir', 'terlambat', 'alpa', 'cuti', 'libur'])
                  ->default('alpa');

            // Keterangan tambahan (alasan absen, catatan admin, dll)
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Kombinasi unik: satu karyawan hanya boleh memiliki satu record absensi per hari
            $table->unique(['karyawan_id', 'tanggal']);

            // Index untuk query laporan
            $table->index('tanggal');
            $table->index('status_kehadiran');
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
