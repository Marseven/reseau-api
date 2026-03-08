<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Maintenance;
use App\Models\Port;
use App\Models\Site;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    public function test_equipements_by_type(): void
    {
        Equipement::factory()->count(3)->create(['type' => 'switch']);
        Equipement::factory()->count(2)->create(['type' => 'router']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/equipements-by-type');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_equipements_by_classification(): void
    {
        Equipement::factory()->count(3)->create(['classification' => 'IT']);
        Equipement::factory()->count(2)->create(['classification' => 'OT']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/equipements-by-classification');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_equipements_by_status(): void
    {
        Equipement::factory()->count(3)->create(['status' => 'active']);
        Equipement::factory()->count(1)->create(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/equipements-by-status');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_equipements_by_vendor(): void
    {
        Equipement::factory()->count(3)->create(['fabricant' => 'Cisco']);
        Equipement::factory()->count(2)->create(['fabricant' => 'HP']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/equipements-by-vendor');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($data));
    }

    public function test_maintenance_trends(): void
    {
        Maintenance::factory()->count(3)->create([
            'scheduled_date' => now()->subMonth(),
        ]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/maintenance-trends');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('month', $data[0]);
        $this->assertArrayHasKey('count', $data[0]);
    }

    public function test_port_utilization(): void
    {
        $equip = Equipement::factory()->create();
        Port::factory()->count(5)->create();
        Port::factory()->count(3)->create(['connected_equipment_id' => $equip->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/port-utilization');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('connected', $data);
        $this->assertArrayHasKey('free', $data);
        $this->assertArrayHasKey('utilization_percent', $data);
    }

    public function test_sites_summary(): void
    {
        $site = Site::factory()->create();
        Zone::factory()->create(['site_id' => $site->id]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/analytics/sites-summary');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('name', $data[0]);
    }

    public function test_user_cannot_access_analytics(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/analytics/equipements-by-type');

        $response->assertStatus(403);
    }

    public function test_analytics_response_is_cached(): void
    {
        Equipement::factory()->count(2)->create(['type' => 'switch']);

        $this->actingAs($this->admin)->getJson('/api/v1/analytics/equipements-by-type');
        $this->assertTrue(Cache::has('analytics.equipements_by_type'));
    }
}
