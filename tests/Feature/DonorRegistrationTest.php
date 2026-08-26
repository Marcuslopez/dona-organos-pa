<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureDonorSessionIsActive;
use App\Mail\DonorCardMail;
use App\Services\DonorCardService;
use Database\Seeders\GeographyCatalogSeeder;
use Database\Seeders\ReferenceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DonorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceCatalogSeeder::class);
        $this->seed(GeographyCatalogSeeder::class);
        config()->set('identity.allow_test_identities', true);
    }

    public function test_registration_form_requires_a_current_identity_verification(): void
    {
        $this->get(route('registration.form'))->assertRedirect(route('registration.identity'));
    }

    public function test_verified_adult_can_complete_registration(): void
    {
        $payload = $this->validPayload();

        $response = $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload);

        $response->assertRedirect(route('registration.completed'));
        $this->assertDatabaseHas('donors', ['document_number' => '8-123-1234', 'status' => 'active']);
        $this->assertDatabaseHas('donor_status_history', [
            'previous_status' => null,
            'new_status' => 'active',
            'source' => 'donor',
        ]);
        $donorId = DB::table('donors')->where('document_number', '8-123-1234')->value('id');
        $this->assertDatabaseCount('donor_contacts', 1);
        $this->assertDatabaseHas('consents', ['donor_id' => $donorId, 'consent_sequence' => 1, 'accepted' => true, 'version' => '2.0', 'revoked_at' => null]);
        $this->assertDatabaseHas('donor_cards', ['donor_id' => $donorId, 'folio' => 'CD-0000001', 'revoked_at' => null]);
        $this->assertNull(session('identity_verification'));
    }

    public function test_completed_registration_displays_card_and_independent_actions(): void
    {
        Mail::fake();

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $this->validPayload())
            ->assertRedirect(route('registration.completed'));

        $response = $this->get(route('registration.completed'))
            ->assertOk()
            ->assertSee('CARNÉ DE DONANTE')
            ->assertSee('CD-0000001')
            ->assertSee('Imprimir / Guardar PDF')
            ->assertSee('Enviamos una copia del carné');

        $this->assertSame(2, substr_count($response->getContent(), '<b>Nombre:</b>'));
        $this->assertSame(2, substr_count($response->getContent(), '<b>Tel.:</b>'));

        Mail::assertSent(DonorCardMail::class, fn (DonorCardMail $mail): bool => $mail->hasTo('donante@example.com'));
    }

    public function test_minor_cannot_complete_registration(): void
    {
        $payload = $this->validPayload();
        $payload['birth_date'] = now()->subYears(17)->toDateString();

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertSessionHasErrors('birth_date');

        $this->assertDatabaseCount('donors', 0);
    }

    public function test_impossible_and_out_of_range_birth_dates_are_rejected(): void
    {
        foreach (['31/02/1990', '14/62/7222', now()->subYears(101)->toDateString()] as $birthDate) {
            $payload = $this->validPayload();
            $payload['birth_date'] = $birthDate;

            $this->withSession($this->verifiedSession())
                ->post(route('registration.store'), $payload)
                ->assertSessionHasErrors('birth_date');
        }

        $this->assertDatabaseCount('donors', 0);
    }

    public function test_at_least_one_contact_is_required(): void
    {
        $payload = $this->validPayload();
        $payload['contacts'] = [];

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertSessionHasErrors('contacts');
    }

    public function test_active_donor_can_withdraw_and_related_records_are_revoked(): void
    {
        $this->withSession($this->verifiedSession())->post(route('registration.store'), $this->validPayload());

        $response = $this->withSession($this->verifiedSession('active'))
            ->post(route('registration.withdraw'), ['confirm_withdrawal' => '1']);

        $response->assertRedirect(route('registration.withdrawn'));
        $donorId = DB::table('donors')->where('document_number', '8-123-1234')->value('id');
        $this->assertDatabaseHas('donors', ['id' => $donorId, 'status' => 'withdrawn']);
        $this->assertNotNull(DB::table('consents')->where('donor_id', $donorId)->value('revoked_at'));
        $this->assertNotNull(DB::table('donor_cards')->where('donor_id', $donorId)->value('revoked_at'));
        $this->assertDatabaseHas('donor_status_history', [
            'donor_id' => $donorId,
            'previous_status' => 'active',
            'new_status' => 'withdrawn',
            'source' => 'donor',
        ]);
    }

    public function test_existing_donor_must_match_stored_document_code_before_withdrawal_flow(): void
    {
        $this->withSession($this->verifiedSession())->post(route('registration.store'), $this->validPayload());

        $this->withSession($this->captchaSession())->post(route('registration.identity.store'), [
            'document_number' => '8-123-1234',
            'document_code' => 'WRONG1234',
            'captcha' => 'abc234',
        ])->assertSessionHasErrors('document_code');

        $this->withSession($this->captchaSession())->post(route('registration.identity.store'), [
            'document_number' => '8-123-1234',
            'document_code' => 'ABC123456',
            'captcha' => 'abc234',
        ])->assertRedirect(route('registration.identity.verified'));
    }

    public function test_active_donor_can_view_and_print_card_before_withdrawing(): void
    {
        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $this->validPayload());

        $this->withSession($this->verifiedSession('active'))
            ->get(route('registration.identity.verified'))
            ->assertOk()
            ->assertSee('Hola, Donante. Gracias por ser donante activo')
            ->assertDontSee('Validación completada')
            ->assertSee('Carné vigente')
            ->assertSee('CD-0000001')
            ->assertSeeInOrder([
                'Imprimir / Descargar PDF',
                'Actualizar datos',
                'Darme de baja',
                'Volver al inicio',
            ])
            ->assertSee('Confirmo voluntariamente que deseo dar de baja mi registro.')
            ->assertSee('Confirmar baja')
            ->assertSee('Cancelar')
            ->assertSee('withdrawal-modal-brand', false)
            ->assertDontSee('return confirm(', false);
    }

    public function test_active_donor_can_update_contacts_with_history_and_new_card(): void
    {
        Mail::fake();
        $payload = $this->validPayload();
        $this->withSession($this->verifiedSession())->post(route('registration.store'), $payload);
        $donorId = (int) DB::table('donors')->where('document_number', '8-123-1234')->value('id');

        $this->withSession($this->verifiedSession('active'))
            ->get(route('registration.update.form'))
            ->assertOk()
            ->assertSee('Actualizar mis datos')
            ->assertSee('Contacto')
            ->assertSee('Confirmar actualización');

        $payload['contacts'][0]['phone'] = '6222-3333';
        $this->withSession($this->verifiedSession('active'))
            ->post(route('registration.update.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $this->assertDatabaseHas('donor_contacts', ['donor_id' => $donorId, 'phone' => '6222-3333']);
        $this->assertDatabaseCount('donor_change_history', 1);
        $this->assertSame(2, DB::table('donor_cards')->where('donor_id', $donorId)->count());
        $this->assertSame(1, DB::table('consents')->where('donor_id', $donorId)->count());
        $this->assertNotNull(DB::table('donor_cards')->where('folio', 'CD-0000001')->value('revoked_at'));
        $this->assertDatabaseHas('donor_cards', ['donor_id' => $donorId, 'folio' => 'CD-0000002', 'revoked_at' => null]);
        Mail::assertSent(DonorCardMail::class, 2);
    }

    public function test_personal_data_update_keeps_current_card_and_consent(): void
    {
        Mail::fake();
        $payload = $this->validPayload();
        $this->withSession($this->verifiedSession())->post(route('registration.store'), $payload);
        $donorId = (int) DB::table('donors')->where('document_number', '8-123-1234')->value('id');
        $payload['phone'] = '6999-8888';

        $this->withSession($this->verifiedSession('active'))
            ->post(route('registration.update.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $this->assertDatabaseHas('donors', ['id' => $donorId, 'phone' => '6999-8888']);
        $this->assertSame(1, DB::table('donor_cards')->where('donor_id', $donorId)->count());
        $this->assertSame(1, DB::table('consents')->where('donor_id', $donorId)->count());
        $this->assertDatabaseCount('donor_change_history', 1);
        Mail::assertSent(DonorCardMail::class, 1);
    }

    public function test_withdrawn_donor_can_reactivate_with_new_consent_card_and_history(): void
    {
        Mail::fake();
        $payload = $this->validPayload();

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload);

        $donorId = (int) DB::table('donors')->where('document_number', '8-123-1234')->value('id');
        $this->withSession($this->verifiedSession('active'))
            ->post(route('registration.withdraw'), ['confirm_withdrawal' => '1']);

        $this->withSession($this->verifiedSession('withdrawn'))
            ->get(route('registration.identity.verified'))
            ->assertOk()
            ->assertSee('Hola, Donante')
            ->assertSee('registro de consentimiento para donar órganos')
            ->assertSee('CD-0000001')
            ->assertSee(now()->timezone('America/Panama')->format('d/m/Y'))
            ->assertSee('Folio:')
            ->assertSee('Fecha:')
            ->assertSee('Hora:')
            ->assertSee('withdrawal-summary', false)
            ->assertSee('Registrar nuevamente mi voluntad');

        $this->withSession($this->verifiedSession('withdrawn'))
            ->get(route('registration.reactivation.form'))
            ->assertOk()
            ->assertSee('Reactivar mi voluntad')
            ->assertSee('Donante')
            ->assertSee('Confirmar reactivación');

        $this->withSession($this->verifiedSession('withdrawn'))
            ->post(route('registration.reactivation.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $this->assertDatabaseHas('donors', ['id' => $donorId, 'status' => 'active', 'withdrawn_at' => null]);
        $this->assertDatabaseHas('donor_status_history', ['donor_id' => $donorId, 'previous_status' => 'withdrawn', 'new_status' => 'active']);
        $this->assertDatabaseHas('consents', ['donor_id' => $donorId, 'version' => '2.0', 'revoked_at' => null]);
        $this->assertDatabaseHas('donor_cards', ['donor_id' => $donorId, 'folio' => 'CD-0000002', 'revoked_at' => null]);
        $this->assertSame(2, DB::table('donor_cards')->where('donor_id', $donorId)->count());
        $this->assertSame(1, DB::table('donors')->where('document_number', '8-123-1234')->count());
        Mail::assertSent(DonorCardMail::class, 2);
    }

    public function test_document_code_cannot_be_used_with_a_different_document_number(): void
    {
        $this->withSession($this->verifiedSession())->post(route('registration.store'), $this->validPayload());

        $this->withSession($this->captchaSession())->post(route('registration.identity.store'), [
            'document_number' => '7-321-98765',
            'document_code' => 'ABC123456',
            'captcha' => 'abc234',
        ])->assertSessionHasErrors('document_code');

        $this->assertNull(session('identity_verification'));
    }

    public function test_names_emails_and_phones_must_use_the_required_formats(): void
    {
        $payload = $this->validPayload();
        $payload['first_name'] = 'donante 123';
        $payload['email'] = 'correo-invalido';
        $payload['phone'] = '6000 ABC';
        $payload['contacts'][0]['first_name'] = 'contacto principal';
        $payload['contacts'][0]['email'] = 'contacto-invalido';
        $payload['contacts'][0]['phone'] = '6111/2222';

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertSessionHasErrors([
                'first_name',
                'email',
                'phone',
                'contacts.0.first_name',
                'contacts.0.email',
                'contacts.0.phone',
            ]);
    }

    public function test_accented_capitalized_names_and_hyphenated_phones_are_valid(): void
    {
        $payload = $this->validPayload();
        $payload['first_name'] = 'María';
        $payload['first_last_name'] = 'Núñez';
        $payload['phone'] = '6000-1234';
        $payload['contacts'][0]['first_name'] = 'José';
        $payload['contacts'][0]['first_last_name'] = 'Pérez';
        $payload['contacts'][0]['phone'] = '123-4567';

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertRedirect(route('registration.completed'));
    }

    public function test_birth_date_accepts_manually_typed_day_month_year_format(): void
    {
        $payload = $this->validPayload();
        $payload['birth_date'] = '15/06/1990';

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $this->assertDatabaseHas('donors', ['birth_date' => '1990-06-15']);
    }

    public function test_names_are_trimmed_before_validation_and_storage(): void
    {
        $payload = $this->validPayload();
        $payload['first_name'] = '  Donante  ';
        $payload['middle_name'] = '  De  ';
        $payload['first_last_name'] = '  Prueba  ';
        $payload['contacts'][0]['first_name'] = '  Contacto  ';
        $payload['contacts'][0]['first_last_name'] = '  Principal  ';

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $this->assertDatabaseHas('donors', ['full_name' => 'Donante De Prueba']);
        $this->assertDatabaseHas('donor_contacts', ['full_name' => 'Contacto Principal']);
        $this->assertDatabaseHas('consents', ['donor_id' => DB::table('donors')->where('document_number', '8-123-1234')->value('id'), 'accepted' => true]);
    }

    public function test_donor_card_uses_full_name_and_contact_names_use_optional_initials(): void
    {
        $payload = $this->validPayload();
        $payload['first_name'] = 'Marcos';
        $payload['middle_name'] = 'Rodolfo';
        $payload['first_last_name'] = 'Ramos';
        $payload['second_last_name'] = 'Lopez';
        $payload['contacts'][0]['first_name'] = 'Maria';
        $payload['contacts'][0]['middle_name'] = 'Isabel';
        $payload['contacts'][0]['first_last_name'] = 'Chen';
        $payload['contacts'][0]['second_last_name'] = 'Flores';

        $this->withSession($this->verifiedSession())
            ->post(route('registration.store'), $payload)
            ->assertRedirect(route('registration.completed'));

        $donorId = (int) DB::table('donors')->where('document_number', '8-123-1234')->value('id');
        $card = app(DonorCardService::class)->find($donorId);

        $this->assertDatabaseHas('donors', ['full_name' => 'Marcos Rodolfo Ramos Lopez']);
        $this->assertDatabaseHas('donor_contacts', ['full_name' => 'Maria Isabel Chen Flores']);
        $this->assertSame('Marcos Rodolfo Ramos Lopez', $card['record']->card_name);
        $this->assertSame('Maria I. Chen F.', $card['contacts']->first()->card_name);
    }

    private function verifiedSession(?string $status = null): array
    {
        return [
            'identity_verification' => [
                'document_number' => '8-123-1234',
                'verified_at' => now()->timestamp,
                'donor_status' => $status,
                'document_code_hash' => Hash::make('ABC123456'),
                'document_code_fingerprint' => hash_hmac('sha256', 'ABC123456', (string) config('app.key')),
            ],
            EnsureDonorSessionIsActive::STARTED_AT_KEY => now()->timestamp,
            EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY => now()->timestamp,
        ];
    }

    private function validPayload(): array
    {
        $province = DB::table('provinces')->first();
        $district = DB::table('districts')->where('province_id', $province->id)->first();
        $corregimiento = DB::table('corregimientos')->where('district_id', $district->id)->first();

        return [
            'first_name' => 'Donante',
            'middle_name' => 'De',
            'first_last_name' => 'Prueba',
            'second_last_name' => null,
            'birth_date' => '1990-01-01',
            'gender_id' => DB::table('genders')->value('id'),
            'email' => 'donante@example.com',
            'phone' => '6000-1234',
            'province_id' => $province->id,
            'district_id' => $district->id,
            'corregimiento_id' => $corregimiento->id,
            'contacts' => [[
                'first_name' => 'Contacto',
                'middle_name' => null,
                'first_last_name' => 'Principal',
                'second_last_name' => null,
                'relationship_id' => DB::table('relationships')->value('id'),
                'phone' => '6111-2222',
                'email' => 'contacto@example.com',
                'is_informed' => '1',
            ]],
            'consent_accepted' => '1',
        ];
    }

    private function captchaSession(): array
    {
        return [
            'identity_captcha_code' => 'abc234',
            'identity_captcha_hash' => hash('sha256', 'abc234'),
        ];
    }
}
