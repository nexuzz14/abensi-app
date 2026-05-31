<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKalenderLiburRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        if ($this->tipe === 'range') {
            return [
                'tipe'           => ['required', 'in:single,range'],
                'tanggal_mulai'  => ['required', 'date', 'after_or_equal:today'],
                'tanggal_selesai'=> ['required', 'date', 'after_or_equal:tanggal_mulai'],
                'keterangan'     => ['required', 'string', 'max:200'],
                'jenis'          => ['required', 'in:nasional,bersama'],
            ];
        }

        return [
            'tipe'       => ['required', 'in:single,range'],
            'tanggal'    => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:200'],
            'jenis'      => ['required', 'in:nasional,bersama'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.required'          => 'Tanggal libur wajib diisi.',
            'tanggal_mulai.required'    => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'  => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'keterangan.required'       => 'Keterangan hari libur wajib diisi.',
            'jenis.required'            => 'Jenis libur wajib dipilih.',
        ];
    }
}
