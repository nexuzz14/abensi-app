<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Absensi
 * Menyimpan record kehadiran harian karyawan beserta
 * validasi GPS, liveness detection, dan status kehadiran
 */
class Absensi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'absensi';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'karyawan_id',
        'tanggal',
        'jam_masuk',
        'foto_masuk',
        'lat_masuk',
        'lng_masuk',
        'accuracy_masuk',
        'jam_keluar',
        'foto_keluar',
        'lat_keluar',
        'lng_keluar',
        'accuracy_keluar',
        'status_liveness',
        'status_fake_gps',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * Cast tipe data kolom
     */
    protected function casts(): array
    {
        return [
            'tanggal'       => 'date',
            'lat_masuk'     => 'float',
            'lng_masuk'     => 'float',
            'lat_keluar'    => 'float',
            'lng_keluar'    => 'float',
            'accuracy_masuk'  => 'float',
            'accuracy_keluar' => 'float',
        ];
    }

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke karyawan pemilik record absensi ini
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Cek apakah karyawan sudah clock-in hari ini
     */
    public function sudahClockIn(): bool
    {
        return $this->jam_masuk !== null;
    }

    /**
     * Cek apakah karyawan sudah clock-out hari ini
     */
    public function sudahClockOut(): bool
    {
        return $this->jam_keluar !== null;
    }

    /**
     * Hitung durasi kerja dalam menit
     * Mengembalikan null jika clock-in atau clock-out belum dilakukan
     */
    public function getDurasiKerjaAttribute(): ?int
    {
        if (!$this->jam_masuk || !$this->jam_keluar) {
            return null;
        }

        $masuk  = \Carbon\Carbon::parse($this->jam_masuk);
        $keluar = \Carbon\Carbon::parse($this->jam_keluar);

        // Handle shift malam yang melewati tengah malam
        if ($keluar->lt($masuk)) {
            $keluar->addDay();
        }

        return $masuk->diffInMinutes($keluar);
    }

    /**
     * Format durasi kerja menjadi "X jam Y menit"
     */
    public function getDurasiKerjaFormatAttribute(): string
    {
        $menit = $this->durasi_kerja;

        if ($menit === null) {
            return '-';
        }

        $jam   = intdiv($menit, 60);
        $sisa  = $menit % 60;

        return "{$jam} jam {$sisa} menit";
    }

    /**
     * Dapatkan badge warna sesuai status kehadiran
     * Berguna untuk tampilan UI
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status_kehadiran) {
            'hadir'     => 'success',
            'terlambat' => 'warning',
            'alpa'      => 'danger',
            'cuti'      => 'info',
            'libur'     => 'secondary',
            default     => 'secondary',
        };
    }
}
