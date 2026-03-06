<?php

namespace Database\Factories;

use App\Models\Salle;
use App\Models\Batiment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalleFactory extends Factory
{
    protected $model = Salle::class;

    public function definition(): array
    {
        return [
            'code' => 'SAL-' . strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->randomElement(['Salle Serveur', 'Bureau 101', 'Local Technique', 'Salle de Stockage', 'Bureau Directeur']),
            'batiment_id' => Batiment::factory(),
            'floor' => $this->faker->randomElement(['RDC', '1er', '2ème', '3ème', 'Sous-sol']),
            'type' => $this->faker->randomElement(['salle_serveur', 'bureau', 'technique', 'stockage']),
            'status' => 'active',
            'description' => $this->faker->sentence(),
        ];
    }
}
