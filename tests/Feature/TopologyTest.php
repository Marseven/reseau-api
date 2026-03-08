<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class TopologyTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_topology_returns_nodes_and_edges(): void
    {
        $admin = $this->createAdmin();
        $coffret = Coffret::factory()->create();
        $eq1 = Equipement::factory()->create(['coffret_id' => $coffret->id]);
        $eq2 = Equipement::factory()->create(['coffret_id' => $coffret->id]);
        Liaison::factory()->create(['from' => $eq1->id, 'to' => $eq2->id]);

        $response = $this->actingAs($admin)->getJson('/api/v1/topology');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['nodes', 'edges'], 'message']);
    }

    public function test_topology_filters_by_classification(): void
    {
        $admin = $this->createAdmin();
        Equipement::factory()->create(['classification' => 'IT']);
        Equipement::factory()->create(['classification' => 'OT']);

        $response = $this->actingAs($admin)->getJson('/api/v1/topology?classification=IT');

        $response->assertStatus(200);
        $nodes = $response->json('data.nodes');
        foreach ($nodes as $node) {
            $this->assertEquals('IT', $node['classification']);
        }
    }

    public function test_topology_filters_by_coffret(): void
    {
        $admin = $this->createAdmin();
        $coffret = Coffret::factory()->create();
        Equipement::factory()->create(['coffret_id' => $coffret->id]);
        Equipement::factory()->create(); // different coffret

        $response = $this->actingAs($admin)->getJson("/api/v1/topology?coffret_id={$coffret->id}");

        $response->assertStatus(200);
        $nodes = $response->json('data.nodes');
        foreach ($nodes as $node) {
            $this->assertEquals($coffret->id, $node['coffret_id']);
        }
    }

    public function test_topology_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/topology');

        $response->assertStatus(401);
    }

    public function test_prestataire_can_access_topology(): void
    {
        $prestataire = $this->createPrestataire();

        $response = $this->actingAs($prestataire)->getJson('/api/v1/topology');

        $response->assertStatus(200);
    }
}
