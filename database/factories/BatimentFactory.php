<?php

namespace Database\Factories;

use App\Models\Batiment;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatimentFactory extends Factory
{
    protected $model = Batiment::class;

    public function definition(): array
    {
        return [
            'code' => 'BAT-' . strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->randomElement(['Bâtiment Principal', 'Annexe', 'Hangar', 'Tour A', 'Tour B']),
            'zone_id' => Zone::factory(),
            'address' => $this->faker->address(),
            'floors_count' => $this->faker->numberBetween(1, 10),
            'longitude' => $this->faker->longitude(),
            'latitude' => $this->faker->latitude(),
            'status' => 'active',
            'description' => $this->faker->sentence(),
        ];
    }
}
