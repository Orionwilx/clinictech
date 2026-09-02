<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentModel;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentManagementTest extends TestCase
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
        $brand = Brand::factory()->create();
        $model = EquipmentModel::factory()->create(['brand_id' => $brand->id]);

        return array_merge([
            'client_id' => Client::factory()->create()->id,
            'name' => 'Monitor de signos vitales',
            'type' => 'Monitor',
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'serial_number' => 'SN-12345678',
            'purchase_date' => '2025-01-15',
            'warranty_expiry' => '2027-01-15',
            'location' => 'UCI',
            'notes' => 'Equipo nuevo',
            'status' => 'active',
        ], $overrides);
    }

    public function test_admin_can_view_equipment_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.equipment.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_create_equipment(): void
    {
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->get(route('admin.equipment.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_equipment(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $this->validPayload())
            ->assertRedirect(route('admin.equipment.index'));

        $this->assertDatabaseHas('equipment', [
            'serial_number' => 'SN-12345678',
            'status' => 'active',
        ]);
    }

    public function test_serial_number_must_be_unique(): void
    {
        Equipment::factory()->create(['serial_number' => 'SN-12345678']);

        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $this->validPayload())
            ->assertSessionHasErrors('serial_number');
    }

    public function test_status_must_be_a_valid_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $this->validPayload(['status' => 'roto']))
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_update_equipment(): void
    {
        $equipment = Equipment::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.equipment.update', $equipment), $this->validPayload([
                'client_id' => $equipment->client_id,
                'serial_number' => $equipment->serial_number,
                'status' => 'maintenance',
                'name' => 'Nombre actualizado',
            ]))
            ->assertRedirect(route('admin.equipment.index'));

        $equipment->refresh();
        $this->assertSame('Nombre actualizado', $equipment->name);
        $this->assertSame('maintenance', $equipment->status);
    }

    public function test_admin_can_soft_delete_and_restore_equipment(): void
    {
        $equipment = Equipment::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.equipment.destroy', $equipment))
            ->assertRedirect(route('admin.equipment.index'));

        $this->assertSoftDeleted($equipment);

        $this->actingAs($this->admin())
            ->put(route('admin.equipment.restore', $equipment->id))
            ->assertRedirect(route('admin.equipment.index'));

        $this->assertNotSoftDeleted($equipment->fresh());
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), [])
            ->assertSessionHasErrors(['client_id', 'name', 'serial_number', 'status']);
    }

    public function test_admin_can_store_extended_equipment_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $this->validPayload([
                'serial_number' => 'SN-EXT-001',
                'risk_class' => 'IIB',
                'acquisition_type' => 'comodato',
                'maintenance_frequency' => 'quarterly',
                'warranty_status' => 'en_garantia',
                'specialties' => ['prevention', 'treatment'],
                'maintenance_tasks' => ['functional_test', 'leak_test'],
                'accessories' => ['ac_cable', 'battery'],
                'voltage' => '110V',
            ]))
            ->assertRedirect(route('admin.equipment.index'));

        $equipment = Equipment::where('serial_number', 'SN-EXT-001')->firstOrFail();
        $this->assertSame('IIB', $equipment->risk_class);
        $this->assertSame('comodato', $equipment->acquisition_type);
        $this->assertEqualsCanonicalizing(['prevention', 'treatment'], $equipment->specialties);
        $this->assertEqualsCanonicalizing(['functional_test', 'leak_test'], $equipment->maintenance_tasks);
        $this->assertEqualsCanonicalizing(['ac_cable', 'battery'], $equipment->accessories);
    }

    public function test_extended_enum_fields_are_validated(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $this->validPayload([
                'serial_number' => 'SN-EXT-002',
                'risk_class' => 'IV',
                'acquisition_type' => 'robo',
                'maintenance_tasks' => ['inexistente'],
            ]))
            ->assertSessionHasErrors(['risk_class', 'acquisition_type', 'maintenance_tasks.0']);
    }

    public function test_equipment_life_sheet_shows_work_order_history(): void
    {
        $equipment = Equipment::factory()->create();
        $order = WorkOrder::factory()->create([
            'client_id' => $equipment->client_id,
            'equipment_id' => $equipment->id,
            'title' => 'Cambio de sensor de presión',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.equipment.show', $equipment))
            ->assertOk()
            ->assertSee('Hoja de vida del equipo')
            ->assertSee($order->code)
            ->assertSee('Cambio de sensor de presión');
    }
}
