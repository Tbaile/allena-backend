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
            'filter' => ['sometimes', 'array:category,tag,search'],
            'filter.category' => ['sometimes', 'string'],
            'filter.tag' => ['sometimes', 'string'],
            'filter.search' => ['sometimes', 'string'],
        ];
    }
}
