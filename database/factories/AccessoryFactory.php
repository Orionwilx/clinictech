<?php

namespace Database\Factories;

use App\Models\Accessory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Accessory>
 */
class AccessoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'is_active' => true,
        ];
    }
}
