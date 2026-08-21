<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureDonorSessionIsActive;
use App\Models\SimulatedIdentity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonorSessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('identity.allow_test_identities', true);
        config()->set('donor_session.idle_timeout', 120);
        config()->set('donor_session.idle_warning', 30);
        config()->set('donor_session.max_lifetime', 0);

        SimulatedIdentity::query()->create([
            'document_number' => '8-123-1234',
            'document_code_hash' => '88NNNN00012',
            'is_active' => true,
        ]);
    }

    public function test_identity_verification_initializes_donor_session_timestamps(): void
    {
        $response = $this->withSession($this->captchaSession())
            ->post(route('registration.identity.store'), [
                'document_number' => '8-123-1234',
                'document_code' => '88NNNN00012',
                'captcha' => 'abc234',
            ]);

        $response->assertRedirect(route('registration.form'))
            ->assertSessionHas(EnsureDonorSessionIsActive::STARTED_AT_KEY)
            ->assertSessionHas(EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY);
    }

    public function test_inactive_donor_session_expires_after_two_minutes(): void
    {
        $now = now()->timestamp;

        $response = $this->withSession([
            'identity_verification' => $this->identityVerification(),
            EnsureDonorSessionIsActive::STARTED_AT_KEY => $now - 121,
            EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY => $now - 120,
        ])->get(route('registration.identity.verified'));

        $response->assertRedirect(route('registration.identity'))
            ->assertSessionHasErrors('document_number')
            ->assertSessionMissing('identity_verification')
            ->assertSessionMissing(EnsureDonorSessionIsActive::STARTED_AT_KEY)
            ->assertSessionMissing(EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY);
    }

    public function test_active_donor_request_renews_last_activity(): void
    {
        $now = now()->timestamp;

        $response = $this->withSession([
            'identity_verification' => $this->identityVerification(),
            EnsureDonorSessionIsActive::STARTED_AT_KEY => $now - 60,
            EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY => $now - 30,
        ])->get(route('registration.identity.verified'));

        $response->assertOk()
            ->assertSessionHas(EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY, $now);
    }

    public function test_donor_heartbeat_renews_session_and_returns_remaining_time(): void
    {
        $now = now()->timestamp;

        $response = $this->withSession([
            'identity_verification' => $this->identityVerification(),
            EnsureDonorSessionIsActive::STARTED_AT_KEY => $now - 60,
            EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY => $now - 30,
        ])->postJson(route('registration.session.activity'));

        $response->assertOk()
            ->assertJson(['expires_in' => 120])
            ->assertSessionHas(EnsureDonorSessionIsActive::LAST_ACTIVITY_KEY, $now);
    }

    private function identityVerification(): array
    {
        return [
            'document_number' => '8-123-1234',
            'verified_at' => now()->timestamp,
            'donor_status' => null,
            'document_code_hash' => null,
            'document_code_fingerprint' => null,
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
