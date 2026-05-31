<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model User
 * Mengelola data autentikasi pengguna sistem (admin dan karyawan)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kolom yang boleh diisi secara massal (mass assignment)
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'admin' atau 'karyawan'
    ];

    /**
     * Kolom yang disembunyikan dari serialisasi (misal: response JSON)
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast tipe data kolom secara otomatis
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ========================
    // RELASI
    // ========================

    /**
     * Relasi ke data karyawan
     * Satu user hanya memiliki satu data karyawan
     */
    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class);
    }

    // ========================
    // HELPER METHODS
    // ========================

    /**
     * Cek apakah user adalah admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Cek apakah user adalah karyawan
     */
    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }
}
