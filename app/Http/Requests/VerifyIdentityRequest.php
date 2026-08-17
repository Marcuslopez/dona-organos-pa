<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class VerifyIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_number' => [
                'required',
                'string',
                'max:18',
                'regex:/^(?:(?:[1-9]|1[0-3])-\d{1,4}-\d{1,5}|PE-\d{1,4}-\d{1,5}|E-\d{1,4}-\d{1,6}|N-\d{1,4}-\d{1,4})$/',
            ],
            'document_code' => ['required', 'string', 'regex:/^[A-Z0-9]{9,12}$/'],
            'captcha' => [
                'required',
                'string',
                'size:6',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $expectedHash = (string) $this->session()->get('identity_captcha_hash');
                    if ($expectedHash === '' || ! hash_equals($expectedHash, hash('sha256', Str::lower((string) $value)))) {
                        $fail('El código de seguridad no coincide. Genera uno nuevo e inténtalo otra vez.');
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'document_number.regex' => 'Usa un formato válido con guiones: 8-1234-12345, PE-1234-12345, E-1234-123456 o N-1234-1234.',
            'document_code.regex' => 'Escribe entre 9 y 12 letras o números, sin espacios ni guiones.',
            'captcha.required' => 'Escribe el código de seguridad.',
            'captcha.size' => 'El código de seguridad debe tener seis caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document_number' => Str::upper(str_replace(' ', '', (string) $this->input('document_number'))),
            'document_code' => Str::upper(str_replace(' ', '', (string) $this->input('document_code'))),
            'captcha' => Str::lower(str_replace(' ', '', (string) $this->input('captcha'))),
        ]);
    }
}
