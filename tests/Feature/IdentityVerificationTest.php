<?php

namespace Tests\Feature;

use App\Contracts\IdentityProvider;
use App\Models\SimulatedIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class IdentityVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('identity.allow_test_identities', true);
        config()->set('identity.max_attempts', 3);
        config()->set('identity.lockout_seconds', 30);

        SimulatedIdentity::query()->create([
            'document_number' => '8-123-1234',
            'document_code_hash' => '88NNNN00012',
            'is_active' => true,
        ]);
    }

    public function test_identity_form_is_available(): void
    {
        $this->get('/registro')->assertOk()->assertSee('Validación de identidad');
    }

    public function test_matching_document_and_code_create_temporary_verification(): void
    {
        $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
            'document_number' => ' 8-123-1234 ',
            'document_code' => '88nnnn00012',
            'captcha' => 'abc234',
        ])->assertRedirect(route('registration.form'));

        $this->assertSame('8-123-1234', session('identity_verification.document_number'));
        $this->assertNull(session('identity_verification.expires_at'));
        $this->get('/registro/identidad-validada')->assertOk()->assertSee('Identidad validada correctamente');
    }

    public function test_any_new_valid_pair_can_continue_in_simulated_mode(): void
    {
        $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
            'document_number' => '7-321-98765',
            'document_code' => '00AAAA00001',
            'captcha' => 'abc234',
        ])->assertRedirect(route('registration.form'));

        $this->assertSame('7-321-98765', session('identity_verification.document_number'));
    }

    public function test_third_failed_match_starts_visible_lockout(): void
    {
        $this->app->instance(IdentityProvider::class, new class implements IdentityProvider
        {
            public function verify(string $documentNumber, string $documentCode): bool
            {
                return false;
            }
        });

        $payload = ['document_number' => '8-123-1234', 'document_code' => '00AAAA00001', 'captcha' => 'abc234'];

        foreach (range(1, 2) as $attempt) {
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', $payload)->assertSessionHasErrors('document_code');
        }

        $this->withSession($this->captchaSession())->post('/registro/validar-identidad', $payload)
            ->assertSessionHasErrors('document_code')
            ->assertSessionHas('identity_retry_after');
    }

    public function test_verified_page_cannot_be_opened_without_valid_session(): void
    {
        $this->get('/registro/identidad-validada')->assertRedirect(route('registration.identity'));
    }

    public function test_incorrect_captcha_does_not_attempt_identity_verification(): void
    {
        $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
            'document_number' => '8-123-1234',
            'document_code' => '88NNNN00012',
            'captcha' => 'wrong1',
        ])->assertSessionHasErrors('captcha');

        $this->assertSame(0, RateLimiter::attempts('identity|8-123-1234|127.0.0.1'));
        $this->assertNull(session('identity_verification'));
    }

    public function test_document_number_accepts_the_four_defined_formats(): void
    {
        foreach (['13-1234-12345', 'PE-1234-12345', 'E-1234-123456', 'N-1234-1234'] as $documentNumber) {
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
                'document_number' => $documentNumber,
                'document_code' => '00AAAA00001',
                'captcha' => 'abc234',
            ])->assertSessionDoesntHaveErrors('document_number');
        }
    }

    public function test_document_number_rejects_unknown_province_and_av_format(): void
    {
        foreach (['14-1234-12345', '8AV-1234-12345'] as $documentNumber) {
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
                'document_number' => $documentNumber,
                'document_code' => '00AAAA00001',
                'captcha' => 'abc234',
            ])->assertSessionHasErrors('document_number');
        }
    }

    public function test_document_number_rejects_characters_other_than_letters_numbers_and_hyphens(): void
    {
        foreach (['8.123.1234', '8/123/1234', '8_123_1234', '8-123-####'] as $documentNumber) {
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
                'document_number' => $documentNumber,
                'document_code' => '00AAAA00001',
                'captcha' => 'abc234',
            ])->assertSessionHasErrors('document_number');
        }
    }

    public function test_document_code_accepts_between_nine_and_twelve_alphanumeric_characters(): void
    {
        foreach ([['9-1234-12345', 'ABC123456'], ['10-1234-12345', 'ABC123456789']] as [$documentNumber, $documentCode]) {
            SimulatedIdentity::query()->create([
                'document_number' => $documentNumber,
                'document_code_hash' => $documentCode,
                'is_active' => true,
            ]);
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
                'document_number' => $documentNumber,
                'document_code' => $documentCode,
                'captcha' => 'abc234',
            ])->assertRedirect(route('registration.form'));
        }

        foreach (['ABC12345', 'ABC1234567890'] as $documentCode) {
            $this->withSession($this->captchaSession())->post('/registro/validar-identidad', [
                'document_number' => '9-1234-12345',
                'document_code' => $documentCode,
                'captcha' => 'abc234',
            ])->assertSessionHasErrors('document_code');
        }
    }

    private function captchaSession(): array
    {
        return [
            'identity_captcha_code' => 'abc234',
            'identity_captcha_hash' => hash('sha256', 'abc234'),
        ];
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('identity|8-123-1234|127.0.0.1');
        parent::tearDown();
    }
}
