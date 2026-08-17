<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DonorRegistrationService
{
    public function update(array $data, string $documentNumber, Request $request): array
    {
        return DB::transaction(function () use ($data, $documentNumber, $request): array {
            $donor = DB::table('donors')->where('document_number', $documentNumber)->lockForUpdate()->first();
            if (! $donor || $donor->status !== 'active') {
                throw ValidationException::withMessages([
                    'document_number' => 'Este registro no está disponible para actualización.',
                ]);
            }

            $now = now();
            $personalFields = ['first_name', 'middle_name', 'first_last_name', 'second_last_name', 'birth_date', 'gender_id', 'email', 'phone', 'province_id', 'district_id', 'corregimiento_id'];
            $previousPersonal = collect($personalFields)->mapWithKeys(fn (string $field): array => [$field => $donor->{$field}])->all();
            $newPersonal = collect($personalFields)->mapWithKeys(fn (string $field): array => [$field => $data[$field] ?? null])->all();

            $previousContacts = DB::table('donor_contacts')->where('donor_id', $donor->id)
                ->orderByDesc('is_primary')->orderBy('id')
                ->get(['first_name', 'middle_name', 'first_last_name', 'second_last_name', 'relationship_id', 'email', 'phone', 'is_informed'])
                ->map(fn (object $contact): array => (array) $contact)->all();
            $newContacts = collect($data['contacts'])->map(fn (array $contact): array => [
                'first_name' => $contact['first_name'],
                'middle_name' => $contact['middle_name'] ?? null,
                'first_last_name' => $contact['first_last_name'],
                'second_last_name' => $contact['second_last_name'] ?? null,
                'relationship_id' => (int) $contact['relationship_id'],
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'],
                'is_informed' => (bool) ($contact['is_informed'] ?? false),
            ])->all();
            $previousContacts = collect($previousContacts)->map(function (array $contact): array {
                $contact['relationship_id'] = (int) $contact['relationship_id'];
                $contact['is_informed'] = (bool) $contact['is_informed'];

                return $contact;
            })->all();

            $preference = DB::table('donation_preferences')->where('donor_id', $donor->id)->first();
            $previousScope = (int) $preference?->donation_scope_id;
            $newScope = (int) $data['donation_scope_id'];
            $contactsChanged = $previousContacts !== $newContacts;
            $scopeChanged = $previousScope !== $newScope;
            $personalChanged = $previousPersonal != $newPersonal;

            DB::table('donors')->where('id', $donor->id)->update([
                ...$newPersonal,
                'full_name' => $this->fullName($data),
                'updated_at' => $now,
            ]);
            $this->replaceContacts((int) $donor->id, $data['contacts'], $now);
            DB::table('donation_preferences')->updateOrInsert(
                ['donor_id' => $donor->id],
                ['donation_scope_id' => $newScope, 'research_authorized' => true, 'updated_at' => $now, 'created_at' => $preference?->created_at ?? $now],
            );
            $this->replaceHealthAnswers((int) $donor->id, $data['health_answers'], $now);

            if ($scopeChanged) {
                DB::table('consents')->where('donor_id', $donor->id)->whereNull('revoked_at')->update(['revoked_at' => $now, 'updated_at' => $now]);
                $this->createConsent((int) $donor->id, $data, $request, $now);
            }

            $cardReissued = $scopeChanged || $contactsChanged;
            if ($cardReissued) {
                DB::table('donor_cards')->where('donor_id', $donor->id)->whereNull('revoked_at')->update(['revoked_at' => $now, 'updated_at' => $now]);
                $folio = $this->issueCard((int) $donor->id, $now);
            } else {
                $folio = (string) DB::table('donor_cards')->where('donor_id', $donor->id)->whereNull('revoked_at')->value('folio');
            }

            $changedFields = array_values(array_filter([
                $personalChanged ? 'personal_data' : null,
                $contactsChanged ? 'contacts' : null,
                $scopeChanged ? 'donation_scope' : null,
            ]));
            if ($changedFields !== []) {
                DB::table('donor_change_history')->insert([
                    'donor_id' => $donor->id,
                    'changed_fields' => json_encode($changedFields, JSON_UNESCAPED_UNICODE),
                    'previous_values' => json_encode(['personal' => $previousPersonal, 'contacts' => $previousContacts, 'donation_scope_id' => $previousScope], JSON_UNESCAPED_UNICODE),
                    'new_values' => json_encode(['personal' => $newPersonal, 'contacts' => $newContacts, 'donation_scope_id' => $newScope], JSON_UNESCAPED_UNICODE),
                    'source' => 'donor',
                    'request_id' => (string) Str::uuid(),
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                    'changed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return ['donor_id' => (int) $donor->id, 'folio' => $folio, 'card_reissued' => $cardReissued];
        }, 3);
    }

    public function reactivate(array $data, string $documentNumber, Request $request): array
    {
        return DB::transaction(function () use ($data, $documentNumber, $request): array {
            $donor = DB::table('donors')->where('document_number', $documentNumber)->lockForUpdate()->first();
            if (! $donor || $donor->status !== 'withdrawn') {
                throw ValidationException::withMessages([
                    'document_number' => 'Este registro no está disponible para reactivación.',
                ]);
            }

            $now = now();
            DB::table('donors')->where('id', $donor->id)->update([
                'full_name' => $this->fullName($data),
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'first_last_name' => $data['first_last_name'],
                'second_last_name' => $data['second_last_name'],
                'birth_date' => $data['birth_date'],
                'gender_id' => $data['gender_id'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'province_id' => $data['province_id'],
                'district_id' => $data['district_id'],
                'corregimiento_id' => $data['corregimiento_id'],
                'status' => 'active',
                'registered_at' => $now,
                'withdrawn_at' => null,
                'updated_at' => $now,
            ]);

            DB::table('donor_contacts')->where('donor_id', $donor->id)->delete();
            foreach ($data['contacts'] as $index => $contact) {
                DB::table('donor_contacts')->insert([
                    'donor_id' => $donor->id,
                    'relationship_id' => $contact['relationship_id'],
                    'full_name' => $this->fullName($contact),
                    'first_name' => $contact['first_name'],
                    'middle_name' => $contact['middle_name'],
                    'first_last_name' => $contact['first_last_name'],
                    'second_last_name' => $contact['second_last_name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'],
                    'is_informed' => (bool) ($contact['is_informed'] ?? false),
                    'is_primary' => $index === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('donation_preferences')->updateOrInsert(
                ['donor_id' => $donor->id],
                ['donation_scope_id' => $data['donation_scope_id'], 'research_authorized' => true, 'updated_at' => $now, 'created_at' => $now],
            );

            DB::table('donor_health_answers')->where('donor_id', $donor->id)->delete();
            $activeQuestionIds = DB::table('health_questions')->where('is_active', true)->pluck('id')->map(fn ($id) => (string) $id);
            foreach ($data['health_answers'] as $questionId => $answerOptionId) {
                if ($activeQuestionIds->contains((string) $questionId)) {
                    DB::table('donor_health_answers')->insert([
                        'donor_id' => $donor->id,
                        'health_question_id' => $questionId,
                        'health_answer_option_id' => $answerOptionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $consentNumber = DB::table('consents')->where('donor_id', $donor->id)->count() + 1;
            DB::table('consents')->insert([
                'donor_id' => $donor->id,
                'version' => $consentNumber.'.0',
                'signed_name' => $data['signed_name'],
                'voluntary_accepted' => true,
                'electronically_accepted' => true,
                'sensitive_data_authorized' => true,
                'institutional_query_authorized' => true,
                'cornea_information_acknowledged' => (bool) ($data['cornea_information_acknowledged'] ?? false),
                'accepted_at' => $now,
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $folio = $this->nextFolio($now);
            $publicToken = hash_hmac('sha256', $donor->id.'|'.$folio, (string) config('app.key'));
            DB::table('donor_cards')->insert([
                'donor_id' => $donor->id,
                'folio' => $folio,
                'public_token_hash' => hash('sha256', $publicToken),
                'issued_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('donor_status_history')->insert([
                'donor_id' => $donor->id,
                'previous_status' => 'withdrawn',
                'new_status' => 'active',
                'reason' => 'Reactivación voluntaria con nuevo consentimiento.',
                'source' => 'donor',
                'changed_by_user_id' => null,
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return ['donor_id' => $donor->id, 'folio' => $folio];
        }, 3);
    }

    public function register(array $data, string $documentNumber, string $documentCodeHash, string $documentCodeFingerprint, Request $request): array
    {
        return DB::transaction(function () use ($data, $documentNumber, $documentCodeHash, $documentCodeFingerprint, $request): array {
            if (DB::table('donors')->where('document_number', $documentNumber)->lockForUpdate()->exists()) {
                throw ValidationException::withMessages([
                    'document_number' => 'Esta cédula ya tiene un registro de donante.',
                ]);
            }

            $now = now();
            $donorId = DB::table('donors')->insertGetId([
                'document_type' => 'cedula',
                'document_number' => $documentNumber,
                'document_code_hash' => $documentCodeHash,
                'document_code_fingerprint' => $documentCodeFingerprint,
                'full_name' => $this->fullName($data),
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'first_last_name' => $data['first_last_name'],
                'second_last_name' => $data['second_last_name'],
                'birth_date' => $data['birth_date'],
                'gender_id' => $data['gender_id'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'province_id' => $data['province_id'],
                'district_id' => $data['district_id'],
                'corregimiento_id' => $data['corregimiento_id'],
                'status' => 'active',
                'registered_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('donor_status_history')->insert([
                'donor_id' => $donorId,
                'previous_status' => null,
                'new_status' => 'active',
                'reason' => 'Registro inicial de la voluntad de donación.',
                'source' => 'donor',
                'changed_by_user_id' => null,
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'changed_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($data['contacts'] as $index => $contact) {
                DB::table('donor_contacts')->insert([
                    'donor_id' => $donorId,
                    'relationship_id' => $contact['relationship_id'],
                    'full_name' => $this->fullName($contact),
                    'first_name' => $contact['first_name'],
                    'middle_name' => $contact['middle_name'],
                    'first_last_name' => $contact['first_last_name'],
                    'second_last_name' => $contact['second_last_name'],
                    'email' => $contact['email'] ?? null,
                    'phone' => $contact['phone'],
                    'is_informed' => (bool) ($contact['is_informed'] ?? false),
                    'is_primary' => $index === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('donation_preferences')->insert([
                'donor_id' => $donorId,
                'donation_scope_id' => $data['donation_scope_id'],
                'research_authorized' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('consents')->insert([
                'donor_id' => $donorId,
                'version' => '1.0',
                'signed_name' => $data['signed_name'],
                'voluntary_accepted' => true,
                'electronically_accepted' => true,
                'sensitive_data_authorized' => true,
                'institutional_query_authorized' => true,
                'cornea_information_acknowledged' => (bool) ($data['cornea_information_acknowledged'] ?? false),
                'accepted_at' => $now,
                'request_id' => (string) Str::uuid(),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $activeQuestionIds = DB::table('health_questions')->where('is_active', true)->pluck('id')->map(fn ($id) => (string) $id);
            foreach ($data['health_answers'] as $questionId => $answerOptionId) {
                if ($activeQuestionIds->contains((string) $questionId)) {
                    DB::table('donor_health_answers')->insert([
                        'donor_id' => $donorId,
                        'health_question_id' => $questionId,
                        'health_answer_option_id' => $answerOptionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            $folio = $this->nextFolio($now);
            $publicToken = hash_hmac('sha256', $donorId.'|'.$folio, (string) config('app.key'));
            DB::table('donor_cards')->insert([
                'donor_id' => $donorId,
                'folio' => $folio,
                'public_token_hash' => hash('sha256', $publicToken),
                'issued_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return ['donor_id' => $donorId, 'folio' => $folio];
        }, 3);
    }

    private function nextFolio(mixed $now): string
    {
        DB::table('folio_sequences')->insertOrIgnore([
            'id' => 1,
            'last_number' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sequence = DB::table('folio_sequences')->where('id', 1)->lockForUpdate()->first();
        $number = $sequence->last_number + 1;
        DB::table('folio_sequences')->where('id', 1)->update(['last_number' => $number, 'updated_at' => $now]);

        return 'CD-'.str_pad((string) $number, 7, '0', STR_PAD_LEFT);
    }

    private function replaceContacts(int $donorId, array $contacts, mixed $now): void
    {
        DB::table('donor_contacts')->where('donor_id', $donorId)->delete();
        foreach ($contacts as $index => $contact) {
            DB::table('donor_contacts')->insert([
                'donor_id' => $donorId,
                'relationship_id' => $contact['relationship_id'],
                'full_name' => $this->fullName($contact),
                'first_name' => $contact['first_name'],
                'middle_name' => $contact['middle_name'] ?? null,
                'first_last_name' => $contact['first_last_name'],
                'second_last_name' => $contact['second_last_name'] ?? null,
                'email' => $contact['email'] ?? null,
                'phone' => $contact['phone'],
                'is_informed' => (bool) ($contact['is_informed'] ?? false),
                'is_primary' => $index === 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function replaceHealthAnswers(int $donorId, array $answers, mixed $now): void
    {
        DB::table('donor_health_answers')->where('donor_id', $donorId)->delete();
        $activeQuestionIds = DB::table('health_questions')->where('is_active', true)->pluck('id')->map(fn ($id) => (string) $id);
        foreach ($answers as $questionId => $answerOptionId) {
            if ($activeQuestionIds->contains((string) $questionId)) {
                DB::table('donor_health_answers')->insert([
                    'donor_id' => $donorId,
                    'health_question_id' => $questionId,
                    'health_answer_option_id' => $answerOptionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function createConsent(int $donorId, array $data, Request $request, mixed $now): void
    {
        $consentNumber = DB::table('consents')->where('donor_id', $donorId)->count() + 1;
        DB::table('consents')->insert([
            'donor_id' => $donorId,
            'version' => $consentNumber.'.0',
            'signed_name' => $data['signed_name'],
            'voluntary_accepted' => true,
            'electronically_accepted' => true,
            'sensitive_data_authorized' => true,
            'institutional_query_authorized' => true,
            'cornea_information_acknowledged' => (bool) ($data['cornea_information_acknowledged'] ?? false),
            'accepted_at' => $now,
            'request_id' => (string) Str::uuid(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function issueCard(int $donorId, mixed $now): string
    {
        $folio = $this->nextFolio($now);
        $publicToken = hash_hmac('sha256', $donorId.'|'.$folio, (string) config('app.key'));
        DB::table('donor_cards')->insert([
            'donor_id' => $donorId,
            'folio' => $folio,
            'public_token_hash' => hash('sha256', $publicToken),
            'issued_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $folio;
    }

    private function fullName(array $person): string
    {
        return collect(['first_name', 'middle_name', 'first_last_name', 'second_last_name'])
            ->map(fn (string $field): ?string => $person[$field] ?? null)
            ->filter()->implode(' ');
    }
}
