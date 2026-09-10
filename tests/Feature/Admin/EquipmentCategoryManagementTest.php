<?php

namespace Tests\Feature\Admin;

use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\MaintenanceTask;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipmentCategoryManagementTest extends TestCase
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

    public function test_admin_can_view_categories_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.equipment_categories.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_manage_categories(): void
    {
        $cliente = User::factory()->create()->assignRole('cliente');

        $this->actingAs($cliente)
            ->get(route('admin.equipment_categories.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_category_with_template(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.equipment_categories.store'), [
                'name' => 'Compresor',
                'risk_class' => 'IIA',
                'maintenance_frequency' => 'quarterly',
                'voltage' => '110V',
                'specialties' => ['Prevención'],
                'maintenance_tasks' => ['Prueba de fugas', 'Limpieza de filtros'],
                'accessories' => ['Manguera de aire'],
            ])
            ->assertRedirect(route('admin.equipment_categories.index'));

        $category = EquipmentCategory::where('name', 'Compresor')->firstOrFail();
        $this->assertSame('IIA', $category->risk_class);
        $this->assertEqualsCanonicalizing(['Prueba de fugas', 'Limpieza de filtros'], $category->maintenance_tasks);
    }

    public function test_category_name_must_be_unique(): void
    {
        EquipmentCategory::factory()->create(['name' => 'Compresor']);

        $this->actingAs($this->admin())
            ->post(route('admin.equipment_categories.store'), ['name' => 'Compresor'])
            ->assertSessionHasErrors('name');
    }

    public function test_category_with_models_cannot_be_deleted(): void
    {
        $category = EquipmentCategory::factory()->create();
        EquipmentModel::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.equipment_categories.destroy', $category))
            ->assertRedirect(route('admin.equipment_categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('equipment_categories', ['id' => $category->id]);
    }

    public function test_admin_can_view_catalogs_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.equipment_catalogs.index', 'maintenance_tasks'))
            ->assertOk();
    }

    public function test_admin_can_quick_add_catalog_item_via_json(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.equipment_catalogs.store', 'maintenance_tasks'), [
                'name' => 'Calibración de sensores',
            ])
            ->assertCreated()
            ->assertJsonPath('name', 'Calibración de sensores');

        $this->assertDatabaseHas('maintenance_tasks', ['name' => 'Calibración de sensores']);
    }

    public function test_catalog_item_name_must_be_unique(): void
    {
        MaintenanceTask::factory()->create(['name' => 'Prueba de fugas']);

        $this->actingAs($this->admin())
            ->postJson(route('admin.equipment_catalogs.store', 'maintenance_tasks'), [
                'name' => 'Prueba de fugas',
            ])
            ->assertUnprocessable();
    }

    public function test_admin_can_deactivate_catalog_item(): void
    {
        $task = MaintenanceTask::factory()->create();

        $this->actingAs($this->admin())
            ->put(route('admin.equipment_catalogs.update', ['maintenance_tasks', $task->id]), [
                'name' => $task->name,
                'is_active' => 0,
            ])
            ->assertRedirect(route('admin.equipment_catalogs.index', 'maintenance_tasks'));

        $this->assertFalse($task->fresh()->is_active);
    }
}
