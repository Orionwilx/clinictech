<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Client;
use App\Models\EquipmentModel;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogManagementTest extends TestCase
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

    public function test_admin_can_view_brands_index(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.brands.index'))
            ->assertOk();
    }

    public function test_non_admin_cannot_manage_brands(): void
    {
        $tecnico = User::factory()->create()->assignRole('tecnico');

        $this->actingAs($tecnico)
            ->get(route('admin.brands.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_brand(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.brands.store'), ['name' => 'Philips'])
            ->assertRedirect(route('admin.brands.index'));

        $this->assertDatabaseHas('brands', ['name' => 'Philips']);
    }

    public function test_brand_name_must_be_unique(): void
    {
        Brand::factory()->create(['name' => 'Philips']);

        $this->actingAs($this->admin())
            ->post(route('admin.brands.store'), ['name' => 'Philips'])
            ->assertSessionHasErrors('name');
    }

    public function test_admin_can_create_model_for_a_brand(): void
    {
        $brand = Brand::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.equipment_models.store'), ['brand_id' => $brand->id, 'name' => 'MX450'])
            ->assertRedirect(route('admin.equipment_models.index'));

        $this->assertDatabaseHas('equipment_models', ['brand_id' => $brand->id, 'name' => 'MX450']);
    }

    public function test_model_name_unique_per_brand(): void
    {
        $brand = Brand::factory()->create();
        EquipmentModel::factory()->create(['brand_id' => $brand->id, 'name' => 'MX450']);

        $this->actingAs($this->admin())
            ->post(route('admin.equipment_models.store'), ['brand_id' => $brand->id, 'name' => 'MX450'])
            ->assertSessionHasErrors('name');
    }

    public function test_equipment_model_must_belong_to_selected_brand(): void
    {
        $brandA = Brand::factory()->create();
        $brandB = Brand::factory()->create();
        $modelOfB = EquipmentModel::factory()->create(['brand_id' => $brandB->id]);

        $payload = [
            'client_id' => Client::factory()->create()->id,
            'name' => 'Equipo X',
            'brand_id' => $brandA->id,
            'model_id' => $modelOfB->id,
            'serial_number' => 'SN-XYZ-1',
            'status' => 'active',
        ];

        $this->actingAs($this->admin())
            ->post(route('admin.equipment.store'), $payload)
            ->assertSessionHasErrors('model_id');

        $this->assertDatabaseMissing('equipment', ['serial_number' => 'SN-XYZ-1']);
    }
}
