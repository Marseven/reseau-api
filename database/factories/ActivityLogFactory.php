<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['created', 'updated', 'deleted']),
            'entity_type' => Coffret::class,
            'entity_id' => Coffret::factory(),
            'old_values' => ['name' => 'Old Name'],
            'new_values' => ['name' => 'New Name'],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => 'PHPUnit',
        ];
    }
}
