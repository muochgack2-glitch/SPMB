<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // No authentication required for scanner
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20',
            'photo_base64' => 'nullable|string', // Changed to nullable - photo is optional
            'action' => 'required|in:check_in,check_out'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nis.required' => 'NIS tidak terdeteksi dari QR Code.',
            'nis.string' => 'Format NIS tidak valid.',
            'photo_base64.required' => 'Foto wajib diambil saat scan.',
            'action.required' => 'Tipe absensi (check in/check out) wajib dipilih.',
            'action.in' => 'Tipe absensi tidak valid. Harus check_in atau check_out.',
        ];
    }
}
