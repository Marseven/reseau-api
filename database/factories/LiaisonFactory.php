<?php

namespace Database\Factories;

use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Liaison>
 */
class LiaisonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'from' => Equipement::factory(),
            'to' => Equipement::factory(),
            'label' => 'Liaison ' . fake()->unique()->word(),
            'media' => fake()->randomElement(['fibre', 'cuivre', 'wifi', 'coaxial']),
            'length' => fake()->optional()->numberBetween(1, 500),
            'status' => fake()->boolean(),
        ];
    }
}
