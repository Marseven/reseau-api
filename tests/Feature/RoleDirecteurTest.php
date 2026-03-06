<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Metric;
use App\Models\Port;
use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class RoleDirecteurTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    private $directeur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->directeur = $this->createDirecteur();
    }

    // ─── Auth ───────────────────────────────────────────────────────

    public function test_directeur_can_login(): void
    {
        $directeur = $this->createDirecteur(['username' => 'dir_login']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'dir_login',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_directeur_can_get_profile(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'directeur');
    }

    public function test_directeur_can_logout(): void
    {
        $token = $this->directeur->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    // ─── Stats (allowed) ────────────────────────────────────────────

    public function test_directeur_can_access_global_stats(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/stats/global');

        $response->assertStatus(200);
    }

    public function test_directeur_can_access_systems_by_type(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/stats/systems-by-type');

        $response->assertStatus(200);
    }

    public function test_directeur_can_access_equipements_by_coffret(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/stats/equipements-by-coffret');

        $response->assertStatus(200);
    }

    public function test_directeur_can_access_ports_by_vlan(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/stats/ports-by-vlan');

        $response->assertStatus(200);
    }

    // ─── Coffrets: index + show (allowed) ───────────────────────────

    public function test_directeur_can_list_coffrets(): void
    {
        Coffret::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/coffrets');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_directeur_can_show_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $coffret->id);
    }

    // ─── Coffrets: store + update (forbidden by isAdministrator) ────

    public function test_directeur_cannot_store_coffret(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/coffrets', [
            'code' => 'COF-DIR',
            'name' => 'Dir Coffret',
            'piece' => 'Salle D',
            'long' => 2.3,
            'lat' => 48.8,
        ]);

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_update_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'Updated']);

        $response->assertStatus(403);
    }

    // ─── Coffrets: destroy (allowed - no FormRequest) ───────────────

    public function test_directeur_can_destroy_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('coffrets', ['id' => $coffret->id]);
    }

    // ─── Equipements: index + show (allowed) ────────────────────────

    public function test_directeur_can_list_equipements(): void
    {
        Equipement::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/equipements');

        $response->assertStatus(200);
    }

    public function test_directeur_can_show_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
    }

    // ─── Equipements: store + update (forbidden) ────────────────────

    public function test_directeur_cannot_store_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-DIR-001',
            'name' => 'Dir Switch',
            'type' => 'switch',
            'coffret_id' => $coffret->id,
            'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_update_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/equipements/{$equipement->id}", ['name' => 'Updated']);

        $response->assertStatus(403);
    }

    // ─── Equipements: destroy (allowed) ─────────────────────────────

    public function test_directeur_can_destroy_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('equipements', ['id' => $equipement->id]);
    }

    // ─── Ports: index + show (allowed) ──────────────────────────────

    public function test_directeur_can_list_ports(): void
    {
        Port::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/ports');

        $response->assertStatus(200);
    }

    public function test_directeur_can_show_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200);
    }

    // ─── Ports: store + update (forbidden) ──────────────────────────

    public function test_directeur_cannot_store_port(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/ports', [
            'port_label' => 'P-DIR-001',
            'device_name' => 'SW-01',
            'poe_enabled' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_update_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/ports/{$port->id}", ['device_name' => 'Updated']);

        $response->assertStatus(403);
    }

    // ─── Ports: destroy (allowed) ───────────────────────────────────

    public function test_directeur_can_destroy_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ports', ['id' => $port->id]);
    }

    // ─── Metrics: full CRUD (authorize = true) ──────────────────────

    public function test_directeur_can_list_metrics(): void
    {
        Metric::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/metrics');

        $response->assertStatus(200);
    }

    public function test_directeur_can_show_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_metric(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/metrics', [
            'name' => 'Dir Metric',
            'type' => 'gauge',
            'coffret_id' => $coffret->id,
            'status' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('metrics', ['name' => 'Dir Metric']);
    }

    public function test_directeur_can_update_metric(): void
    {
        $metric = Metric::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/metrics/{$metric->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('metrics', ['id' => $metric->id, 'name' => 'New']);
    }

    public function test_directeur_can_destroy_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('metrics', ['id' => $metric->id]);
    }

    // ─── Liaisons: index + show (allowed) ───────────────────────────

    public function test_directeur_can_list_liaisons(): void
    {
        Liaison::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/liaisons');

        $response->assertStatus(200);
    }

    public function test_directeur_can_show_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200);
    }

    // ─── Liaisons: store + update (forbidden) ───────────────────────

    public function test_directeur_cannot_store_liaison(): void
    {
        $eq1 = Equipement::factory()->create();
        $eq2 = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/liaisons', [
            'from' => $eq1->id,
            'to' => $eq2->id,
            'label' => 'Dir Liaison',
            'media' => 'fibre',
            'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_update_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/liaisons/{$liaison->id}", ['label' => 'Updated']);

        $response->assertStatus(403);
    }

    // ─── Liaisons: destroy (allowed) ────────────────────────────────

    public function test_directeur_can_destroy_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('liaisons', ['id' => $liaison->id]);
    }

    // ─── Systems: index + show (allowed) ────────────────────────────

    public function test_directeur_can_list_systems(): void
    {
        System::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/systems');

        $response->assertStatus(200);
    }

    public function test_directeur_can_show_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200);
    }

    // ─── Systems: store + update (forbidden) ────────────────────────

    public function test_directeur_cannot_store_system(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/systems', [
            'name' => 'Dir NMS',
            'type' => 'monitoring',
            'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_update_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/systems/{$system->id}", ['name' => 'Updated']);

        $response->assertStatus(403);
    }

    // ─── Systems: destroy (allowed) ─────────────────────────────────

    public function test_directeur_can_destroy_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('systems', ['id' => $system->id]);
    }
}
