<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'nit' => $this->faker->unique()->numerify('#########-#'),
            'email' => $this->faker->unique()->companyEmail(),
            'city' => $this->faker->city(),
            'country' => $this->faker->country(),
            'whatsapp' => $this->faker->numerify('3#########'),
            'phone' => $this->faker->numerify('3#########'),
            'is_active' => true,
            'user_id' => null,
        ];
    }
}
