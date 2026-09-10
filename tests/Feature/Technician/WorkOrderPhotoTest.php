<?php

namespace Tests\Feature\Technician;

use App\Models\Equipment;
use App\Models\Technician;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkOrderPhotoTest extends TestCase
{
    use RefreshDatabase;

    private Technician $technician;

    private WorkOrder $workOrder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('tecnico');
        $this->technician = Technician::factory()->create(['user_id' => $user->id]);
        $this->workOrder = WorkOrder::factory()->create([
            'technician_id' => $this->technician->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_technician_can_upload_photo_and_it_is_compressed(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->technician->user)
            ->postJson(route('technician.work_orders.photos.store', $this->workOrder), [
                'photo' => UploadedFile::fake()->image('evidencia.jpg', 3000, 2000),
            ]);

        $response->assertCreated()->assertJsonStructure(['id', 'url', 'name']);

        $photo = $this->workOrder->photos()->firstOrFail();
        Storage::disk('public')->assertExists($photo->path);
        $this->assertStringEndsWith('.jpg', $photo->path);

        // Comprimida: el lado mayor no supera 1600px.
        [$width, $height] = getimagesizefromstring(Storage::disk('public')->get($photo->path));
        $this->assertLessThanOrEqual(1600, max($width, $height));
    }

    public function test_technician_cannot_upload_to_someone_elses_order(): void
    {
        Storage::fake('public');
        $otherOrder = WorkOrder::factory()->create(['status' => 'in_progress']);

        $this->actingAs($this->technician->user)
            ->postJson(route('technician.work_orders.photos.store', $otherOrder), [
                'photo' => UploadedFile::fake()->image('evidencia.jpg'),
            ])
            ->assertForbidden();
    }

    public function test_technician_can_delete_own_photo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('work_order_photos/x.jpg', 'fake');
        $photo = $this->workOrder->photos()->create(['path' => 'work_order_photos/x.jpg']);

        $this->actingAs($this->technician->user)
            ->deleteJson(route('technician.work_orders.photos.destroy', [$this->workOrder, $photo]))
            ->assertOk();

        $this->assertDatabaseMissing('work_order_photos', ['id' => $photo->id]);
        Storage::disk('public')->assertMissing('work_order_photos/x.jpg');
    }

    public function test_autosave_returns_json(): void
    {
        $this->actingAs($this->technician->user)
            ->putJson(route('technician.work_orders.update', $this->workOrder), [
                'diagnosis' => 'Borrador en progreso',
            ])
            ->assertOk()
            ->assertJsonStructure(['saved_at']);

        $this->assertSame('Borrador en progreso', $this->workOrder->fresh()->diagnosis);
    }

    public function test_technician_can_edit_equipment_data_from_the_order(): void
    {
        $equipment = Equipment::factory()->create([
            'client_id' => $this->workOrder->client_id,
            'voltage' => '110V',
        ]);
        $this->workOrder->update(['equipment_id' => $equipment->id]);

        $this->actingAs($this->technician->user)
            ->put(route('technician.work_orders.update', $this->workOrder), [
                'diagnosis' => 'OK',
                'eq_voltage' => '220V',
                'maintenance_tasks' => ['Prueba de funcionamiento'],
                'accessories_checked' => ['Cable de AC'],
            ])
            ->assertRedirect();

        $equipment->refresh();
        $this->assertSame('220V', $equipment->voltage);
        $this->assertEqualsCanonicalizing(['Prueba de funcionamiento'], $equipment->maintenance_tasks);
        $this->assertEqualsCanonicalizing(['Cable de AC'], $equipment->accessories);
    }
}
