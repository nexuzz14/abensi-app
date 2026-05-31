<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model KaryawanShift
 * Tabel pivot yang menghubungkan karyawan dengan shift mereka
 * beserta tanggal mulai berlakunya penugasan tersebut
 */
class KaryawanShift extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'karyawan_shift';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'karyawan_id',
        'shift_id',
        'tanggal_berlaku',
        'keterangan',
    ];

    /**
     * Cast tipe data kolom
     */
    protected function casts(): array
    {
        return [
            'tanggal_berlaku' => 'date',
        ];
    }

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke karyawan
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Relasi ke shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
