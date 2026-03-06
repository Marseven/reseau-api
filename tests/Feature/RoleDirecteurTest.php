<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Metric;
use App\Models\Port;
use App\Models\Site;
use App\Models\System;
use App\Models\User;
use App\Models\Zone;
use App\Models\Batiment;
use App\Models\Salle;
use App\Models\Vlan;
use App\Models\Maintenance;
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

    // ─── Coffrets: full CRUD (allowed) ─────────────────────────────

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

    public function test_directeur_can_store_coffret(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/coffrets', [
            'code' => 'COF-DIR',
            'name' => 'Dir Coffret',
            'piece' => 'Salle D',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('coffrets', ['code' => 'COF-DIR']);
    }

    public function test_directeur_can_update_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coffrets', ['id' => $coffret->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('coffrets', ['id' => $coffret->id]);
    }

    // ─── Equipements: full CRUD (allowed) ──────────────────────────

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

    public function test_directeur_can_store_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-DIR-001',
            'name' => 'Dir Switch',
            'type' => 'switch',
            'coffret_id' => $coffret->id,
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('equipements', ['equipement_code' => 'EQ-DIR-001']);
    }

    public function test_directeur_can_update_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/equipements/{$equipement->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('equipements', ['id' => $equipement->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('equipements', ['id' => $equipement->id]);
    }

    // ─── Ports: full CRUD (allowed) ────────────────────────────────

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

    public function test_directeur_can_store_port(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/ports', [
            'port_label' => 'P-DIR-001',
            'device_name' => 'SW-01',
            'poe_enabled' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ports', ['port_label' => 'P-DIR-001']);
    }

    public function test_directeur_can_update_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/ports/{$port->id}", ['device_name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ports', ['id' => $port->id, 'device_name' => 'Updated']);
    }

    public function test_directeur_can_destroy_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ports', ['id' => $port->id]);
    }

    // ─── Metrics: full CRUD (allowed) ──────────────────────────────

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

    // ─── Liaisons: full CRUD (allowed) ─────────────────────────────

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

    public function test_directeur_can_store_liaison(): void
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

        $response->assertStatus(201);
        $this->assertDatabaseHas('liaisons', ['label' => 'Dir Liaison']);
    }

    public function test_directeur_can_update_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/liaisons/{$liaison->id}", ['label' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('liaisons', ['id' => $liaison->id, 'label' => 'Updated']);
    }

    public function test_directeur_can_destroy_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('liaisons', ['id' => $liaison->id]);
    }

    // ─── Systems: full CRUD (allowed) ──────────────────────────────

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

    public function test_directeur_can_store_system(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/systems', [
            'name' => 'Dir NMS',
            'type' => 'monitoring',
            'status' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('systems', ['name' => 'Dir NMS']);
    }

    public function test_directeur_can_update_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/systems/{$system->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('systems', ['id' => $system->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('systems', ['id' => $system->id]);
    }

    // ─── Sites: full CRUD (allowed) ────────────────────────────────

    public function test_directeur_can_list_sites(): void
    {
        Site::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/sites');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_site(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/sites', [
            'code' => 'SITE-DIR',
            'name' => 'Dir Site',
            'city' => 'Moanda',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sites', ['code' => 'SITE-DIR']);
    }

    public function test_directeur_can_show_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/sites/{$site->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('sites', ['id' => $site->id]);
    }

    // ─── Zones: full CRUD (allowed) ────────────────────────────────

    public function test_directeur_can_list_zones(): void
    {
        Zone::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/zones');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_zone(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/zones', [
            'code' => 'ZONE-DIR',
            'name' => 'Dir Zone',
            'site_id' => $site->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('zones', ['code' => 'ZONE-DIR']);
    }

    public function test_directeur_can_show_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/zones/{$zone->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('zones', ['id' => $zone->id]);
    }

    // ─── Batiments: full CRUD (allowed) ────────────────────────────

    public function test_directeur_can_list_batiments(): void
    {
        Batiment::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/batiments');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_batiment(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/batiments', [
            'code' => 'BAT-DIR',
            'name' => 'Dir Batiment',
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('batiments', ['code' => 'BAT-DIR']);
    }

    public function test_directeur_can_show_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/batiments/{$batiment->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('batiments', ['id' => $batiment->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('batiments', ['id' => $batiment->id]);
    }

    // ─── Salles: full CRUD (allowed) ────────────────────────────────

    public function test_directeur_can_list_salles(): void
    {
        Salle::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/salles');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_salle(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/salles', [
            'code' => 'SAL-DIR',
            'name' => 'Dir Salle',
            'batiment_id' => $batiment->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('salles', ['code' => 'SAL-DIR']);
    }

    public function test_directeur_can_show_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/salles/{$salle->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('salles', ['id' => $salle->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('salles', ['id' => $salle->id]);
    }

    // ─── VLANs: full CRUD (allowed) ────────────────────────────────

    public function test_directeur_can_list_vlans(): void
    {
        Vlan::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/vlans');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_vlan(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/vlans', [
            'vlan_id' => 600,
            'name' => 'Dir VLAN',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('vlans', ['vlan_id' => 600]);
    }

    public function test_directeur_can_show_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/vlans/{$vlan->id}", ['name' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vlans', ['id' => $vlan->id, 'name' => 'Updated']);
    }

    public function test_directeur_can_destroy_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('vlans', ['id' => $vlan->id]);
    }

    // ─── Maintenances: full CRUD (allowed) ──────────────────────────

    public function test_directeur_can_list_maintenances(): void
    {
        Maintenance::factory()->count(2)->create();

        $response = $this->actingAs($this->directeur)->getJson('/api/v1/maintenances');

        $response->assertStatus(200);
    }

    public function test_directeur_can_store_maintenance(): void
    {
        $technicien = User::factory()->create(['role' => 'technicien', 'is_active' => true]);

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/maintenances', [
            'code' => 'MAINT-DIR',
            'title' => 'Dir Maintenance',
            'type' => 'preventive',
            'priority' => 'basse',
            'technicien_id' => $technicien->id,
            'scheduled_date' => '2026-04-01',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('maintenances', ['code' => 'MAINT-DIR']);
    }

    public function test_directeur_can_show_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->directeur)->getJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(200);
    }

    public function test_directeur_can_update_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->directeur)
            ->putJson("/api/v1/maintenances/{$maintenance->id}", ['title' => 'Updated']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('maintenances', ['id' => $maintenance->id, 'title' => 'Updated']);
    }

    public function test_directeur_can_destroy_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->directeur)->deleteJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('maintenances', ['id' => $maintenance->id]);
    }

    // ─── Users: forbidden ──────────────────────────────────────────

    public function test_directeur_cannot_list_users(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_directeur_cannot_store_user(): void
    {
        $response = $this->actingAs($this->directeur)->postJson('/api/v1/users', [
            'name' => 'T', 'surname' => 'U', 'username' => 'tu',
            'email' => 'tu@example.com', 'role' => 'user',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(403);
    }
}
