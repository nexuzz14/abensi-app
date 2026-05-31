<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Cuti
 * Mengelola pengajuan dan persetujuan cuti karyawan
 */
class Cuti extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'cuti';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_cuti',
        'alasan',
        'file_surat',
        'status',
        'approved_by',
        'tanggal_diproses',
        'catatan_admin',
    ];

    /**
     * Cast tipe data kolom
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai'    => 'date',
            'tanggal_selesai'  => 'date',
            'tanggal_diproses' => 'datetime',
        ];
    }

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke karyawan yang mengajukan cuti
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    /**
     * Relasi ke admin yang memproses cuti ini
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Hitung jumlah hari cuti (inklusif)
     */
    public function getJumlahHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    /**
     * Dapatkan badge warna sesuai status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'secondary',
        };
    }

    /**
     * Dapatkan label jenis cuti yang lebih mudah dibaca
     */
    public function getJenisCutiLabelAttribute(): string
    {
        return match ($this->jenis_cuti) {
            'sakit'      => 'Sakit',
            'tahunan'    => 'Cuti Tahunan',
            'izin'       => 'Izin',
            'melahirkan' => 'Cuti Melahirkan',
            'darurat'    => 'Darurat',
            default      => ucfirst($this->jenis_cuti),
        };
    }

    /**
     * Cek apakah pengajuan cuti masih bisa diubah/dibatalkan (hanya saat pending)
     */
    public function bisaDiubah(): bool
    {
        return $this->status === 'pending';
    }
}
