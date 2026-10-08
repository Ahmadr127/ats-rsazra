<?php

namespace App\Http\Requests;

use App\Models\DiscQuestion;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SubmitDiscTestRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $questionCount = DiscQuestion::count();

        return [
            'most' => ['required', 'array', "min:{$questionCount}"],
            'most.*' => ['required', 'integer'],
            'least' => ['required', 'array', "min:{$questionCount}"],
            'least.*' => ['required', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'most.required' => 'Semua pertanyaan harus dijawab.',
            'most.min' => 'Semua pertanyaan harus dijawab.',
            'least.required' => 'Semua pertanyaan harus dijawab.',
            'least.min' => 'Semua pertanyaan harus dijawab.',
        ];
    }

    /**
     * @return array<int, \Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $most = $this->input('most', []);
                $least = $this->input('least', []);

                if (! is_array($most) || ! is_array($least)) {
                    return;
                }

                foreach ($most as $questionId => $mostWordId) {
                    if (isset($least[$questionId]) && (int) $least[$questionId] === (int) $mostWordId) {
                        $validator->errors()->add(
                            "most.{$questionId}",
                            'Kata Paling Mirip dan Paling Tidak Mirip tidak boleh sama.'
                        );
                    }
                }
            },
        ];
    }
}
