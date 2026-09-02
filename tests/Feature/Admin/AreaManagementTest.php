<?php

namespace Tests\Feature\Admin;

use App\Models\Area;
use App\Models\Client;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreaManagementTest extends TestCase
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

    public function test_admin_can_create_area_for_client(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.clients.areas.store', $client), ['name' => 'UCI'])
            ->assertRedirect(route('admin.clients.show', [$client, 'tab' => 'areas']));

        $this->assertDatabaseHas('areas', ['client_id' => $client->id, 'name' => 'UCI']);
    }

    public function test_non_admin_cannot_create_area(): void
    {
        $client = Client::factory()->create();
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->post(route('admin.clients.areas.store', $client), ['name' => 'UCI'])
            ->assertForbidden();
    }

    public function test_area_name_unique_per_client(): void
    {
        $client = Client::factory()->create();
        Area::factory()->create(['client_id' => $client->id, 'name' => 'UCI']);

        $this->actingAs($this->admin())
            ->post(route('admin.clients.areas.store', $client), ['name' => 'UCI'])
            ->assertSessionHasErrors('name');
    }

    public function test_same_area_name_allowed_for_different_clients(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        Area::factory()->create(['client_id' => $clientA->id, 'name' => 'UCI']);

        $this->actingAs($this->admin())
            ->post(route('admin.clients.areas.store', $clientB), ['name' => 'UCI'])
            ->assertSessionDoesntHaveErrors('name');

        $this->assertDatabaseHas('areas', ['client_id' => $clientB->id, 'name' => 'UCI']);
    }

    public function test_admin_can_update_and_delete_area(): void
    {
        $area = Area::factory()->create(['name' => 'UCI']);

        $this->actingAs($this->admin())
            ->put(route('admin.areas.update', $area), ['name' => 'UCI Adultos'])
            ->assertRedirect();
        $this->assertSame('UCI Adultos', $area->fresh()->name);

        $this->actingAs($this->admin())
            ->delete(route('admin.areas.destroy', $area))
            ->assertRedirect();
        $this->assertDatabaseMissing('areas', ['id' => $area->id]);
    }

    public function test_equipment_area_must_belong_to_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $areaOfB = Area::factory()->create(['client_id' => $clientB->id]);

        $payload = [
            'client_id' => $clientA->id,
            'area_id' => $areaOfB->id,
            'name' => 'Equipo X',
            'serial_number' => 'SN-AREA-1',
            'status' => 'active',
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $payload)
            ->assertSessionHasErrors('area_id');

        $this->assertDatabaseMissing('equipment', ['serial_number' => 'SN-AREA-1']);
    }
}
