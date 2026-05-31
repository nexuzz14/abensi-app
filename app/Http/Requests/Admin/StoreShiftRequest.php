<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama_shift'      => ['required', 'string', 'max:50'],
            'jam_masuk'       => ['required', 'date_format:H:i'],
            'jam_keluar'      => ['required', 'date_format:H:i'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_shift.required'      => 'Nama shift wajib diisi.',
            'jam_masuk.required'       => 'Jam masuk wajib diisi.',
            'jam_masuk.date_format'    => 'Format jam masuk harus HH:MM (contoh: 08:00).',
            'jam_keluar.required'      => 'Jam keluar wajib diisi.',
            'jam_keluar.date_format'   => 'Format jam keluar harus HH:MM (contoh: 16:00).',
            'toleransi_menit.required' => 'Toleransi keterlambatan wajib diisi.',
            'toleransi_menit.integer'  => 'Toleransi harus berupa angka.',
            'toleransi_menit.min'      => 'Toleransi minimal 0 menit.',
            'toleransi_menit.max'      => 'Toleransi maksimal 120 menit.',
        ];
    }
}
