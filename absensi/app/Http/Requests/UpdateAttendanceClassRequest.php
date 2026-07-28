<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceClassRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add authorization logic if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nama_kelas' => 'required|string|max:50',
            'tingkat' => 'required|integer|in:10,11,12',
            'jurusan' => 'required|string|max:50',
            'wali_kelas_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.max' => 'Nama kelas maksimal 50 karakter.',
            'tingkat.required' => 'Tingkat kelas wajib diisi.',
            'tingkat.in' => 'Tingkat kelas harus 10, 11, atau 12.',
            'jurusan.required' => 'Jurusan wajib diisi.',
            'jurusan.max' => 'Jurusan maksimal 50 karakter.',
            'wali_kelas_id.exists' => 'Wali kelas yang dipilih tidak valid.',
        ];
    }
}
