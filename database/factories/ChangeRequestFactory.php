<?php

namespace Database\Factories;

use App\Models\ChangeRequest;
use App\Models\Coffret;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChangeRequestFactory extends Factory
{
    protected $model = ChangeRequest::class;

    public function definition(): array
    {
        return [
            'coffret_id' => Coffret::factory(),
            'requester_id' => User::factory()->state(['role' => 'technicien']),
            'type' => $this->faker->randomElement([
                'ajout_port', 'modification_connexion', 'suppression_port',
                'changement_statut', 'ajout_equipement', 'suppression_equipement',
            ]),
            'description' => $this->faker->paragraph(),
            'justification' => $this->faker->paragraph(),
            'intervention_date' => $this->faker->dateTimeBetween('now', '+30 days')->format('Y-m-d H:i:s'),
            'status' => 'en_attente',
            'snapshot_before' => [
                'coffret' => [
                    'id' => 1,
                    'name' => 'Coffret Test',
                    'status' => 'active',
                ],
            ],
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approuvee',
            'reviewer_id' => User::factory()->state(['role' => 'administrator']),
            'reviewed_at' => now(),
            'review_comment' => 'Approuvé.',
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejetee',
            'reviewer_id' => User::factory()->state(['role' => 'administrator']),
            'reviewed_at' => now(),
            'review_comment' => 'Rejeté : justification insuffisante.',
        ]);
    }
}
