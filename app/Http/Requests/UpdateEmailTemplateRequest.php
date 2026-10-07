<?php

namespace App\Http\Requests;

use App\Support\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission(Permissions::EMAIL_TEMPLATE_UPDATE);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'subjek' => ['required', 'string', 'max:255'],
            'isi' => ['required', 'string', 'max:65535'],
        ];
    }
}
