<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
            ->assertRedirect(route('admin.work_orders.show', WorkOrder::firstOrFail()));

        $order = WorkOrder::firstOrFail();
        $this->assertNotNull($order->code);
        $this->assertStringStartsWith('OT-', $order->code);
        $this->assertSame('Falla en monitor', $order->title);
    }

    public function test_work_order_code_includes_type_abbreviation(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload(['type' => 'preventive']));
        $this->assertStringEndsWith('_MP', WorkOrder::firstOrFail()->code);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload(['type' => 'review']));
        $this->assertStringEndsWith('_MR', WorkOrder::latest('id')->firstOrFail()->code);
    }

    public function test_creating_work_order_with_technician_notifies_them(): void
    {
        Notification::fake();
        $user = User::factory()->create()->assignRole('tecnico');
        $technician = Technician::factory()->create(['user_id' => $user->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'technician_id' => $technician->id,
            ]));

        Notification::assertSentTo($user, WorkOrderNotification::class);
    }

    public function test_create_form_prefilled_from_equipment_includes_its_details(): void
    {
        $brand = Brand::factory()->create(['name' => 'MarcaPrueba']);
        $category = EquipmentCategory::factory()->create(['name' => 'CategoríaPrueba']);
        $model = EquipmentModel::factory()->create(['brand_id' => $brand->id, 'category_id' => $category->id, 'name' => 'ModeloPrueba']);
        $client = Client::factory()->create();
        $equipment = Equipment::factory()->create([
            'client_id' => $client->id,
            'brand_id' => $brand->id,
            'model_id' => $model->id,
            'category_id' => $category->id,
            'voltage' => '220V',
        ]);

        // El form embebe marca/modelo/categoría/características del equipo para el prellenado.
        $this->actingAs($this->admin())
            ->get(route('admin.work_orders.create', ['client_id' => $client->id, 'equipment_id' => $equipment->id]))
            ->assertOk()
            ->assertSee('MarcaPrueba')
            ->assertSee('ModeloPrueba')
            ->assertSee('CategoríaPrueba')
            ->assertSee('220V');
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), [])
            ->assertSessionHasErrors(['client_id', 'title', 'type', 'priority']);
    }

    public function test_creating_work_order_without_technician_sets_open_status(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload(['technician_id' => null]));

        $this->assertSame('open', WorkOrder::firstOrFail()->status);
    }

    public function test_creating_work_order_with_technician_sets_assigned_status(): void
    {
        $user = User::factory()->create()->assignRole('tecnico');
        $technician = Technician::factory()->create(['user_id' => $user->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload(['technician_id' => $technician->id]));

        $this->assertSame('assigned', WorkOrder::firstOrFail()->status);
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

    public function test_assigning_technician_via_update_transitions_open_to_assigned(): void
    {
        $user = User::factory()->create()->assignRole('tecnico');
        $technician = Technician::factory()->create(['user_id' => $user->id]);
        $order = WorkOrder::factory()->create(['status' => 'open', 'technician_id' => null]);

        $this->actingAs($this->admin())
            ->put(route('admin.work_orders.update', $order), $this->validPayload([
                'client_id' => $order->client_id,
                'technician_id' => $technician->id,
            ]))
            ->assertRedirect(route('admin.work_orders.show', $order));

        $this->assertSame('assigned', $order->fresh()->status);
    }

    public function test_admin_can_assign_technician(): void
    {
        $order = WorkOrder::factory()->create();
        $technician = Technician::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.work_orders.update', $order), $this->validPayload([
                'client_id' => $order->client_id,
                'technician_id' => $technician->id,
            ]))
            ->assertRedirect(route('admin.work_orders.show', $order));

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

    public function test_work_order_stores_maintenance_checklist(): void
    {
        $client = Client::factory()->create();
        $equipment = Equipment::factory()->create(['client_id' => $client->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'client_id' => $client->id,
                'equipment_id' => $equipment->id,
                'type' => 'preventive',
                'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma'],
                'accessories_checked' => ['Cable de AC', 'Batería'],
            ]))
            ->assertRedirect(route('admin.work_orders.show', WorkOrder::firstOrFail()));

        $order = WorkOrder::firstOrFail();
        $this->assertEqualsCanonicalizing(['Prueba de funcionamiento', 'Revisión de alarma'], $order->maintenance_tasks);
        $this->assertEqualsCanonicalizing(['Cable de AC', 'Batería'], $order->accessories_checked);
    }

    public function test_work_order_persists_equipment_data_to_the_equipment(): void
    {
        $client = Client::factory()->create();
        $equipment = Equipment::factory()->create([
            'client_id' => $client->id,
            'voltage' => '110V',
            'maintenance_tasks' => ['Prueba de funcionamiento'],
        ]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'client_id' => $client->id,
                'equipment_id' => $equipment->id,
                'type' => 'preventive',
                'eq_voltage' => '220V',
                'eq_risk_class' => 'IIB',
                'maintenance_tasks' => ['Prueba de funcionamiento', 'Prueba de fugas'],
                'accessories_checked' => ['Cable de AC'],
            ]))
            ->assertRedirect(route('admin.work_orders.show', WorkOrder::firstOrFail()));

        $equipment->refresh();
        // Las características y el checklist editados desde la OT persisten en la ficha del equipo.
        $this->assertSame('220V', $equipment->voltage);
        $this->assertSame('IIB', $equipment->risk_class);
        $this->assertEqualsCanonicalizing(['Prueba de funcionamiento', 'Prueba de fugas'], $equipment->maintenance_tasks);
        $this->assertEqualsCanonicalizing(['Cable de AC'], $equipment->accessories);
    }

    public function test_equipment_risk_class_from_order_is_validated(): void
    {
        $client = Client::factory()->create();
        $equipment = Equipment::factory()->create(['client_id' => $client->id]);

        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'client_id' => $client->id,
                'equipment_id' => $equipment->id,
                'eq_risk_class' => 'ZZZ',
            ]))
            ->assertSessionHasErrors('eq_risk_class');
    }

    public function test_work_order_checklist_rejects_non_string_values(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.work_orders.store'), $this->validPayload([
                'maintenance_tasks' => [['array' => 'no válido']],
            ]))
            ->assertSessionHasErrors(['maintenance_tasks.0']);
    }

    public function test_admin_can_upload_and_delete_photo_on_a_work_order(): void
    {
        Storage::fake('private');
        $order = WorkOrder::factory()->create();

        $response = $this->actingAs($this->admin())
            ->postJson(route('admin.work_orders.photos.store', $order), [
                'photo' => UploadedFile::fake()->image('evidencia.jpg', 3000, 2000),
            ])
            ->assertCreated()
            ->assertJsonStructure(['id', 'url', 'name']);

        $photo = $order->photos()->firstOrFail();
        Storage::disk('private')->assertExists($photo->path);
        // Comprimida por ImageService.
        [$width, $height] = getimagesizefromstring(Storage::disk('private')->get($photo->path));
        $this->assertLessThanOrEqual(1600, max($width, $height));

        $this->actingAs($this->admin())
            ->deleteJson(route('admin.work_orders.photos.destroy', [$order, $photo->id]))
            ->assertOk();
        $this->assertDatabaseMissing('uploads', ['id' => $photo->id]);
    }

    public function test_client_cannot_upload_photo_to_work_order(): void
    {
        $order = WorkOrder::factory()->create();
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->postJson(route('admin.work_orders.photos.store', $order), [
                'photo' => UploadedFile::fake()->image('x.jpg'),
            ])
            ->assertForbidden();
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
