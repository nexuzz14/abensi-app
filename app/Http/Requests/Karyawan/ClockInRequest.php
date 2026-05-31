<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request untuk validasi data clock-in
 * Memastikan semua data yang diperlukan tersedia sebelum diproses backend
 */
class ClockInRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            // Data foto wajah dalam format base64
            'foto_base64'     => ['required', 'string'],

            // Koordinat GPS
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'accuracy'        => ['required', 'numeric', 'min:0'],

            // Hasil liveness detection dari frontend
            'status_liveness' => ['required', 'in:passed,failed,skipped'],

            // ID karyawan yang terdeteksi oleh face recognition
            'face_match_id'   => ['nullable', 'integer', 'exists:karyawan,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'foto_base64.required'     => 'Data foto wajah tidak boleh kosong.',
            'latitude.required'        => 'Koordinat GPS (latitude) tidak ditemukan.',
            'longitude.required'       => 'Koordinat GPS (longitude) tidak ditemukan.',
            'status_liveness.required' => 'Status liveness detection tidak valid.',
            'status_liveness.in'       => 'Status liveness harus passed, failed, atau skipped.',
        ];
    }
}
