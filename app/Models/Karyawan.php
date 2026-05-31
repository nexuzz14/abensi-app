<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model Karyawan
 * Menyimpan data detail karyawan termasuk descriptor wajah untuk face recognition
 */
class Karyawan extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'karyawan';

    /**
     * Kolom yang boleh diisi secara massal
     */
    protected $fillable = [
        'user_id',
        'nip',
        'nama_lengkap',
        'jabatan',
        'no_hp',
        'foto',
        'face_descriptor',
        'status_aktif',
    ];

    /**
     * Cast tipe data kolom
     * face_descriptor disimpan sebagai JSON dan akan otomatis di-decode saat diambil
     * status_aktif otomatis dikonversi ke boolean
     */
    protected function casts(): array
    {
        return [
            'face_descriptor' => 'array', // JSON array of 128 floats
            'status_aktif'    => 'boolean',
        ];
    }

    /**
     * Kolom yang disembunyikan dari serialisasi
     * face_descriptor TIDAK dikirim ke frontend setelah disimpan (keamanan data biometrik)
     */
    protected $hidden = [
        'face_descriptor',
    ];

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke model User (autentikasi)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke semua record absensi karyawan ini
     */
    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    /**
     * Relasi ke semua pengajuan cuti karyawan ini
     */
    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class);
    }

    /**
     * Relasi ke semua penugasan shift karyawan ini
     */
    public function karyawanShift(): HasMany
    {
        return $this->hasMany(KaryawanShift::class);
    }

    /**
     * Ambil absensi karyawan pada hari ini
     */
    public function absensiHariIni(): HasOne
    {
        return $this->hasOne(Absensi::class)->whereDate('tanggal', today());
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Dapatkan shift aktif karyawan pada tanggal tertentu
     * Mengambil penugasan shift terbaru yang berlaku pada atau sebelum tanggal tersebut
     */
    public function getShiftAktif($tanggal = null): ?Shift
    {
        $tanggal = $tanggal ?? today();

        $karyawanShift = $this->karyawanShift()
            ->with('shift')
            ->where('tanggal_berlaku', '<=', $tanggal)
            ->orderBy('tanggal_berlaku', 'desc')
            ->first();

        return $karyawanShift?->shift;
    }

    /**
     * Cek apakah karyawan sudah memiliki face descriptor terdaftar
     */
    public function hasFaceDescriptor(): bool
    {
        // Tidak menggunakan $this->face_descriptor karena hidden,
        // kita query langsung ke DB
        return static::where('id', $this->id)
            ->whereNotNull('face_descriptor')
            ->exists();
    }

    /**
     * Ambil URL foto profil karyawan
     * Jika tidak ada foto, return URL foto default
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }

        return asset('img/default-avatar.png');
    }
}
