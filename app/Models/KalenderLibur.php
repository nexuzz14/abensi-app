<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model KalenderLibur
 * Menyimpan daftar hari libur nasional dan cuti bersama
 */
class KalenderLibur extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'kalender_libur';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'tanggal',
        'keterangan',
        'jenis',
    ];

    /**
     * Cast tipe data kolom
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Cek apakah tanggal tertentu adalah hari libur
     *
     * @param string|\Carbon\Carbon $tanggal
     */
    public static function isHariLibur($tanggal): bool
    {
        return static::whereDate('tanggal', $tanggal)->exists();
    }
}
