<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_is_available_to_guests(): void
    {
        $this->get('/administracion/login')->assertOk()->assertSee('Acceso administrativo');
    }

    public function test_active_administrator_can_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Administrator'),
            'is_active' => true,
        ]);

        $this->post('/administracion/login', [
            'email' => strtoupper($user->email),
            'password' => 'Administrator',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_active_master_is_redirected_directly_to_user_management(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'password' => Hash::make('MasterAdministrator1'),
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->post('/administracion/login', [
            'email' => $master->email,
            'password' => 'MasterAdministrator1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertAuthenticatedAs($master);
    }

    public function test_invalid_credentials_are_rejected_with_a_generic_message(): void
    {
        User::factory()->create();

        $this->post('/administracion/login', [
            'email' => 'unknown@example.com',
            'password' => 'incorrect',
        ])->assertSessionHasErrors(['email' => 'Las credenciales proporcionadas no son correctas.']);

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Administrator'),
            'is_active' => false,
        ]);

        $this->post('/administracion/login', [
            'email' => $user->email,
            'password' => 'Administrator',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_is_temporarily_blocked_after_three_failed_attempts(): void
    {
        $credentials = [
            'email' => 'blocked@example.com',
            'password' => 'incorrect',
        ];

        foreach (range(1, 2) as $attempt) {
            $this->post('/administracion/login', $credentials)
                ->assertSessionHasErrors(['email' => 'Las credenciales proporcionadas no son correctas.']);
        }

        $this->post('/administracion/login', $credentials)
            ->assertSessionHasErrors('email')
            ->assertSessionHas('login_retry_after');

        $this->assertStringContainsString(
            'Demasiados intentos.',
            session('errors')->first('email'),
        );
    }

    public function test_guest_cannot_open_admin_dashboard(): void
    {
        $this->get('/administracion')->assertRedirect(route('login'));
    }

    public function test_administrator_can_log_out(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post('/administracion/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_authenticated_administrator_link_opens_dashboard(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('admin.dashboard').'"', false);
    }
}
