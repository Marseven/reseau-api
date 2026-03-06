<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
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

    public function test_can_list_sites(): void
    {
        Site::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/sites');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_create_site(): void
    {
        $data = [
            'code' => 'SITE-001',
            'name' => 'Site Test',
            'address' => '123 Rue Test',
            'city' => 'Libreville',
            'country' => 'Gabon',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/sites', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Site Test');
        $this->assertDatabaseHas('sites', ['code' => 'SITE-001']);
    }

    public function test_can_show_site(): void
    {
        $site = Site::factory()->create();
        Zone::factory()->count(2)->create(['site_id' => $site->id]);

        $response = $this->actingAs($this->admin)->getJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $site->id)
            ->assertJsonCount(2, 'data.zones');
    }

    public function test_can_update_site(): void
    {
        $site = Site::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/sites/{$site->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'New Name']);
    }

    public function test_can_delete_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('sites', ['id' => $site->id]);
    }

    public function test_create_site_validation(): void
    {
        // Missing required fields
        $response = $this->actingAs($this->admin)->postJson('/api/v1/sites', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name']);
    }

    public function test_create_site_unique_code(): void
    {
        Site::factory()->create(['code' => 'SITE-DUP']);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/sites', [
            'code' => 'SITE-DUP',
            'name' => 'Duplicate Site',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthorized_user_cannot_create_site(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/sites', [
            'code' => 'SITE-001',
            'name' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_sites(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/sites');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/sites');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_name(): void
    {
        Site::factory()->create(['name' => 'Site Alpha']);
        Site::factory()->create(['name' => 'Site Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/sites?search=Alpha');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }

    public function test_index_filters_by_status(): void
    {
        Site::factory()->create(['status' => 'active']);
        Site::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/sites?status=active');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        foreach ($items as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }
}
