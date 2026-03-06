<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Vlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VlanTest extends TestCase
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

    public function test_can_list_vlans(): void
    {
        Vlan::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/vlans');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_filter_vlans_by_site_id(): void
    {
        $site = Site::factory()->create();
        Vlan::factory()->count(2)->create(['site_id' => $site->id]);
        Vlan::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/vlans?site_id={$site->id}");

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($site->id, $item['site_id']);
        }
    }

    public function test_can_filter_vlans_by_status(): void
    {
        Vlan::factory()->create(['status' => 'active']);
        Vlan::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/vlans?status=active');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_can_create_vlan(): void
    {
        $site = Site::factory()->create();

        $data = [
            'vlan_id' => 50,
            'name' => 'VLAN Test',
            'site_id' => $site->id,
            'network' => '192.168.50.0/24',
            'gateway' => '192.168.50.1',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/vlans', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'VLAN Test');
        $this->assertDatabaseHas('vlans', ['vlan_id' => 50]);
    }

    public function test_can_show_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $vlan->id);
    }

    public function test_can_update_vlan(): void
    {
        $vlan = Vlan::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/vlans/{$vlan->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vlans', ['id' => $vlan->id, 'name' => 'New Name']);
    }

    public function test_can_delete_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('vlans', ['id' => $vlan->id]);
    }

    public function test_create_vlan_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/vlans', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vlan_id', 'name']);
    }

    public function test_create_vlan_unique_vlan_id(): void
    {
        Vlan::factory()->create(['vlan_id' => 100]);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/vlans', [
            'vlan_id' => 100,
            'name' => 'Duplicate',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['vlan_id']);
    }

    public function test_unauthorized_user_cannot_create_vlan(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/vlans', [
            'vlan_id' => 50,
            'name' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_vlans(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/vlans');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/vlans');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_name(): void
    {
        Vlan::factory()->create(['name' => 'VLAN-ALPHA']);
        Vlan::factory()->create(['name' => 'VLAN-BETA']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/vlans?search=ALPHA');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }
}
