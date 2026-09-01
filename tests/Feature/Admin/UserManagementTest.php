<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::factory()->create()->assignRole('admin');
    }

    public function test_admin_can_view_users_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.users.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_view_users_index(): void
    {
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_user_with_role(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [
                'name' => 'Juan Técnico',
                'email' => 'juan@ingsoln.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'tecnico',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'juan@ingsoln.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('tecnico'));
        $this->assertTrue($user->is_active);
    }

    public function test_admin_can_update_a_user(): void
    {
        $user = User::factory()->create()->assignRole('cliente');

        $this->actingAs($this->admin())
            ->put(route('admin.users.update', $user), [
                'name' => 'Nombre Nuevo',
                'email' => $user->email,
                'role' => 'tecnico',
                'is_active' => '0',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();
        $this->assertSame('Nombre Nuevo', $user->name);
        $this->assertTrue($user->hasRole('tecnico'));
        $this->assertFalse($user->is_active);
    }

    public function test_admin_can_soft_delete_and_restore_a_user(): void
    {
        $user = User::factory()->create()->assignRole('cliente');

        $this->actingAs($this->admin())
            ->delete(route('admin.users.destroy', $user))
            ->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted($user);

        $this->actingAs($this->admin())
            ->put(route('admin.users.restore', $user->id))
            ->assertRedirect(route('admin.users.index'));

        $this->assertNotSoftDeleted($user);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.users.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'password', 'role']);
    }
}
