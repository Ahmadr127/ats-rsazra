<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendExternalTestRequest extends FormRequest
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
            'link_tes' => ['required', 'string', 'max:2048', 'url:http,https'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'link_tes.required' => 'Link tes eksternal wajib diisi.',
            'link_tes.url' => 'Link tes harus berupa URL yang valid (http atau https).',
            'catatan.max' => 'Catatan maksimal 2000 karakter.',
        ];
    }
}
