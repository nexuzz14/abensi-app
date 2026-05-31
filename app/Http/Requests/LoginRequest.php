<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi form login
 */
class LoginRequest extends FormRequest
{
    /**
     * Siapa yang boleh menggunakan request ini
     * Return true berarti semua user (termasuk yang belum login) boleh mengakses
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk form login
     */
    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia
     */
    public function messages(): array
    {
        return [
            'identifier.required' => 'NIP atau Email wajib diisi.',
            'password.required'   => 'Password wajib diisi.',
            'password.min'        => 'Password minimal 6 karakter.',
        ];
    }
}
