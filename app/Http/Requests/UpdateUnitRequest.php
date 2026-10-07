<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::UNIT_UPDATE);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Unit $unit */
        $unit = $this->route('unit');

        return [
            'nama' => ['required', 'string', 'max:255', Rule::unique('units', 'nama')->ignore($unit->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama' => 'Nama Unit',
        ];
    }
}
