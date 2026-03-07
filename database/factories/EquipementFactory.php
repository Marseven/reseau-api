<?php

namespace Database\Factories;

use App\Models\Coffret;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipement>
 */
class EquipementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'equipement_code' => 'EQ-' . fake()->unique()->numerify('####'),
            'name' => fake()->word() . ' ' . fake()->randomElement(['Switch', 'Router', 'AP', 'Firewall']),
            'type' => fake()->randomElement(['switch', 'router', 'access_point', 'firewall']),
            'description' => fake()->optional()->sentence(),
            'direction_in_out' => fake()->optional()->randomElement(['in', 'out']),
            'vlan' => fake()->optional()->numerify('###'),
            'ip_address' => fake()->optional()->ipv4(),
            'coffret_id' => Coffret::factory(),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
            'qr_token' => fake()->uuid(),
        ];
    }
}
