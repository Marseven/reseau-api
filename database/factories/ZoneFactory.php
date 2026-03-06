<?php

namespace Database\Factories;

use App\Models\Zone;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'code' => 'ZONE-' . strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->randomElement(['Bâtiment A', 'Bâtiment B', 'Local Technique', 'Salle Serveur', 'Salle Réseau']),
            'floor' => $this->faker->randomElement(['RDC', '1er', '2ème', 'Sous-sol']),
            'building' => $this->faker->randomElement(['Bâtiment Principal', 'Annexe', 'Hangar']),
            'site_id' => Site::factory(),
            'status' => 'active',
            'description' => $this->faker->sentence(),
        ];
    }
}
