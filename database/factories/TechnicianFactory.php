<?php

namespace Database\Factories;

use App\Models\Technician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Technician>
 */
class TechnicianFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'document' => $this->faker->unique()->numerify('##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('3#########'),
            'specialty' => $this->faker->randomElement(['Electromedicina', 'Refrigeración', 'Imagenología', 'Laboratorio']),
            'is_active' => true,
            'user_id' => null,
        ];
    }
}
