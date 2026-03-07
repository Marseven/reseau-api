<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement([
                'modification_request', 'modification_approved', 'modification_rejected',
                'intervention_active', 'system_alert',
            ]),
            'title' => $this->faker->sentence(4),
            'message' => $this->faker->paragraph(),
            'data' => null,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => [
            'read_at' => now(),
        ]);
    }

    public function unread(): static
    {
        return $this->state(fn () => [
            'read_at' => null,
        ]);
    }
}
