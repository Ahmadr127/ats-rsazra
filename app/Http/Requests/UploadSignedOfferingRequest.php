<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadSignedOfferingRequest extends FormRequest
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
            'surat_ttd' => ['required', 'file', 'mimes:pdf', 'max:3072'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'surat_ttd.required' => 'Surat penawaran yang sudah di-TTD wajib diunggah.',
            'surat_ttd.file' => 'Surat penawaran yang sudah di-TTD wajib diunggah.',
            'surat_ttd.mimes' => 'Format surat harus PDF.',
            'surat_ttd.max' => 'Ukuran surat maksimal 3 MB.',
        ];
    }
}
