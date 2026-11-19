<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadTestProofRequest extends FormRequest
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
            'proofs' => ['required', 'array', 'min:1', 'max:5'],
            'proofs.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'proofs.required' => 'Minimal satu gambar bukti tes wajib diunggah.',
            'proofs.array' => 'Bukti tes harus berupa gambar.',
            'proofs.min' => 'Minimal satu gambar bukti tes wajib diunggah.',
            'proofs.max' => 'Maksimal 5 gambar bukti dalam satu pengiriman.',
            'proofs.*.image' => 'Setiap bukti harus berupa gambar.',
            'proofs.*.mimes' => 'Format gambar harus JPG, JPEG, PNG, atau WEBP.',
            'proofs.*.max' => 'Ukuran setiap gambar maksimal 5 MB.',
        ];
    }
}
