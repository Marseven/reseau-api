<?php

namespace Database\Factories;

use App\Models\Coffret;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Metric>
 */
class MetricFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['CPU', 'Memory', 'Disk', 'Temperature', 'Bandwidth']),
            'type' => fake()->randomElement(['gauge', 'counter', 'histogram']),
            'description' => fake()->optional()->sentence(),
            'last_value' => fake()->optional()->numerify('##.#'),
            'coffret_id' => Coffret::factory(),
            'status' => fake()->boolean(),
        ];
    }
}
