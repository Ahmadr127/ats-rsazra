<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ParseQuestionExcelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'File Excel wajib diunggah.',
            'file.file' => 'File Excel wajib diunggah.',
            'file.mimes' => 'Format file harus XLSX.',
            'file.max' => 'Ukuran file maksimal 2 MB.',
        ];
    }
}
