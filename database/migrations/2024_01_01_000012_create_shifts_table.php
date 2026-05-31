<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Buat tabel shifts
 * Master data shift kerja yang mendefinisikan jam masuk, jam keluar,
 * dan toleransi keterlambatan untuk setiap shift
 */
return new class extends Migration
{
    /**
     * Jalankan migration
     */
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // Nama shift, misal: "Shift Pagi", "Shift Siang", "Shift Malam"
            $table->string('nama_shift', 50);

            // Jam masuk dalam format HH:MM (waktu saja, bukan datetime)
            $table->time('jam_masuk');

            // Jam keluar dalam format HH:MM
            $table->time('jam_keluar');

            // Toleransi keterlambatan dalam menit
            // Misal: 15 berarti keterlambatan sampai 15 menit masih dianggap tepat waktu
            $table->unsignedSmallInteger('toleransi_menit')->default(15);

            $table->timestamps();
        });
    }

    /**
     * Batalkan migration
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
