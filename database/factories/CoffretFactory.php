<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coffret>
 */
class CoffretFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'COF-' . fake()->unique()->numerify('###'),
            'name' => 'Coffret ' . fake()->word(),
            'piece' => 'Salle ' . fake()->randomLetter(),
            'long' => fake()->longitude(),
            'lat' => fake()->latitude(),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance']),
        ];
    }
}
