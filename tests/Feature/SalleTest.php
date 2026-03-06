<?php

namespace Tests\Feature;

use App\Models\Batiment;
use App\Models\Salle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalleTest extends TestCase
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

    public function test_can_list_salles(): void
    {
        Salle::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/salles');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_can_filter_salles_by_batiment_id(): void
    {
        $batiment = Batiment::factory()->create();
        Salle::factory()->count(2)->create(['batiment_id' => $batiment->id]);
        Salle::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/salles?batiment_id={$batiment->id}");

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertEquals($batiment->id, $item['batiment_id']);
        }
    }

    public function test_can_filter_salles_by_status(): void
    {
        Salle::factory()->create(['status' => 'active']);
        Salle::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/salles?status=active');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_can_create_salle(): void
    {
        $batiment = Batiment::factory()->create();

        $data = [
            'code' => 'SAL-001',
            'name' => 'Salle Test',
            'batiment_id' => $batiment->id,
            'floor' => 'RDC',
            'type' => 'salle_serveur',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/salles', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Salle Test');
        $this->assertDatabaseHas('salles', ['code' => 'SAL-001']);
    }

    public function test_can_show_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $salle->id);
    }

    public function test_can_update_salle(): void
    {
        $salle = Salle::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/salles/{$salle->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('salles', ['id' => $salle->id, 'name' => 'New Name']);
    }

    public function test_can_delete_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('salles', ['id' => $salle->id]);
    }

    public function test_create_salle_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/salles', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'batiment_id']);
    }

    public function test_create_salle_unique_code(): void
    {
        Salle::factory()->create(['code' => 'SAL-DUP']);

        $batiment = Batiment::factory()->create();
        $response = $this->actingAs($this->admin)->postJson('/api/v1/salles', [
            'code' => 'SAL-DUP',
            'name' => 'Duplicate',
            'batiment_id' => $batiment->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    public function test_unauthorized_user_cannot_create_salle(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/salles', [
            'code' => 'SAL-001',
            'name' => 'Test',
            'batiment_id' => $batiment->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_read_salles(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/salles');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/salles');

        $response->assertStatus(401);
    }

    public function test_index_searches_by_name(): void
    {
        Salle::factory()->create(['name' => 'Salle Alpha']);
        Salle::factory()->create(['name' => 'Salle Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/salles?search=Alpha');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        $this->assertCount(1, $items);
    }
}
