<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateMeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'current_password' => [
                Rule::requiredIf(fn (): bool => $this->filled('password') && ! $user->must_change_password),
                function (string $attribute, mixed $value, Closure $fail) use ($user): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if (! is_string($value) || ! Hash::check($value, $user->getAuthPassword())) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
        ];
    }
}
