<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdministrativeUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMaster() === true;
    }

    public function rules(): array
    {
        $target = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($target)],
            'role' => ['required', Rule::in(['master', 'administrator'])],
            'reset_password' => ['required', 'boolean'],
            'password' => [Rule::requiredIf(fn (): bool => $this->boolean('reset_password')), 'nullable', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'unlock_access' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'is_active' => $this->boolean('is_active'),
            'reset_password' => $this->boolean('reset_password'),
            'unlock_access' => $this->boolean('unlock_access'),
        ]);
    }
}
