<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'OT-'.$this->faker->unique()->numerify('######'),
            'client_id' => Client::factory(),
            'equipment_id' => null,
            'technician_id' => null,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'type' => $this->faker->randomElement(array_keys(WorkOrder::TYPES)),
            'priority' => $this->faker->randomElement(array_keys(WorkOrder::PRIORITIES)),
            'status' => $this->faker->randomElement(array_keys(WorkOrder::STATUSES)),
            'diagnosis' => $this->faker->optional()->sentence(),
            'work_performed' => $this->faker->optional()->sentence(),
            'scheduled_at' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'started_at' => null,
            'completed_at' => null,
            'closed_at' => null,
        ];
    }
}
