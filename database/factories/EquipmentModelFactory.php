<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\EquipmentModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentModel>
 */
class EquipmentModelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'name' => $this->faker->unique()->bothify('MOD-###??'),
        ];
    }
}
