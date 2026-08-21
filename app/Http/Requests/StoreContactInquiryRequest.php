<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = $this->input('name');

        if (is_string($name)) {
            // Elimina espacios al inicio/final y espacios múltiples
            $name = preg_replace('/\s+/u', ' ', trim($name));

            // Normaliza mayúsculas/minúsculas
            // Ej: jUAN péREZ-GóMEZ → Juan Pérez-Gómez
            $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');
        }

        $this->merge([
            'name' => $name,

            'message' => is_string($this->input('message'))
                ? trim($this->input('message'))
                : $this->input('message'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'nullable',
                'string',
                'max:150',

                // Solo letras y espacios.
                // ' y - únicamente pueden aparecer entre letras.
                'regex:/^[\p{L}]+(?:[\'-][\p{L}]+)*(?: [\p{L}]+(?:[\'-][\p{L}]+)*)*$/u',
            ],

            'email' => ['required', 'email:rfc', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'privacy_accepted' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'El nombre solo admite letras, espacios, apóstrofes y guiones. Los apóstrofes y guiones deben estar entre letras.',

            'privacy_accepted.accepted' =>
                'Debes aceptar las condiciones de uso y política de privacidad.',
        ];
    }
}
