<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\EnsureAdminSessionIsActive;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('admin_session.idle_timeout', 120);
        config()->set('admin_session.idle_warning', 30);
        config()->set('admin_session.max_lifetime', 900);
    }

    public function test_login_initializes_admin_session_timestamps(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Administrator'),
            'is_active' => true,
        ]);

        $this->completeCodeChallenge($user);
        $response = $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'Administrator',
        ]);

        $response->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas(EnsureAdminSessionIsActive::STARTED_AT_KEY)
            ->assertSessionHas(EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY);
    }

    private function completeCodeChallenge(User $user): void
    {
        $code = '123456';
        DB::table('admin_login_codes')->insert([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinute(),
            'last_sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession(['admin_login' => [
            'user_id' => $user->id,
            'email' => $user->email,
            'code_verified_at' => now()->timestamp,
        ]]);
    }

    public function test_inactive_admin_is_logged_out_after_two_minutes(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $now = now()->timestamp;

        $response = $this->actingAs($user)
            ->withSession([
                EnsureAdminSessionIsActive::STARTED_AT_KEY => $now - 121,
                EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => $now - 120,
            ])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'))
            ->assertSessionHas('status');
        $this->assertGuest();
    }

    public function test_active_admin_request_renews_last_activity(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $now = now()->timestamp;

        $response = $this->actingAs($user)
            ->withSession([
                EnsureAdminSessionIsActive::STARTED_AT_KEY => $now - 300,
                EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => $now - 30,
            ])
            ->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSessionHas(EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY, $now);
        $this->assertAuthenticatedAs($user);
    }

    public function test_heartbeat_renews_session_and_returns_remaining_time(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $now = now()->timestamp;

        $response = $this->actingAs($user)
            ->withSession([
                EnsureAdminSessionIsActive::STARTED_AT_KEY => $now - 60,
                EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => $now - 30,
            ])
            ->postJson(route('admin.session.activity'));

        $response->assertOk()
            ->assertJson(['expires_in' => 120])
            ->assertSessionHas(EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY, $now);
    }

    public function test_absolute_lifetime_expires_even_with_recent_activity(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $now = now()->timestamp;

        $response = $this->actingAs($user)
            ->withSession([
                EnsureAdminSessionIsActive::STARTED_AT_KEY => $now - 900,
                EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => $now - 1,
            ])
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_public_donor_routes_do_not_use_admin_timeout(): void
    {
        $now = now()->timestamp;

        $response = $this->withSession([
            EnsureAdminSessionIsActive::STARTED_AT_KEY => $now - 901,
            EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => $now - 121,
        ])->get(route('registration.identity'));

        $response->assertOk();
    }
}
