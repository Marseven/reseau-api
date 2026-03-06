<?php

namespace Database\Factories;

use App\Models\Vlan;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class VlanFactory extends Factory
{
    protected $model = Vlan::class;

    public function definition(): array
    {
        return [
            'vlan_id' => $this->faker->unique()->numberBetween(1, 4094),
            'name' => 'VLAN-' . $this->faker->randomElement(['DATA', 'VOIP', 'MGMT', 'GUEST', 'SERVER']),
            'description' => $this->faker->sentence(),
            'site_id' => Site::factory(),
            'network' => '192.168.' . $this->faker->numberBetween(1, 254) . '.0/24',
            'gateway' => '192.168.' . $this->faker->numberBetween(1, 254) . '.1',
            'status' => 'active',
        ];
    }
}
