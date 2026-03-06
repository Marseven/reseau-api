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

class RoleTechnicienTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    private $technicien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->technicien = $this->createTechnicien();
    }

    // ─── Auth (allowed) ─────────────────────────────────────────────

    public function test_technicien_can_login(): void
    {
        $tech = $this->createTechnicien(['username' => 'tech_login']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'tech_login',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_technicien_can_get_profile(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'technicien');
    }

    public function test_technicien_can_logout(): void
    {
        $token = $this->technicien->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    // ─── Stats (forbidden) ──────────────────────────────────────────

    public function test_technicien_cannot_access_global_stats(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/stats/global');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_access_systems_by_type(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/stats/systems-by-type');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_access_equipements_by_coffret(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/stats/equipements-by-coffret');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_access_ports_by_vlan(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/stats/ports-by-vlan');

        $response->assertStatus(403);
    }

    // ─── Coffrets (all forbidden) ───────────────────────────────────

    public function test_technicien_cannot_list_coffrets(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/coffrets');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_coffret(): void
    {
        $response = $this->actingAs($this->technicien)->postJson('/api/v1/coffrets', [
            'code' => 'COF-T', 'name' => 'T', 'piece' => 'S', 'long' => 0, 'lat' => 0,
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(403);
    }

    // ─── Equipements (all forbidden) ────────────────────────────────

    public function test_technicien_cannot_list_equipements(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/equipements');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-T', 'name' => 'T', 'type' => 't',
            'coffret_id' => $coffret->id, 'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/equipements/{$equipement->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(403);
    }

    // ─── Ports (all forbidden) ──────────────────────────────────────

    public function test_technicien_cannot_list_ports(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/ports');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_port(): void
    {
        $response = $this->actingAs($this->technicien)->postJson('/api/v1/ports', [
            'port_label' => 'P-T', 'device_name' => 'SW', 'poe_enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/ports/{$port->id}", ['device_name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(403);
    }

    // ─── Metrics (all forbidden) ────────────────────────────────────

    public function test_technicien_cannot_list_metrics(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/metrics');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_metric(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/metrics', [
            'name' => 'T', 'type' => 'gauge', 'coffret_id' => $coffret->id, 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/metrics/{$metric->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(403);
    }

    // ─── Liaisons (all forbidden) ───────────────────────────────────

    public function test_technicien_cannot_list_liaisons(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/liaisons');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_liaison(): void
    {
        $eq1 = Equipement::factory()->create();
        $eq2 = Equipement::factory()->create();

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/liaisons', [
            'from' => $eq1->id, 'to' => $eq2->id, 'label' => 'T',
            'media' => 'fibre', 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/liaisons/{$liaison->id}", ['label' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(403);
    }

    // ─── Systems (all forbidden) ────────────────────────────────────

    public function test_technicien_cannot_list_systems(): void
    {
        $response = $this->actingAs($this->technicien)->getJson('/api/v1/systems');

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_store_system(): void
    {
        $response = $this->actingAs($this->technicien)->postJson('/api/v1/systems', [
            'name' => 'T', 'type' => 'monitoring', 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_show_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->technicien)->getJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_update_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->technicien)
            ->putJson("/api/v1/systems/{$system->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_technicien_cannot_destroy_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(403);
    }
}
