<?php

namespace Tests\Traits;

use App\Models\User;

trait CreatesTestUsers
{
    protected function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'administrator',
            'is_active' => true,
        ], $overrides));
    }

    protected function createDirecteur(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'directeur',
            'is_active' => true,
        ], $overrides));
    }

    protected function createTechnicien(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'technicien',
            'is_active' => true,
        ], $overrides));
    }

    protected function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'user',
            'is_active' => true,
        ], $overrides));
    }

    protected function createPrestataire(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'prestataire',
            'is_active' => true,
        ], $overrides));
    }
}
