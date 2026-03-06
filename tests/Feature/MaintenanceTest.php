<?php

namespace Tests\Feature;

use App\Models\Maintenance;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $technicien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->technicien = User::factory()->create(['role' => 'technicien', 'is_active' => true]);
    }

    public function test_can_list_maintenances(): void
    {
        Maintenance::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/maintenances');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_filter_maintenances_by_status(): void
    {
        Maintenance::factory()->create(['status' => 'planifiee']);
        Maintenance::factory()->create(['status' => 'en_cours']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/maintenances?status=planifiee');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('planifiee', $item['status']);
        }
    }

    public function test_can_filter_maintenances_by_type(): void
    {
        Maintenance::factory()->create(['type' => 'preventive']);
        Maintenance::factory()->create(['type' => 'corrective']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/maintenances?type=preventive');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('preventive', $item['type']);
        }
    }

    public function test_can_filter_maintenances_by_priority(): void
    {
        Maintenance::factory()->create(['priority' => 'haute']);
        Maintenance::factory()->create(['priority' => 'basse']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/maintenances?priority=haute');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('haute', $item['priority']);
        }
    }

    public function test_can_create_maintenance(): void
    {
        $site = Site::factory()->create();

        $data = [
            'code' => 'MAINT-TEST-001',
            'title' => 'Test Maintenance',
            'description' => 'Description test',
            'type' => 'preventive',
            'priority' => 'moyenne',
            'status' => 'planifiee',
            'technicien_id' => $this->technicien->id,
            'site_id' => $site->id,
            'scheduled_date' => '2026-04-01',
            'scheduled_time' => '10:00',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/maintenances', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Maintenance');
        $this->assertDatabaseHas('maintenances', ['code' => 'MAINT-TEST-001']);
    }

    public function test_can_show_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $maintenance->id);
    }

    public function test_can_update_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create(['title' => 'Old Title']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/maintenances/{$maintenance->id}", ['title' => 'New Title']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('maintenances', ['id' => $maintenance->id, 'title' => 'New Title']);
    }

    public function test_can_delete_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('maintenances', ['id' => $maintenance->id]);
    }

    public function test_create_maintenance_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/maintenances', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'title', 'type', 'priority', 'technicien_id', 'scheduled_date']);
    }

    public function test_create_maintenance_unique_code(): void
    {
        Maintenance::factory()->create(['code' => 'MAINT-DUP']);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/maintenances', [
            'code' => 'MAINT-DUP',
            'title' => 'Duplicate',
            'type' => 'preventive',
            'priority' => 'basse',
            'technicien_id' => $this->technicien->id,
            'scheduled_date' => '2026-04-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthorized_user_cannot_create_maintenance(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/maintenances', [
            'code' => 'MAINT-001',
            'title' => 'Test',
            'type' => 'preventive',
            'priority' => 'basse',
            'technicien_id' => $this->technicien->id,
            'scheduled_date' => '2026-04-01',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_maintenances(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/maintenances');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/maintenances');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_title(): void
    {
        Maintenance::factory()->create(['title' => 'Vérification câblage']);
        Maintenance::factory()->create(['title' => 'Mise à jour firmware']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/maintenances?search=firmware');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }
}
