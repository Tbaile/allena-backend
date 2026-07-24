<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'string'],
            'tag' => ['sometimes', 'string'],
            'search' => ['sometimes', 'string'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
