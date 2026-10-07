<?php

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Support\Permissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::VACANCY_UPDATE);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul_posisi' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('vacancies', 'slug')->ignore($this->route('lowongan'))],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'workflow_template_id' => ['required', 'integer', 'exists:workflow_templates,id'],
            'jenis_pekerjaan' => ['required', new Enum(EmploymentType::class)],
            'deskripsi_pekerjaan' => ['required', 'string'],
            'kualifikasi' => ['required', 'string'],
            'flyer' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
            'jumlah_posisi' => ['required', 'integer', 'min:1'],
            'tenggat_lamaran' => ['required', 'date'],
            'status' => ['required', new Enum(VacancyStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'judul_posisi.required' => 'Judul posisi wajib diisi.',
            'slug.alpha_dash' => 'Slug hanya boleh berisi huruf, angka, strip, dan underscore.',
            'slug.unique' => 'Slug sudah dipakai lowongan lain.',
            'meta_description.max' => 'Deskripsi meta maksimal 160 karakter.',
            'unit_id.required' => 'Unit wajib dipilih.',
            'unit_id.exists' => 'Unit tidak valid.',
            'workflow_template_id.required' => 'Template alur kerja wajib dipilih.',
            'workflow_template_id.exists' => 'Template alur kerja tidak valid.',
            'jenis_pekerjaan.required' => 'Jenis pekerjaan wajib dipilih.',
            'deskripsi_pekerjaan.required' => 'Deskripsi pekerjaan wajib diisi.',
            'kualifikasi.required' => 'Kualifikasi wajib diisi.',
            'flyer.image' => 'Flyer harus berupa gambar.',
            'flyer.mimes' => 'Flyer harus berformat JPG, PNG, atau WEBP.',
            'flyer.max' => 'Ukuran flyer maksimal 4 MB.',
            'jumlah_posisi.required' => 'Jumlah posisi wajib diisi.',
            'jumlah_posisi.min' => 'Jumlah posisi minimal 1.',
            'tenggat_lamaran.required' => 'Tenggat lamaran wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
        ];
    }
}
