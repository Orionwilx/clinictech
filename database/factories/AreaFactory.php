<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'name' => ucfirst($this->faker->unique()->words(2, true)),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
