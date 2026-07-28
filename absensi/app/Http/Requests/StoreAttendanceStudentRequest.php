<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nis' => 'required|string|max:20|unique:attendance_students,nis',
            'nama' => 'required|string|max:100',
            'kelas_id' => 'required|exists:attendance_classes,id',
            'no_hp_ortu' => 'required|string|regex:/^628[0-9]{9,12}$/',
            'foto_profil' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'nis.max' => 'NIS maksimal 20 karakter.',
            'nama.required' => 'Nama siswa wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'kelas_id.required' => 'Kelas wajib dipilih.',
            'kelas_id.exists' => 'Kelas yang dipilih tidak valid.',
            'no_hp_ortu.required' => 'Nomor HP orang tua wajib diisi.',
            'no_hp_ortu.regex' => 'Format nomor HP tidak valid. Gunakan format: 628xxxxxxxxxx',
            'foto_profil.image' => 'File harus berupa gambar.',
            'foto_profil.mimes' => 'Format gambar harus JPEG, JPG, atau PNG.',
            'foto_profil.max' => 'Ukuran gambar maksimal 2MB.',
        ];
    }
}
