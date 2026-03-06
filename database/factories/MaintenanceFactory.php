<?php

namespace Database\Factories;

use App\Models\Maintenance;
use App\Models\User;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    protected $model = Maintenance::class;

    public function definition(): array
    {
        return [
            'code' => 'MAINT-' . strtoupper($this->faker->unique()->lexify('?????')),
            'title' => $this->faker->randomElement([
                'Remplacement switch', 'Mise à jour firmware', 'Vérification câblage',
                'Nettoyage armoire', 'Test connectivité', 'Inspection visuelle',
            ]),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['preventive', 'corrective', 'urgente', 'evolutive']),
            'priority' => $this->faker->randomElement(['basse', 'moyenne', 'haute', 'critique']),
            'status' => 'planifiee',
            'technicien_id' => User::factory()->state(['role' => 'technicien']),
            'site_id' => Site::factory(),
            'scheduled_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'scheduled_time' => $this->faker->time('H:i'),
        ];
    }
}
