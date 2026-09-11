<?php

namespace Tests\Feature\Client;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El cliente SOLO ve OT aprobadas y enviadas por el admin (visible_to_client),
 * en hoja de vida, PDFs y métricas. En proceso, sin aprobar o eliminadas jamás
 * aparecen (sus propias solicitudes draft/cancelled sí, solo en su listado).
 */
class ClientVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Equipment $equipment;

    private WorkOrder $visibleOrder;

    private WorkOrder $hiddenOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('cliente');
        $this->client = Client::factory()->create(['user_id' => $user->id]);
        $this->equipment = Equipment::factory()->create(['client_id' => $this->client->id, 'status' => 'active']);

        $this->visibleOrder = WorkOrder::factory()->create([
            'client_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'status' => 'closed',
            'visible_to_client' => true,
        ]);

        $this->hiddenOrder = WorkOrder::factory()->create([
            'client_id' => $this->client->id,
            'equipment_id' => $this->equipment->id,
            'status' => 'in_progress',
            'visible_to_client' => false,
        ]);
    }

    public function test_equipment_life_sheet_only_shows_sent_orders(): void
    {
        $this->actingAs($this->client->user)
            ->get(route('client.equipment.show', $this->equipment))
            ->assertOk()
            ->assertSee($this->visibleOrder->code)
            ->assertDontSee($this->hiddenOrder->code);
    }

    public function test_client_cannot_download_pdf_of_unsent_order(): void
    {
        $this->actingAs($this->client->user)
            ->get(route('client.work_orders.pdf', $this->hiddenOrder))
            ->assertForbidden();
    }

    public function test_client_cannot_open_unsent_order(): void
    {
        $this->actingAs($this->client->user)
            ->get(route('client.work_orders.show', $this->hiddenOrder))
            ->assertForbidden();
    }

    public function test_dashboard_metrics_only_count_sent_orders(): void
    {
        $response = $this->actingAs($this->client->user)
            ->get(route('client.dashboard'))
            ->assertOk()
            ->assertSee('Órdenes recibidas')
            ->assertSee($this->visibleOrder->code)
            ->assertDontSee($this->hiddenOrder->code);
    }
}
