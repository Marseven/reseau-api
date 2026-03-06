<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        return [
            'code' => 'SITE-' . strtoupper($this->faker->unique()->lexify('???')),
            'name' => 'Site ' . $this->faker->city(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'country' => 'Gabon',
            'longitude' => $this->faker->longitude(9, 14),
            'latitude' => $this->faker->latitude(-4, 2),
            'status' => 'active',
            'description' => $this->faker->sentence(),
        ];
    }
}
