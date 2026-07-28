<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentRequest extends FormRequest
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
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'generate_qr' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'excel_file.required' => 'File Excel wajib dipilih.',
            'excel_file.file' => 'File tidak valid.',
            'excel_file.mimes' => 'Format file harus XLSX, XLS, atau CSV.',
            'excel_file.max' => 'Ukuran file maksimal 5MB.',
        ];
    }
}
