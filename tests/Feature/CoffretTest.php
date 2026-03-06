<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoffretTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    public function test_index_returns_paginated_coffrets(): void
    {
        Coffret::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_index_filters_by_status(): void
    {
        Coffret::factory()->create(['status' => 'active']);
        Coffret::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets?status=active');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        foreach ($items as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_index_searches_by_name(): void
    {
        Coffret::factory()->create(['name' => 'Coffret Alpha']);
        Coffret::factory()->create(['name' => 'Coffret Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets?search=Alpha');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }

    public function test_store_creates_coffret(): void
    {
        $data = [
            'code' => 'COF-001',
            'name' => 'Test Coffret',
            'piece' => 'Salle A',
            'long' => 2.3488,
            'lat' => 48.8534,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/coffrets', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Coffret');
        $this->assertDatabaseHas('coffrets', ['name' => 'Test Coffret']);
    }

    public function test_store_forbidden_for_non_admin(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/coffrets', [
            'code' => 'COF-001',
            'name' => 'Test',
            'piece' => 'Salle A',
            'long' => 2.3,
            'lat' => 48.8,
        ]);

        $response->assertStatus(403);
    }

    public function test_show_returns_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $coffret->id);
    }

    public function test_update_modifies_coffret(): void
    {
        $coffret = Coffret::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coffrets', ['id' => $coffret->id, 'name' => 'New Name']);
    }

    public function test_destroy_soft_deletes_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('coffrets', ['id' => $coffret->id]);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/coffrets');

        $response->assertStatus(401);
    }

    public function test_user_can_read_coffrets(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/coffrets');

        $response->assertStatus(200);
    }

    public function test_user_cannot_write_coffrets(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/coffrets', [
            'code' => 'CAB-TEST', 'name' => 'Test', 'piece' => 'R-1',
        ]);

        $response->assertStatus(403);
    }
}
