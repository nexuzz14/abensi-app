<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Shift
 * Merepresentasikan master data shift kerja
 */
class Shift extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'nama_shift',
        'jam_masuk',
        'jam_keluar',
        'toleransi_menit',
    ];

    /**
     * Cast tipe data
     */
    protected function casts(): array
    {
        return [
            'toleransi_menit' => 'integer',
        ];
    }

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke semua penugasan shift ini ke karyawan
     */
    public function karyawanShift(): HasMany
    {
        return $this->hasMany(KaryawanShift::class);
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Format jam masuk dalam format yang mudah dibaca (HH:MM)
     */
    public function getJamMasukFormatAttribute(): string
    {
        return substr($this->jam_masuk, 0, 5);
    }

    /**
     * Format jam keluar dalam format yang mudah dibaca (HH:MM)
     */
    public function getJamKeluarFormatAttribute(): string
    {
        return substr($this->jam_keluar, 0, 5);
    }
}
