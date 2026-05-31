<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel karyawan
 * Tabel ini menyimpan data detail karyawan yang terhubung ke tabel users
 * Termasuk data wajah (face_descriptor) untuk keperluan face recognition
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel users untuk autentikasi
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Nomor Induk Pegawai - harus unik
            $table->string('nip', 20)->unique();

            $table->string('nama_lengkap', 100);
            $table->string('jabatan', 100);
            $table->string('no_hp', 20)->nullable();

            // Foto profil karyawan (path relatif dari storage)
            $table->string('foto')->nullable();

            // Descriptor wajah disimpan sebagai JSON array
            // Array of 128 floating point numbers dari face-api.js
            // Nilai ini tidak pernah dikirim kembali ke frontend setelah disimpan
            $table->json('face_descriptor')->nullable();

            // Status karyawan: true = aktif, false = nonaktif/diarsipkan
            $table->boolean('status_aktif')->default(true);

            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('status_aktif');
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
