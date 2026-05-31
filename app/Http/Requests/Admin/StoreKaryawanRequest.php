<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi tambah karyawan baru
 */
class StoreKaryawanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nip'             => ['required', 'string', 'max:20', 'unique:karyawan,nip'],
            'nama_lengkap'    => ['required', 'string', 'max:100'],
            'jabatan'         => ['required', 'string', 'max:100'],
            'no_hp'           => ['required', 'string', 'max:20'],
            'password'        => ['required', 'string', 'min:8'],
            'shift_id'        => ['nullable', 'exists:shifts,id'],
            'tanggal_berlaku' => ['nullable', 'date'],
            'foto'            => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'          => 'NIP wajib diisi.',
            'nip.unique'            => 'NIP sudah digunakan oleh karyawan lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required'      => 'Jabatan wajib diisi.',
            'no_hp.required'        => 'Nomor HP wajib diisi.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'foto.image'            => 'File foto harus berupa gambar.',
            'foto.max'              => 'Ukuran foto maksimal 2MB.',
        ];
    }
}
