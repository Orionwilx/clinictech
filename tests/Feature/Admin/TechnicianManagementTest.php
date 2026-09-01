<?php

namespace Tests\Feature\Admin;

use App\Models\Technician;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicianManagementTest extends TestCase
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

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Carlos Técnico',
            'document' => '1122334455',
            'email' => 'carlos@ingsoln.com',
            'phone' => '3001234567',
            'specialty' => 'Electromedicina',
            'is_active' => '1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_admin_can_view_technicians_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.technicians.index'))
            ->assertOk();
    }

    public function test_technician_role_cannot_manage_technicians(): void
    {
        $tecnico = User::factory()->create()->assignRole('tecnico');

        $this->actingAs($tecnico)
            ->get(route('admin.technicians.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_technician_with_linked_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.technicians.store'), $this->validPayload())
            ->assertRedirect(route('admin.technicians.index'));

        $technician = Technician::where('document', '1122334455')->first();
        $this->assertNotNull($technician);
        $this->assertNotNull($technician->user);
        $this->assertSame('carlos@ingsoln.com', $technician->user->email);
        $this->assertTrue($technician->user->hasRole('tecnico'));
    }

    public function test_email_must_be_unique_across_users(): void
    {
        User::factory()->create(['email' => 'carlos@ingsoln.com']);

        $this->actingAs($this->admin())
            ->post(route('admin.technicians.store'), $this->validPayload())
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_a_technician(): void
    {
        $this->actingAs($this->admin())->post(route('admin.technicians.store'), $this->validPayload());
        $technician = Technician::where('document', '1122334455')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.technicians.update', $technician), $this->validPayload([
                'name' => 'Carlos Actualizado',
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('admin.technicians.index'));

        $technician->refresh();
        $this->assertSame('Carlos Actualizado', $technician->name);
        $this->assertSame('Carlos Actualizado', $technician->user->name);
    }

    public function test_admin_can_soft_delete_and_restore_a_technician(): void
    {
        $this->actingAs($this->admin())->post(route('admin.technicians.store'), $this->validPayload());
        $technician = Technician::where('document', '1122334455')->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('admin.technicians.destroy', $technician))
            ->assertRedirect(route('admin.technicians.index'));

        $this->assertSoftDeleted($technician);
        $this->assertFalse($technician->user->refresh()->is_active);

        $this->actingAs($this->admin())
            ->put(route('admin.technicians.restore', $technician->id))
            ->assertRedirect(route('admin.technicians.index'));

        $this->assertNotSoftDeleted($technician->fresh());
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.technicians.store'), [])
            ->assertSessionHasErrors(['name', 'document', 'email', 'password']);
    }
}
