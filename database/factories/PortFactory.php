<?php

namespace Database\Factories;

use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Port>
 */
class PortFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_label' => 'P' . fake()->unique()->numerify('###'),
            'device_name' => 'SW-' . fake()->numerify('##'),
            'poe_enabled' => fake()->boolean(),
            'vlan' => fake()->optional()->numerify('###'),
            'speed' => fake()->optional()->randomElement(['100Mbps', '1Gbps', '10Gbps']),
            'connected_equipment_id' => Equipement::factory(),
        ];
    }
}
