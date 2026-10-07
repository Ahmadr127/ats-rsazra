<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::ACCOUNT_UPDATE)
            && $this->user()->isNot($this->route('user'));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')->ignore($this->route('user')),
                'regex:/^[a-z0-9]+$/',
            ],
            'role_id' => ['required', 'integer', Rule::exists('roles', 'id')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'Username',
            'role_id' => 'Peran',
            'password' => 'Kata Sandi Baru',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.regex' => 'Username hanya boleh berisi huruf kecil dan angka.',
        ];
    }
}
