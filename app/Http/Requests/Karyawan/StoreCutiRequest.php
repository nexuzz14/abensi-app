<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class StoreCutiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'jenis_cuti'     => ['required', 'in:sakit,tahunan,izin,melahirkan,darurat'],
            'tanggal_mulai'  => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai'=> ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan'         => ['required', 'string', 'min:10', 'max:1000'],
            'file_surat'     => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_cuti.required'           => 'Jenis cuti wajib dipilih.',
            'jenis_cuti.in'                 => 'Jenis cuti tidak valid.',
            'tanggal_mulai.required'        => 'Tanggal mulai cuti wajib diisi.',
            'tanggal_mulai.after_or_equal'  => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.required'      => 'Tanggal selesai cuti wajib diisi.',
            'tanggal_selesai.after_or_equal'=> 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'alasan.required'               => 'Alasan cuti wajib diisi.',
            'alasan.min'                    => 'Alasan cuti minimal 10 karakter.',
            'file_surat.mimes'              => 'File surat harus berformat PDF, JPG, atau PNG.',
            'file_surat.max'                => 'Ukuran file surat maksimal 5MB.',
        ];
    }
}
