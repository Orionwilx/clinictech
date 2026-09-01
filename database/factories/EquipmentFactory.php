<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => $this->faker->words(2, true),
            'type' => $this->faker->randomElement(['Monitor', 'Ventilador', 'Desfibrilador', 'Bomba de infusión']),
            'brand' => $this->faker->company(),
            'model' => $this->faker->bothify('MOD-###??'),
            'serial_number' => $this->faker->unique()->bothify('SN-########'),
            'purchase_date' => $this->faker->date(),
            'warranty_expiry' => $this->faker->date(),
            'location' => $this->faker->randomElement(['Piso 1', 'UCI', 'Urgencias', 'Laboratorio']),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(array_keys(Equipment::STATUSES)),
        ];
    }
}
