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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class RoleAdministratorTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdmin();
    }

    // ─── Auth ───────────────────────────────────────────────────────

    public function test_admin_can_login(): void
    {
        $admin = $this->createAdmin(['username' => 'admin_login']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin_login',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_admin_can_get_profile(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->admin->id);
    }

    public function test_admin_can_logout(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
    }

    // ─── Stats ──────────────────────────────────────────────────────

    public function test_admin_can_access_global_stats(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/stats/global');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data']);
    }

    public function test_admin_can_access_systems_by_type(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/stats/systems-by-type');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_equipements_by_coffret(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/stats/equipements-by-coffret');

        $response->assertStatus(200);
    }

    public function test_admin_can_access_ports_by_vlan(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/stats/ports-by-vlan');

        $response->assertStatus(200);
    }

    // ─── Coffrets CRUD ──────────────────────────────────────────────

    public function test_admin_can_list_coffrets(): void
    {
        Coffret::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_coffret(): void
    {
        $data = [
            'code' => 'COF-100',
            'name' => 'Admin Coffret',
            'piece' => 'Salle Z',
            'long' => 2.35,
            'lat' => 48.85,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/coffrets', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Admin Coffret');
        $this->assertDatabaseHas('coffrets', ['code' => 'COF-100']);
    }

    public function test_admin_can_show_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $coffret->id);
    }

    public function test_admin_can_update_coffret(): void
    {
        $coffret = Coffret::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/coffrets/{$coffret->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coffrets', ['id' => $coffret->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_coffret(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('coffrets', ['id' => $coffret->id]);
    }

    // ─── Equipements CRUD ───────────────────────────────────────────

    public function test_admin_can_list_equipements(): void
    {
        Equipement::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/equipements');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_equipement(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'equipement_code' => 'EQ-ADMIN-001',
            'name' => 'Admin Switch',
            'type' => 'switch',
            'coffret_id' => $coffret->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/equipements', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('equipements', ['equipement_code' => 'EQ-ADMIN-001']);
    }

    public function test_admin_can_show_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $equipement->id);
    }

    public function test_admin_can_update_equipement(): void
    {
        $equipement = Equipement::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/equipements/{$equipement->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('equipements', ['id' => $equipement->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_equipement(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/equipements/{$equipement->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('equipements', ['id' => $equipement->id]);
    }

    // ─── Ports CRUD ─────────────────────────────────────────────────

    public function test_admin_can_list_ports(): void
    {
        Port::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/ports');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_port(): void
    {
        $equipement = Equipement::factory()->create();

        $data = [
            'port_label' => 'P-ADMIN-001',
            'device_name' => 'SW-01',
            'poe_enabled' => true,
            'vlan' => '100',
            'speed' => '1Gbps',
            'connected_equipment_id' => $equipement->id,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/ports', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ports', ['port_label' => 'P-ADMIN-001']);
    }

    public function test_admin_can_show_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $port->id);
    }

    public function test_admin_can_update_port(): void
    {
        $port = Port::factory()->create(['device_name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/ports/{$port->id}", ['device_name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('ports', ['id' => $port->id, 'device_name' => 'New']);
    }

    public function test_admin_can_destroy_port(): void
    {
        $port = Port::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/ports/{$port->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('ports', ['id' => $port->id]);
    }

    // ─── Metrics CRUD ───────────────────────────────────────────────

    public function test_admin_can_list_metrics(): void
    {
        Metric::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/metrics');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_metric(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'name' => 'CPU Usage',
            'type' => 'gauge',
            'coffret_id' => $coffret->id,
            'status' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/metrics', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('metrics', ['name' => 'CPU Usage']);
    }

    public function test_admin_can_show_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $metric->id);
    }

    public function test_admin_can_update_metric(): void
    {
        $metric = Metric::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/metrics/{$metric->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('metrics', ['id' => $metric->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_metric(): void
    {
        $metric = Metric::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/metrics/{$metric->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('metrics', ['id' => $metric->id]);
    }

    // ─── Liaisons CRUD ──────────────────────────────────────────────

    public function test_admin_can_list_liaisons(): void
    {
        Liaison::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/liaisons');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_liaison(): void
    {
        $eq1 = Equipement::factory()->create();
        $eq2 = Equipement::factory()->create();

        $data = [
            'from' => $eq1->id,
            'to' => $eq2->id,
            'label' => 'Fibre Admin',
            'media' => 'fibre',
            'length' => 100,
            'status' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/liaisons', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('liaisons', ['label' => 'Fibre Admin']);
    }

    public function test_admin_can_show_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $liaison->id);
    }

    public function test_admin_can_update_liaison(): void
    {
        $liaison = Liaison::factory()->create(['label' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/liaisons/{$liaison->id}", ['label' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('liaisons', ['id' => $liaison->id, 'label' => 'New']);
    }

    public function test_admin_can_destroy_liaison(): void
    {
        $liaison = Liaison::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/liaisons/{$liaison->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('liaisons', ['id' => $liaison->id]);
    }

    // ─── Systems CRUD ───────────────────────────────────────────────

    public function test_admin_can_list_systems(): void
    {
        System::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/systems');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_system_without_coffret_id(): void
    {
        $data = [
            'name' => 'Admin NMS',
            'type' => 'monitoring',
            'status' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/systems', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('systems', ['name' => 'Admin NMS']);
    }

    public function test_admin_can_show_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $system->id);
    }

    public function test_admin_can_update_system(): void
    {
        $system = System::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/systems/{$system->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('systems', ['id' => $system->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_system(): void
    {
        $system = System::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/systems/{$system->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('systems', ['id' => $system->id]);
    }

    // ─── Filters & Search ───────────────────────────────────────────

    public function test_admin_can_filter_coffrets_by_status(): void
    {
        Coffret::factory()->create(['status' => 'active']);
        Coffret::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets?status=active');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_admin_can_search_coffrets_by_name(): void
    {
        Coffret::factory()->create(['name' => 'Coffret Alpha']);
        Coffret::factory()->create(['name' => 'Coffret Beta']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/coffrets?search=Alpha');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_admin_can_filter_ports_by_vlan(): void
    {
        Port::factory()->create(['vlan' => '100']);
        Port::factory()->create(['vlan' => '200']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/ports?vlan=100');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('100', $item['vlan']);
        }
    }

    // ─── Sites CRUD ────────────────────────────────────────────────

    public function test_admin_can_list_sites(): void
    {
        Site::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/sites');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_site(): void
    {
        $data = [
            'code' => 'SITE-ADM',
            'name' => 'Admin Site',
            'city' => 'Libreville',
            'country' => 'Gabon',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/sites', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('sites', ['code' => 'SITE-ADM']);
    }

    public function test_admin_can_show_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $site->id);
    }

    public function test_admin_can_update_site(): void
    {
        $site = Site::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/sites/{$site->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/sites/{$site->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('sites', ['id' => $site->id]);
    }

    // ─── Zones CRUD ────────────────────────────────────────────────

    public function test_admin_can_list_zones(): void
    {
        Zone::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/zones');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_zone(): void
    {
        $site = Site::factory()->create();

        $data = [
            'code' => 'ZONE-ADM',
            'name' => 'Admin Zone',
            'site_id' => $site->id,
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/zones', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('zones', ['code' => 'ZONE-ADM']);
    }

    public function test_admin_can_show_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $zone->id);
    }

    public function test_admin_can_update_zone(): void
    {
        $zone = Zone::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/zones/{$zone->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('zones', ['id' => $zone->id, 'name' => 'New']);
    }

    public function test_admin_can_destroy_zone(): void
    {
        $zone = Zone::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/zones/{$zone->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('zones', ['id' => $zone->id]);
    }

    // ─── Users CRUD (admin only) ───────────────────────────────────

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_store_user(): void
    {
        $data = [
            'name' => 'New',
            'surname' => 'User',
            'username' => 'newuser',
            'email' => 'new@example.com',
            'role' => 'technicien',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['username' => 'newuser']);
    }

    public function test_admin_can_show_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_admin_can_update_user(): void
    {
        $user = User::factory()->create(['name' => 'Old']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/users/{$user->id}", ['name' => 'New']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New']);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }
}
