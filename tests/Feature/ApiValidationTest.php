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

class ApiValidationTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    // ─── Unauthenticated access → 401 ──────────────────────────────

    public function test_unauthenticated_cannot_access_me(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_logout(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_coffrets(): void
    {
        $this->getJson('/api/v1/coffrets')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_equipements(): void
    {
        $this->getJson('/api/v1/equipements')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_ports(): void
    {
        $this->getJson('/api/v1/ports')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_metrics(): void
    {
        $this->getJson('/api/v1/metrics')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_liaisons(): void
    {
        $this->getJson('/api/v1/liaisons')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_systems(): void
    {
        $this->getJson('/api/v1/systems')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_stats(): void
    {
        $this->getJson('/api/v1/stats/global')->assertStatus(401);
    }

    public function test_unauthenticated_cannot_store_coffret(): void
    {
        $this->postJson('/api/v1/coffrets', [])->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_2fa_setup(): void
    {
        $this->postJson('/api/v1/auth/2fa/setup')->assertStatus(401);
    }

    // ─── Validation errors → 422 ───────────────────────────────────

    public function test_login_validation_requires_fields(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_store_coffret_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/coffrets', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['code', 'name', 'piece', 'long', 'lat']);
    }

    public function test_store_equipement_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/equipements', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['equipement_code', 'name', 'type', 'coffret_id', 'status']);
    }

    public function test_store_port_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/ports', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['port_label', 'device_name', 'poe_enabled']);
    }

    public function test_store_metric_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/metrics', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'coffret_id', 'status']);
    }

    public function test_store_liaison_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/liaisons', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to', 'label', 'media', 'status']);
    }

    public function test_store_system_validation_requires_fields(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/systems', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'type', 'status']);
    }

    public function test_store_equipement_validates_ip_address(): void
    {
        $admin = $this->createAdmin();
        $coffret = Coffret::factory()->create();

        $this->actingAs($admin)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-VAL-001',
            'name' => 'Test',
            'type' => 'switch',
            'ip_address' => 'not-an-ip',
            'coffret_id' => $coffret->id,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['ip_address']);
    }

    public function test_store_equipement_validates_unique_code(): void
    {
        $admin = $this->createAdmin();
        $coffret = Coffret::factory()->create();
        Equipement::factory()->create(['equipement_code' => 'EQ-DUP']);

        $this->actingAs($admin)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-DUP',
            'name' => 'Test',
            'type' => 'switch',
            'coffret_id' => $coffret->id,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['equipement_code']);
    }

    public function test_store_equipement_validates_coffret_exists(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/equipements', [
            'equipement_code' => 'EQ-NOREF',
            'name' => 'Test',
            'type' => 'switch',
            'coffret_id' => 99999,
            'status' => 'active',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['coffret_id']);
    }

    public function test_store_coffret_validates_status_enum(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->postJson('/api/v1/coffrets', [
            'code' => 'COF-VAL',
            'name' => 'Test',
            'piece' => 'Salle A',
            'long' => 2.3,
            'lat' => 48.8,
            'status' => 'invalid_status',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // ─── Pagination ─────────────────────────────────────────────────

    public function test_coffrets_default_pagination(): void
    {
        $admin = $this->createAdmin();
        Coffret::factory()->count(20)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/coffrets');

        $response->assertStatus(200);
        $this->assertCount(15, $response->json('data.data'));
        $this->assertEquals(20, $response->json('data.total'));
    }

    public function test_coffrets_custom_pagination(): void
    {
        $admin = $this->createAdmin();
        Coffret::factory()->count(10)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/coffrets?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    public function test_equipements_pagination(): void
    {
        $admin = $this->createAdmin();
        Equipement::factory()->count(20)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/equipements?per_page=5');

        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data.data'));
    }

    // ─── Filters ────────────────────────────────────────────────────

    public function test_coffrets_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        Coffret::factory()->count(3)->create(['status' => 'active']);
        Coffret::factory()->count(2)->create(['status' => 'inactive']);

        $response = $this->actingAs($admin)->getJson('/api/v1/coffrets?status=active');

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_coffrets_search_by_name(): void
    {
        $admin = $this->createAdmin();
        Coffret::factory()->create(['name' => 'Coffret Unique']);
        Coffret::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/coffrets?search=Unique');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_equipements_filter_by_status(): void
    {
        $admin = $this->createAdmin();
        Equipement::factory()->count(2)->create(['status' => 'active']);
        Equipement::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($admin)->getJson('/api/v1/equipements?status=active');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('active', $item['status']);
        }
    }

    public function test_ports_filter_by_vlan(): void
    {
        $admin = $this->createAdmin();
        Port::factory()->count(2)->create(['vlan' => '100']);
        Port::factory()->create(['vlan' => '200']);

        $response = $this->actingAs($admin)->getJson('/api/v1/ports?vlan=100');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.data'));
    }

    public function test_liaisons_search_by_label(): void
    {
        $admin = $this->createAdmin();
        Liaison::factory()->create(['label' => 'Fibre Unique']);
        Liaison::factory()->count(2)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/liaisons?search=Unique');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    // ─── Soft Delete verification ───────────────────────────────────

    public function test_soft_delete_coffret(): void
    {
        $admin = $this->createAdmin();
        $coffret = Coffret::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/coffrets/{$coffret->id}")->assertStatus(200);

        $this->assertSoftDeleted('coffrets', ['id' => $coffret->id]);
        $this->assertDatabaseHas('coffrets', ['id' => $coffret->id]);
    }

    public function test_soft_delete_equipement(): void
    {
        $admin = $this->createAdmin();
        $equipement = Equipement::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/equipements/{$equipement->id}")->assertStatus(200);

        $this->assertSoftDeleted('equipements', ['id' => $equipement->id]);
    }

    public function test_soft_delete_port(): void
    {
        $admin = $this->createAdmin();
        $port = Port::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/ports/{$port->id}")->assertStatus(200);

        $this->assertSoftDeleted('ports', ['id' => $port->id]);
    }

    public function test_soft_delete_metric(): void
    {
        $admin = $this->createAdmin();
        $metric = Metric::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/metrics/{$metric->id}")->assertStatus(200);

        $this->assertSoftDeleted('metrics', ['id' => $metric->id]);
    }

    public function test_soft_delete_liaison(): void
    {
        $admin = $this->createAdmin();
        $liaison = Liaison::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/liaisons/{$liaison->id}")->assertStatus(200);

        $this->assertSoftDeleted('liaisons', ['id' => $liaison->id]);
    }

    public function test_soft_delete_system(): void
    {
        $admin = $this->createAdmin();
        $system = System::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/v1/systems/{$system->id}")->assertStatus(200);

        $this->assertSoftDeleted('systems', ['id' => $system->id]);
    }

    // ─── API response format ────────────────────────────────────────

    public function test_success_response_format(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data', 'message']);
    }

    public function test_created_response_format(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson('/api/v1/coffrets', [
            'code' => 'COF-FMT',
            'name' => 'Format Test',
            'piece' => 'Salle A',
            'long' => 2.3,
            'lat' => 48.8,
            'status' => 'active',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'data', 'message']);
    }

    public function test_error_response_format(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
            ->assertJsonStructure(['message']);
    }
}
