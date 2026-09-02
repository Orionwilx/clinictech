<?php

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderManagementTest extends TestCase
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
            'client_id' => Client::factory()->create()->id,
            'equipment_id' => null,
            'technician_id' => null,
            'title' => 'Falla en monitor',
            'description' => 'El equipo no enciende',
            'type' => 'corrective',
            'priority' => 'high',
            'status' => 'open',
        ], $overrides);
    }

    public function test_admin_can_view_work_orders_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.work_orders.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_create_work_orders(): void
    {
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->get(route('admin.work_orders.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_work_order_and_code_is_generated(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload())
            ->assertRedirect(route('admin.work_orders.index'));

        $order = WorkOrder::firstOrFail();
        $this->assertNotNull($order->code);
        $this->assertStringStartsWith('OT-', $order->code);
        $this->assertSame('Falla en monitor', $order->title);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), [])
            ->assertSessionHasErrors(['client_id', 'title', 'type', 'priority', 'status']);
    }

    public function test_status_must_be_a_valid_value(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload(['status' => 'foo']))
            ->assertSessionHasErrors('status');
    }

    public function test_equipment_must_belong_to_selected_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $equipmentOfB = Equipment::factory()->create(['client_id' => $clientB->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'client_id' => $clientA->id,
                'equipment_id' => $equipmentOfB->id,
            ]))
            ->assertSessionHasErrors('equipment_id');
    }

    public function test_moving_to_in_progress_stamps_started_at(): void
    {
        $order = WorkOrder::factory()->create(['status' => 'open', 'started_at' => null]);

        $this->actingAs($this->admin())
            ->put(route('admin.work_orders.update', $order), $this->validPayload([
                'client_id' => $order->client_id,
                'status' => 'in_progress',
            ]))
            ->assertRedirect(route('admin.work_orders.index'));

        $this->assertNotNull($order->fresh()->started_at);
    }

    public function test_admin_can_assign_technician(): void
    {
        $order = WorkOrder::factory()->create();
        $technician = Technician::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.work_orders.update', $order), $this->validPayload([
                'client_id' => $order->client_id,
                'technician_id' => $technician->id,
                'status' => 'assigned',
            ]))
            ->assertRedirect(route('admin.work_orders.index'));

        $this->assertSame($technician->id, $order->fresh()->technician_id);
    }

    public function test_index_filters_by_status_and_client(): void
    {
        $clientA = Client::factory()->create();
        $clientB = Client::factory()->create();
        $open = WorkOrder::factory()->create(['client_id' => $clientA->id, 'status' => 'open', 'title' => 'Orden abierta A']);
        $closed = WorkOrder::factory()->create(['client_id' => $clientA->id, 'status' => 'closed', 'title' => 'Orden cerrada A']);
        $otherClient = WorkOrder::factory()->create(['client_id' => $clientB->id, 'status' => 'open', 'title' => 'Orden abierta B']);

        $this->actingAs($this->admin())
            ->get(route('admin.work_orders.index', ['status' => 'open', 'client_id' => $clientA->id]))
            ->assertOk()
            ->assertSee($open->code)
            ->assertDontSee($closed->code)
            ->assertDontSee($otherClient->code);
    }

    public function test_index_search_matches_code_or_title(): void
    {
        $match = WorkOrder::factory()->create(['title' => 'Calibración especial']);
        $other = WorkOrder::factory()->create(['title' => 'Revisión rutinaria']);

        $this->actingAs($this->admin())
            ->get(route('admin.work_orders.index', ['search' => 'Calibración']))
            ->assertOk()
            ->assertSee($match->code)
            ->assertDontSee($other->code);
    }

    public function test_admin_can_soft_delete_and_restore_work_order(): void
    {
        $order = WorkOrder::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.work_orders.destroy', $order))
            ->assertRedirect(route('admin.work_orders.index'));

        $this->assertSoftDeleted($order);

        $this->actingAs($this->admin())
            ->put(route('admin.work_orders.restore', $order->id))
            ->assertRedirect(route('admin.work_orders.index'));

        $this->assertNotSoftDeleted($order->fresh());
    }
}
