<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminMasterUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_administrator_cannot_access_master_user_management(): void
    {
        $administrator = User::factory()->create(['role' => 'administrator', 'is_active' => true]);

        $this->actingAs($administrator)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_master_user_management_renders_the_create_user_form(): void
    {
        $master = User::factory()->create(['role' => 'master', 'is_active' => true]);

        $this->actingAs($master)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Adicionar usuario administrativo')
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertDontSee("@include('admin.users.partials.form'", false);
    }

    public function test_a_master_can_create_an_administrator_with_a_temporary_password(): void
    {
        $master = User::factory()->create(['role' => 'master', 'is_active' => true]);

        $response = $this->actingAs($master)->post(route('admin.users.store'), [
            'name' => 'Administradora Dos',
            'email' => 'administradora2@admin.com',
            'role' => 'administrator',
            'is_active' => '1',
            'password' => 'TemporaryAdmin123',
            'password_confirmation' => 'TemporaryAdmin123',
            'current_master_password' => 'password',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::query()->where('email', 'administradora2@admin.com')->firstOrFail();
        $this->assertTrue($user->must_change_password);
        $this->assertSame($master->id, $user->created_by);
        $this->assertTrue(Hash::check('TemporaryAdmin123', $user->password));
        $this->assertDatabaseHas('admin_user_audits', [
            'actor_user_id' => $master->id,
            'target_user_id' => $user->id,
            'action' => 'created',
        ]);
    }

    public function test_a_master_cannot_demote_or_deactivate_their_own_account(): void
    {
        $master = User::factory()->create(['role' => 'master', 'is_active' => true]);

        $response = $this->actingAs($master)->put(route('admin.users.update', $master), [
            'name' => $master->name,
            'email' => $master->email,
            'role' => 'administrator',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
            'current_master_password' => 'password',
        ]);

        $response->assertSessionHasErrors('role');
        $master->refresh();
        $this->assertSame('master', $master->role);
        $this->assertTrue($master->is_active);
    }

    public function test_a_master_can_deactivate_another_master_while_one_active_master_remains(): void
    {
        $actor = User::factory()->create(['role' => 'master', 'is_active' => true]);
        $target = User::factory()->create(['role' => 'master', 'is_active' => true]);

        $response = $this->actingAs($actor)->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'email' => $target->email,
            'role' => 'administrator',
            'is_active' => '0',
            'password' => '',
            'password_confirmation' => '',
            'current_master_password' => 'password',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSame('administrator', $target->fresh()->role);
        $this->assertFalse($target->fresh()->is_active);
    }

    public function test_a_user_with_a_temporary_password_must_change_it_before_entering_the_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'administrator',
            'is_active' => true,
            'must_change_password' => true,
            'password' => 'TemporaryAdmin123',
        ]);

        $this->actingAs($user)->get(route('admin.dashboard'))->assertRedirect(route('admin.password.edit'));

        $response = $this->actingAs($user)->put(route('admin.password.update'), [
            'password' => 'MyPermanentAdmin456',
            'password_confirmation' => 'MyPermanentAdmin456',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('MyPermanentAdmin456', $user->fresh()->password));
        $this->assertDatabaseHas('admin_user_audits', [
            'target_user_id' => $user->id,
            'action' => 'password_changed',
        ]);
    }
}
