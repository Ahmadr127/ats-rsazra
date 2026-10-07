<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isHrAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.hero_eyebrow' => ['required', 'string', 'max:255'],
            'settings.hero_title' => ['required', 'string', 'max:255'],
            'settings.hero_lede' => ['required', 'string', 'max:1000'],
            'settings.kontak_telepon' => ['required', 'string', 'max:50'],
            'settings.kontak_wa_nomor' => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/'],
            'settings.kontak_wa_label' => ['required', 'string', 'max:50'],
            'settings.kontak_email' => ['required', 'email', 'max:255'],
            'settings.kontak_alamat' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'settings.hero_eyebrow.required' => 'Teks kecil hero wajib diisi.',
            'settings.hero_title.required' => 'Judul hero wajib diisi.',
            'settings.hero_lede.required' => 'Paragraf hero wajib diisi.',
            'settings.kontak_telepon.required' => 'Nomor telepon wajib diisi.',
            'settings.kontak_wa_nomor.required' => 'Nomor WhatsApp wajib diisi.',
            'settings.kontak_wa_nomor.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'settings.kontak_wa_label.required' => 'Teks WhatsApp wajib diisi.',
            'settings.kontak_email.required' => 'Email wajib diisi.',
            'settings.kontak_email.email' => 'Format email tidak valid.',
            'settings.kontak_alamat.required' => 'Alamat wajib diisi.',
        ];
    }
}
