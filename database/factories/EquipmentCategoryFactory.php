<?php

namespace Database\Factories;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EquipmentCategory>
 */
class EquipmentCategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'risk_class' => $this->faker->randomElement(array_keys(Equipment::RISK_CLASSES)),
            'maintenance_frequency' => $this->faker->randomElement(array_keys(Equipment::FREQUENCIES)),
            'specialties' => ['Prevención'],
            'maintenance_tasks' => ['Prueba de funcionamiento', 'Revisión de alarma'],
            'accessories' => ['Cable de AC', 'Batería'],
        ];
    }
}
