<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\Liaison;
use App\Models\Port;
use App\Models\Site;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $directeur;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->directeur = User::factory()->create(['role' => 'directeur', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    public function test_export_equipements_csv_returns_200(): void
    {
        Equipement::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/equipements/csv');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_coffrets_csv_returns_200(): void
    {
        Coffret::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/coffrets/csv');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_ports_csv_returns_200(): void
    {
        Port::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/ports/csv');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_liaisons_csv_returns_200(): void
    {
        Liaison::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/liaisons/csv');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_activity_logs_csv_with_dates_returns_200(): void
    {
        ActivityLog::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/activity-logs/csv?from=2020-01-01&to=2030-12-31');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_architecture_pdf_returns_200(): void
    {
        Site::factory()->create();

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/architecture/pdf');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_export_csv_contains_expected_data(): void
    {
        Equipement::factory()->create(['name' => 'Switch Alpha', 'status' => 'active']);

        $response = $this->actingAs($this->admin)->get('/api/v1/exports/equipements/csv');

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('Switch Alpha', $content);
        $this->assertStringContainsString('active', $content);
    }

    public function test_filter_site_id_on_equipements_csv(): void
    {
        $site = Site::factory()->create();
        $zone = Zone::factory()->create(['site_id' => $site->id]);
        $coffret = Coffret::factory()->create(['zone_id' => $zone->id]);
        Equipement::factory()->create(['coffret_id' => $coffret->id, 'name' => 'In Site']);
        Equipement::factory()->create(['name' => 'Other Site']);

        $response = $this->actingAs($this->admin)
            ->get("/api/v1/exports/equipements/csv?site_id={$site->id}");

        $response->assertStatus(200);
        $content = $response->streamedContent();
        $this->assertStringContainsString('In Site', $content);
        $this->assertStringNotContainsString('Other Site', $content);
    }

    public function test_user_cannot_export(): void
    {
        $response = $this->actingAs($this->user)->get('/api/v1/exports/equipements/csv');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_cannot_export(): void
    {
        $response = $this->getJson('/api/v1/exports/equipements/csv');

        $response->assertStatus(401);
    }

    public function test_directeur_can_export(): void
    {
        $response = $this->actingAs($this->directeur)->get('/api/v1/exports/coffrets/csv');

        $response->assertStatus(200);
    }
}
