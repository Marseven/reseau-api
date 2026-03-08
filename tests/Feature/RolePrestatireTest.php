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

class RolePrestatireTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    private $prestataire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prestataire = $this->createPrestataire();
    }

    // ─── Auth (allowed) ─────────────────────────────────────────────

    public function test_prestataire_can_login(): void
    {
        $user = $this->createPrestataire(['username' => 'presta_login']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'presta_login',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_prestataire_can_get_profile(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_logout(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    // ─── Stats (forbidden) ──────────────────────────────────────────

    public function test_prestataire_forbidden_global_stats(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/stats/global');

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_systems_by_type(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/stats/systems-by-type');

        $response->assertStatus(403);
    }

    // ─── Read access (allowed) ──────────────────────────────────────

    public function test_prestataire_can_list_sites(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/sites');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_show_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->prestataire)->getJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_zones(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/zones');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_show_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->prestataire)->getJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_coffrets(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/coffrets');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_show_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->prestataire)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_equipements(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/equipements');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_show_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->prestataire)->getJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_ports(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/ports');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_liaisons(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/liaisons');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_metrics(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/metrics');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_systems(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/systems');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_batiments(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/batiments');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_salles(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/salles');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_vlans(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/vlans');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_maintenances(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/maintenances');

        $response->assertStatus(200);
    }

    public function test_prestataire_can_list_change_requests(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/change-requests');

        $response->assertStatus(200);
    }

    // ─── Write access (forbidden) ───────────────────────────────────

    public function test_prestataire_forbidden_create_site(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/sites', [
            'code' => 'SITE-1', 'name' => 'Test', 'country' => 'Gabon',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_coffret(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/coffrets', [
            'code' => 'COF-1', 'name' => 'Test', 'piece' => 'R-1',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-1', 'name' => 'Test', 'type' => 'Switch', 'classification' => 'IT', 'coffret_id' => $coffret->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_port(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/ports', [
            'port_label' => 'Gi0/1', 'device_name' => 'SW-1',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_update_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->prestataire)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'Updated']);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_delete_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->prestataire)
            ->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_liaison(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/liaisons', [
            'from' => 1, 'to' => 2, 'label' => 'test', 'media' => 'fibre',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_metric(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/metrics', [
            'name' => 'test', 'type' => 'temperature',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_system(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/systems', [
            'name' => 'test', 'type' => 'monitoring',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_zone(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/zones', [
            'code' => 'Z-1', 'name' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_batiment(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/batiments', [
            'code' => 'BAT-1', 'name' => 'Test', 'zone_id' => $zone->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_salle(): void
    {
        $batiment = Batiment::factory()->create();

        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/salles', [
            'code' => 'SAL-1', 'name' => 'Test', 'batiment_id' => $batiment->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_vlan(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/vlans', [
            'vlan_id' => 100, 'name' => 'Test',
        ]);

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_create_maintenance(): void
    {
        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/maintenances', [
            'code' => 'M-1', 'title' => 'Test', 'type' => 'corrective',
        ]);

        $response->assertStatus(403);
    }

    // ─── Change requests (forbidden) ────────────────────────────────

    public function test_prestataire_forbidden_create_change_request(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->prestataire)->postJson('/api/v1/change-requests', [
            'coffret_id' => $coffret->id,
            'type' => 'changement_statut',
            'description' => 'Test',
            'justification' => 'Test',
            'intervention_date' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertStatus(403);
    }

    // ─── Exports (forbidden) ────────────────────────────────────────

    public function test_prestataire_forbidden_export_csv(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/exports/equipements/csv');

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_export_pdf(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/exports/architecture/pdf');

        $response->assertStatus(403);
    }

    // ─── Reports (forbidden) ────────────────────────────────────────

    public function test_prestataire_forbidden_reports_summary(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/reports/summary');

        $response->assertStatus(403);
    }

    // ─── Users management (forbidden) ───────────────────────────────

    public function test_prestataire_forbidden_list_users(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/users');

        $response->assertStatus(403);
    }

    public function test_prestataire_forbidden_activity_logs(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/activity-logs');

        $response->assertStatus(403);
    }

    // ─── Topology (allowed) ─────────────────────────────────────────

    public function test_prestataire_can_access_topology(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/topology');

        $response->assertStatus(200);
    }

    // ─── Login audit own history (allowed) ──────────────────────────

    public function test_prestataire_can_access_own_login_history(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/login-audits/me');

        $response->assertStatus(200);
    }

    // ─── Login audit all (forbidden - admin only) ───────────────────

    public function test_prestataire_forbidden_all_login_audits(): void
    {
        $response = $this->actingAs($this->prestataire)->getJson('/api/v1/login-audits');

        $response->assertStatus(403);
    }
}
