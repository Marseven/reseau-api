<?php

namespace Tests\Feature;

use App\Models\Batiment;
use App\Models\Zone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatimentTest extends TestCase
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

    public function test_can_list_batiments(): void
    {
        Batiment::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/batiments');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_filter_batiments_by_zone_id(): void
    {
        $zone = Zone::factory()->create();
        Batiment::factory()->count(2)->create(['zone_id' => $zone->id]);
        Batiment::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/batiments?zone_id={$zone->id}");

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($zone->id, $item['zone_id']);
        }
    }

    public function test_can_filter_batiments_by_status(): void
    {
        Batiment::factory()->create(['status' => 'active']);
        Batiment::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/batiments?status=active');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_can_create_batiment(): void
    {
        $zone = Zone::factory()->create();

        $data = [
            'code' => 'BAT-001',
            'name' => 'Bâtiment Test',
            'zone_id' => $zone->id,
            'address' => '123 Rue Test',
            'floors_count' => 3,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/batiments', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Bâtiment Test');
        $this->assertDatabaseHas('batiments', ['code' => 'BAT-001']);
    }

    public function test_can_show_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $batiment->id);
    }

    public function test_can_update_batiment(): void
    {
        $batiment = Batiment::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/batiments/{$batiment->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('batiments', ['id' => $batiment->id, 'name' => 'New Name']);
    }

    public function test_can_delete_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('batiments', ['id' => $batiment->id]);
    }

    public function test_create_batiment_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/batiments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'zone_id']);
    }

    public function test_create_batiment_unique_code(): void
    {
        Batiment::factory()->create(['code' => 'BAT-DUP']);

        $zone = Zone::factory()->create();
        $response = $this->actingAs($this->admin)->postJson('/api/v1/batiments', [
            'code' => 'BAT-DUP',
            'name' => 'Duplicate',
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthorized_user_cannot_create_batiment(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/batiments', [
            'code' => 'BAT-001',
            'name' => 'Test',
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_batiments(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/batiments');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/batiments');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_name(): void
    {
        Batiment::factory()->create(['name' => 'Bâtiment Alpha']);
        Batiment::factory()->create(['name' => 'Bâtiment Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/batiments?search=Alpha');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }
}
