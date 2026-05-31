<?php

namespace App\Http\Requests\Karyawan;

use Illuminate\Foundation\Http\FormRequest;

class ClockOutRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'foto_base64'     => ['required', 'string'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'accuracy'        => ['required', 'numeric', 'min:0'],
            'status_liveness' => ['required', 'in:passed,failed,skipped'],
            'face_match_id'   => ['nullable', 'integer', 'exists:karyawan,id'],
        ];
    }
}
