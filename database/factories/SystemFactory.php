<?php

namespace Database\Factories;

use App\Models\Coffret;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\System>
 */
class SystemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word() . ' ' . fake()->randomElement(['NMS', 'SNMP', 'Syslog', 'Netflow']),
            'type' => fake()->randomElement(['monitoring', 'alerting', 'logging', 'backup']),
            'description' => fake()->optional()->sentence(),
            'vendor' => fake()->optional()->company(),
            'endpoint' => fake()->optional()->url(),
            'monitored_scope' => fake()->optional()->randomElement(['network', 'servers', 'applications']),
            'coffret_id' => Coffret::factory(),
            'status' => fake()->boolean(),
        ];
    }
}
