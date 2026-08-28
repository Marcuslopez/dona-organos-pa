<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Mail\AdminLoginCodeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $this->completeCodeChallenge($user);

        $this->post('/administracion/login', ['email' => strtoupper($user->email), 'password' => 'Administrator'])
            ->assertRedirect('/administracion');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_administrator_must_validate_a_single_use_email_code_before_password(): void
    {
        Mail::fake();
        $user = User::factory()->create(['is_active' => true]);

        $this->post(route('login.code.send'), ['email' => $user->email])
            ->assertRedirect(route('login'));
        Mail::assertSent(AdminLoginCodeMail::class, fn (AdminLoginCodeMail $mail) => $mail->hasTo($user->email));

        $this->withSession(['admin_login' => ['user_id' => $user->id, 'email' => $user->email]])
            ->post(route('login.code.verify'), ['code' => '000000'])
            ->assertSessionHasErrors('code');

        $code = '123456';
        DB::table('admin_login_codes')->where('user_id', $user->id)->update([
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'consumed_at' => null,
        ]);
        $this->withSession(['admin_login' => ['user_id' => $user->id, 'email' => $user->email]])
            ->post(route('login.code.verify'), ['code' => $code])
            ->assertRedirect(route('login'));

        $this->assertNotNull(DB::table('admin_login_codes')->where('user_id', $user->id)->value('consumed_at'));
    }

    public function test_active_master_is_redirected_directly_to_user_management(): void
    {
        $master = User::factory()->create([
            'role' => 'master',
            'password' => Hash::make('MasterAdministrator1'),
            'is_active' => true,
            'must_change_password' => false,
        ]);

        $this->completeCodeChallenge($master);

        $this->post('/administracion/login', ['email' => $master->email, 'password' => 'MasterAdministrator1'])
            ->assertRedirect('/administracion/usuarios');

        $this->assertAuthenticatedAs($master);
    }

    public function test_invalid_credentials_are_rejected_with_a_generic_message(): void
    {
        $this->post('/administracion/login/codigo', ['email' => 'unknown@example.com'])
            ->assertRedirect('/administracion/login');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Administrator'),
            'is_active' => false,
        ]);

        $this->post('/administracion/login/codigo', ['email' => $user->email])
            ->assertRedirect('/administracion/login');

        $this->assertGuest();
    }

    public function test_login_is_temporarily_blocked_after_three_failed_attempts(): void
    {
        $user = User::factory()->create(['email' => 'blocked@example.com', 'password' => Hash::make('Administrator')]);

        foreach (range(1, 3) as $attempt) {
            $this->completeCodeChallenge($user);
            $this->post('/administracion/login', ['email' => $user->email, 'password' => 'incorrect'])
                ->assertSessionHasErrors('password');
        }

        $this->assertNotNull($user->fresh()->login_locked_until);
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

    private function completeCodeChallenge(User $user): void
    {
        DB::table('admin_login_codes')->updateOrInsert(['user_id' => $user->id], [
            'code_hash' => Hash::make('123456'),
            'attempts' => 0,
            'expires_at' => now()->addMinute(),
            'consumed_at' => null,
            'last_sent_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->withSession(['admin_login' => ['user_id' => $user->id, 'email' => $user->email]])
            ->post('/administracion/login/codigo/verificar', ['code' => '123456'])
            ->assertRedirect('/administracion/login');
    }
}
