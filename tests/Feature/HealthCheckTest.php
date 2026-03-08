<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_healthy(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson(['status' => 'healthy'])
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['ok'],
                    'storage' => ['ok'],
                ],
            ]);
    }

    public function test_health_check_database_is_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $this->assertTrue($response->json('checks.database.ok'));
    }

    public function test_health_check_storage_is_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $this->assertTrue($response->json('checks.storage.ok'));
    }

    public function test_health_check_does_not_require_auth(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
    }
}
