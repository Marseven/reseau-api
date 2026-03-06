<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZoneTest extends TestCase
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

    public function test_can_list_zones(): void
    {
        Zone::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/zones');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_filter_zones_by_site_id(): void
    {
        $site = Site::factory()->create();
        Zone::factory()->count(2)->create(['site_id' => $site->id]);
        Zone::factory()->create(); // different site

        $response = $this->actingAs($this->admin)->getJson("/api/v1/zones?site_id={$site->id}");

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($site->id, $item['site_id']);
        }
    }

    public function test_can_create_zone(): void
    {
        $site = Site::factory()->create();

        $data = [
            'code' => 'ZONE-001',
            'name' => 'Zone Test',
            'floor' => 'RDC',
            'building' => 'Bâtiment A',
            'site_id' => $site->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/zones', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Zone Test');
        $this->assertDatabaseHas('zones', ['code' => 'ZONE-001']);
    }

    public function test_can_show_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $zone->id);
    }

    public function test_can_update_zone(): void
    {
        $zone = Zone::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/zones/{$zone->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'name' => 'New Name']);
    }

    public function test_can_delete_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('zones', ['id' => $zone->id]);
    }

    public function test_create_zone_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/zones', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'site_id']);
    }

    public function test_create_zone_unique_code(): void
    {
        Zone::factory()->create(['code' => 'ZONE-DUP']);

        $site = Site::factory()->create();
        $response = $this->actingAs($this->admin)->postJson('/api/v1/zones', [
            'code' => 'ZONE-DUP',
            'name' => 'Duplicate Zone',
            'site_id' => $site->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthorized_user_cannot_create_zone(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/zones', [
            'code' => 'ZONE-001',
            'name' => 'Test',
            'site_id' => $site->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_zones(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/zones');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/zones');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_name(): void
    {
        Zone::factory()->create(['name' => 'Zone Alpha']);
        Zone::factory()->create(['name' => 'Zone Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/zones?search=Alpha');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }
}
