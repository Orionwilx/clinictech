<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientManagementTest extends TestCase
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
            'name' => 'Clínica Norte SAS',
            'nit' => '900123456-7',
            'email' => 'contacto@clinicanorte.com',
            'city' => 'Bogotá',
            'country' => 'Colombia',
            'whatsapp' => '3001234567',
            'phone' => '3007654321',
            'is_active' => '1',
            'usuario' => 'clinica_norte',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    public function test_admin_can_view_clients_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.clients.index'))
            ->assertOk();
    }

    public function test_admin_can_upload_client_logo(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())
            ->post(route('admin.clients.store'), $this->validPayload([
                'logo' => UploadedFile::fake()->image('logo.png'),
            ]))
            ->assertRedirect(route('admin.clients.index'));

        $client = Client::where('nit', '900123456-7')->firstOrFail();
        $this->assertNotNull($client->logo_path);
        Storage::disk('public')->assertExists($client->logo_path);
    }

    public function test_non_admin_cannot_view_clients_index(): void
    {
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->get(route('admin.clients.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_a_client_with_linked_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.clients.store'), $this->validPayload())
            ->assertRedirect(route('admin.clients.index'));

        $client = Client::where('nit', '900123456-7')->first();
        $this->assertNotNull($client);

        // Cuenta vinculada creada con rol cliente
        $this->assertNotNull($client->user);
        $this->assertSame('contacto@clinicanorte.com', $client->user->email);
        $this->assertSame('clinica_norte', $client->user->name);
        $this->assertTrue($client->user->hasRole('cliente'));
    }

    public function test_client_hub_shows_equipment_with_pending_work_orders(): void
    {
        $client = Client::factory()->create();
        $withPending = Equipment::factory()->create(['client_id' => $client->id, 'name' => 'Ventilador con OT']);
        $withoutPending = Equipment::factory()->create(['client_id' => $client->id, 'name' => 'Equipo tranquilo']);

        WorkOrder::factory()->create(['client_id' => $client->id, 'equipment_id' => $withPending->id, 'status' => 'in_progress']);
        WorkOrder::factory()->create(['client_id' => $client->id, 'equipment_id' => $withoutPending->id, 'status' => 'closed']);

        $this->actingAs($this->admin())
            ->get(route('admin.clients.show', ['client' => $client, 'tab' => 'pendientes']))
            ->assertOk()
            ->assertViewHas('pendingEquipment', fn ($eq) => $eq->pluck('id')->contains($withPending->id)
                && ! $eq->pluck('id')->contains($withoutPending->id)
                && (int) $eq->firstWhere('id', $withPending->id)->pending_count === 1);
    }

    public function test_email_must_be_unique_across_users(): void
    {
        User::factory()->create(['email' => 'contacto@clinicanorte.com']);

        $this->actingAs($this->admin())
            ->post(route('admin.clients.store'), $this->validPayload())
            ->assertSessionHasErrors('email');
    }

    public function test_admin_can_update_a_client_and_its_account(): void
    {
        $this->actingAs($this->admin())->post(route('admin.clients.store'), $this->validPayload());
        $client = Client::where('nit', '900123456-7')->firstOrFail();

        $this->actingAs($this->admin())
            ->put(route('admin.clients.update', $client), $this->validPayload([
                'name' => 'Clínica Norte Actualizada',
                'usuario' => 'nuevo_usuario',
                'password' => '',
                'password_confirmation' => '',
            ]))
            ->assertRedirect(route('admin.clients.index'));

        $client->refresh();
        $this->assertSame('Clínica Norte Actualizada', $client->name);
        $this->assertSame('nuevo_usuario', $client->user->name);
    }

    public function test_admin_can_soft_delete_and_restore_a_client(): void
    {
        $this->actingAs($this->admin())->post(route('admin.clients.store'), $this->validPayload());
        $client = Client::where('nit', '900123456-7')->firstOrFail();

        $this->actingAs($this->admin())
            ->delete(route('admin.clients.destroy', $client))
            ->assertRedirect(route('admin.clients.index'));

        $this->assertSoftDeleted($client);
        $this->assertFalse($client->user->refresh()->is_active);

        $this->actingAs($this->admin())
            ->put(route('admin.clients.restore', $client->id))
            ->assertRedirect(route('admin.clients.index'));

        $this->assertNotSoftDeleted($client->fresh());
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.clients.store'), [])
            ->assertSessionHasErrors(['name', 'nit', 'email', 'usuario', 'password']);
    }
}
