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

class RoleUserTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    // ─── Auth (allowed) ─────────────────────────────────────────────

    public function test_user_can_login(): void
    {
        $user = $this->createUser(['username' => 'user_login']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'user_login',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_user_can_get_profile(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.role', 'user');
    }

    public function test_user_can_logout(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    // ─── Stats (forbidden) ──────────────────────────────────────────

    public function test_user_cannot_access_global_stats(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/stats/global');

        $response->assertStatus(403);
    }

    public function test_user_cannot_access_systems_by_type(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/stats/systems-by-type');

        $response->assertStatus(403);
    }

    public function test_user_cannot_access_equipements_by_coffret(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/stats/equipements-by-coffret');

        $response->assertStatus(403);
    }

    public function test_user_cannot_access_ports_by_vlan(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/stats/ports-by-vlan');

        $response->assertStatus(403);
    }

    // ─── Coffrets (read allowed, write forbidden) ─────────────────

    public function test_user_can_list_coffrets(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/coffrets');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_coffret(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/coffrets', [
            'code' => 'COF-U', 'name' => 'U', 'piece' => 'S', 'long' => 0, 'lat' => 0,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(403);
    }

    // ─── Equipements (read allowed, write forbidden) ──────────────

    public function test_user_can_list_equipements(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/equipements');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-U', 'name' => 'U', 'type' => 'u',
            'coffret_id' => $coffret->id, 'status' => 'active',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/equipements/{$equipement->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(403);
    }

    // ─── Ports (read allowed, write forbidden) ────────────────────

    public function test_user_can_list_ports(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/ports');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_port(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/ports', [
            'port_label' => 'P-U', 'device_name' => 'SW', 'poe_enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/ports/{$port->id}", ['device_name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(403);
    }

    // ─── Metrics (read allowed, write forbidden) ──────────────────

    public function test_user_can_list_metrics(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/metrics');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_metric(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/metrics', [
            'name' => 'U', 'type' => 'gauge', 'coffret_id' => $coffret->id, 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/metrics/{$metric->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(403);
    }

    // ─── Liaisons (read allowed, write forbidden) ─────────────────

    public function test_user_can_list_liaisons(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/liaisons');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_liaison(): void
    {
        $eq1 = Equipement::factory()->create();
        $eq2 = Equipement::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/liaisons', [
            'from' => $eq1->id, 'to' => $eq2->id, 'label' => 'U',
            'media' => 'fibre', 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/liaisons/{$liaison->id}", ['label' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(403);
    }

    // ─── Systems (read allowed, write forbidden) ──────────────────

    public function test_user_can_list_systems(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/systems');

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_system(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/systems', [
            'name' => 'U', 'type' => 'monitoring', 'status' => true,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_show_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_update_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/systems/{$system->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(403);
    }

    // ─── Sites (read allowed, write forbidden) ────────────────────

    public function test_user_can_list_sites(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/sites');

        $response->assertStatus(200);
    }

    public function test_user_can_show_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_site(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/sites', [
            'code' => 'SITE-U', 'name' => 'U',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/sites/{$site->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(403);
    }

    // ─── Zones (read allowed, write forbidden) ────────────────────

    public function test_user_can_list_zones(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/zones');

        $response->assertStatus(200);
    }

    public function test_user_can_show_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_zone(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/zones', [
            'code' => 'ZONE-U', 'name' => 'U', 'site_id' => $site->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/zones/{$zone->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(403);
    }

    // ─── Batiments (read allowed, write forbidden) ──────────────

    public function test_user_can_list_batiments(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/batiments');

        $response->assertStatus(200);
    }

    public function test_user_can_show_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_batiment(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/batiments', [
            'code' => 'BAT-U', 'name' => 'U', 'zone_id' => $zone->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/batiments/{$batiment->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_batiment(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/batiments/{$batiment->id}");

        $response->assertStatus(403);
    }

    // ─── Salles (read allowed, write forbidden) ────────────────────

    public function test_user_can_list_salles(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/salles');

        $response->assertStatus(200);
    }

    public function test_user_can_show_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_salle(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/salles', [
            'code' => 'SAL-U', 'name' => 'U', 'batiment_id' => $batiment->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/salles/{$salle->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_salle(): void
    {
        $salle = Salle::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/salles/{$salle->id}");

        $response->assertStatus(403);
    }

    // ─── VLANs (read allowed, write forbidden) ─────────────────────

    public function test_user_can_list_vlans(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/vlans');

        $response->assertStatus(200);
    }

    public function test_user_can_show_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_vlan(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/vlans', [
            'vlan_id' => 999, 'name' => 'U',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/vlans/{$vlan->id}", ['name' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_vlan(): void
    {
        $vlan = Vlan::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/vlans/{$vlan->id}");

        $response->assertStatus(403);
    }

    // ─── Maintenances (read allowed, write forbidden) ──────────────

    public function test_user_can_list_maintenances(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/maintenances');

        $response->assertStatus(200);
    }

    public function test_user_can_show_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->user)->getJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(200);
    }

    public function test_user_cannot_store_maintenance(): void
    {
        $technicien = User::factory()->create(['role' => 'technicien']);

        $response = $this->actingAs($this->user)->postJson('/api/v1/maintenances', [
            'code' => 'MAINT-U', 'title' => 'U', 'type' => 'preventive',
            'priority' => 'basse', 'technicien_id' => $technicien->id,
            'scheduled_date' => '2026-04-01',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson("/api/v1/maintenances/{$maintenance->id}", ['title' => 'X']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_destroy_maintenance(): void
    {
        $maintenance = Maintenance::factory()->create();

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/maintenances/{$maintenance->id}");

        $response->assertStatus(403);
    }

    // ─── Users (forbidden) ────────────────────────────────────────

    public function test_user_cannot_list_users(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_user_cannot_store_user(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/users', [
            'name' => 'T', 'surname' => 'U', 'username' => 'tu',
            'email' => 'tu@example.com', 'role' => 'user',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(403);
    }
}
