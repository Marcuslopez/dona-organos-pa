<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegisterDonorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $adultLimit = now()->subYears(18)->toDateString();
        $oldestLimit = now()->subYears(100)->toDateString();

        return [
            'first_name' => $this->nameRules(true),
            'middle_name' => $this->nameRules(false),
            'first_last_name' => $this->nameRules(true),
            'second_last_name' => $this->nameRules(false),
            'birth_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:'.$oldestLimit, 'before_or_equal:'.$adultLimit],
            'gender_id' => ['required', Rule::exists('genders', 'id')->where('is_active', true)],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['required', 'regex:/^(?:\d{3}-\d{4}|\d{4}-\d{4})$/'],
            'province_id' => ['required', Rule::exists('provinces', 'id')->where('is_active', true)],
            'district_id' => ['required', Rule::exists('districts', 'id')->where('is_active', true)],
            'corregimiento_id' => ['required', Rule::exists('corregimientos', 'id')->where('is_active', true)],
            'contacts' => ['required', 'array', 'min:1', 'max:3'],
            'contacts.*.first_name' => $this->nameRules(true),
            'contacts.*.middle_name' => $this->nameRules(false),
            'contacts.*.first_last_name' => $this->nameRules(true),
            'contacts.*.second_last_name' => $this->nameRules(false),
            'contacts.*.relationship_id' => ['required', Rule::exists('relationships', 'id')->where('is_active', true)],
            'contacts.*.phone' => ['required', 'regex:/^(?:\d{3}-\d{4}|\d{4}-\d{4})$/'],
            'contacts.*.email' => ['nullable', 'email:rfc', 'max:180'],
            'contacts.*.is_informed' => ['nullable', 'boolean'],
            'donation_scope_id' => ['required', Rule::exists('donation_scopes', 'id')->where('is_active', true)],
            'research_authorized' => ['accepted'],
            'health_answers' => ['required', 'array'],
            'health_answers.*' => [Rule::exists('health_answer_options', 'id')->where('is_active', true)],
            'signed_name' => ['required', 'string', 'max:180', 'regex:/^\p{Lu}\p{Ll}*(?: \p{Lu}\p{Ll}*)+$/u'],
            'voluntary_accepted' => ['accepted'],
            'electronically_accepted' => ['accepted'],
            'sensitive_data_authorized' => ['accepted'],
            'institutional_query_authorized' => ['accepted'],
            'cornea_information_acknowledged' => ['nullable', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $districtBelongs = \DB::table('districts')
                ->where('id', $this->input('district_id'))
                ->where('province_id', $this->input('province_id'))->exists();
            $corregimientoBelongs = \DB::table('corregimientos')
                ->where('id', $this->input('corregimiento_id'))
                ->where('district_id', $this->input('district_id'))->exists();

            if (! $districtBelongs) {
                $validator->errors()->add('district_id', 'El distrito no pertenece a la provincia seleccionada.');
            }
            if (! $corregimientoBelongs) {
                $validator->errors()->add('corregimiento_id', 'El corregimiento no pertenece al distrito seleccionado.');
            }

            $requiredQuestions = \DB::table('health_questions')->where('is_active', true)->where('is_required', true)->pluck('id');
            foreach ($requiredQuestions as $questionId) {
                if (! $this->filled("health_answers.$questionId")) {
                    $validator->errors()->add("health_answers.$questionId", 'Esta respuesta médica es obligatoria.');
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'birth_date.date_format' => 'Fecha incorrecta.',
            'birth_date.after_or_equal' => 'La fecha de nacimiento no puede ser anterior a 100 años desde la fecha actual.',
            'birth_date.before_or_equal' => 'Debes tener al menos 18 años para completar el registro.',
            '*.regex' => 'Cada nombre o apellido debe iniciar con mayúscula y contener solamente letras.',
            'contacts.*.*.regex' => 'Cada nombre o apellido debe iniciar con mayúscula y contener solamente letras.',
            'signed_name.regex' => 'La firma debe contener nombres y apellidos iniciados con mayúscula y solamente letras.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'contacts.*.email.email' => 'Ingresa un correo electrónico válido.',
            'contacts.min' => 'Debes registrar al menos un contacto.',
            'phone.regex' => 'Usa uno de estos formatos de Panamá: 123-4567 o 6123-4567.',
            'contacts.*.phone.regex' => 'Usa uno de estos formatos de Panamá: 123-4567 o 6123-4567.',
            'research_authorized.accepted' => 'Debes autorizar este uso para continuar.',
            '*.accepted' => 'Debes aceptar esta condición para continuar.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $birthDate = trim((string) $this->input('birth_date'));
        $contacts = collect($this->input('contacts', []))->map(function (mixed $contact): mixed {
            if (is_array($contact)) {
                foreach (['first_name', 'middle_name', 'first_last_name', 'second_last_name'] as $field) {
                    $contact[$field] = trim((string) ($contact[$field] ?? '')) ?: null;
                }
            }

            return $contact;
        })->all();

        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $birthDate, $parts)) {
            $day = (int) $parts[1];
            $month = (int) $parts[2];
            $year = (int) $parts[3];

            if (checkdate($month, $day, $year)) {
                $birthDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        $this->merge([
            'birth_date' => $birthDate,
            'first_name' => trim((string) $this->input('first_name')),
            'middle_name' => trim((string) $this->input('middle_name')) ?: null,
            'first_last_name' => trim((string) $this->input('first_last_name')),
            'second_last_name' => trim((string) $this->input('second_last_name')) ?: null,
            'signed_name' => trim((string) $this->input('signed_name')),
            'contacts' => $contacts,
        ]);
    }

    private function nameRules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'string', 'max:80', 'regex:/^\p{Lu}\p{Ll}*(?:[\'’-]\p{Lu}\p{Ll}*)*$/u'];
    }
}
