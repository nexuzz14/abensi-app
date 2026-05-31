<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form Request untuk validasi update data karyawan
 */
class UpdateKaryawanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $karyawanId = $this->route('karyawan')->id;

        return [
            'nip'          => ['required', 'string', 'max:20', Rule::unique('karyawan', 'nip')->ignore($karyawanId)],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'jabatan'      => ['required', 'string', 'max:100'],
            'no_hp'        => ['required', 'string', 'max:20'],
            'password'     => ['nullable', 'string', 'min:8'],
            'shift_id'     => ['nullable', 'exists:shifts,id'],
            'status_aktif' => ['boolean'],
            'reset_face'   => ['boolean'],
            'foto'         => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nip.required'          => 'NIP wajib diisi.',
            'nip.unique'            => 'NIP sudah digunakan oleh karyawan lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'no_hp.required'        => 'Nomor HP wajib diisi.',
        ];
    }
}
