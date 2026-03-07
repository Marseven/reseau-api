<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Maintenance;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
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

    public function test_summary_returns_json_structure(): void
    {
        ActivityLog::factory()->count(3)->create();
        Maintenance::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/summary?from=2020-01-01&to=2030-12-31');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'period' => ['from', 'to'],
                    'modifications' => ['total', 'by_action'],
                    'interventions' => ['total', 'by_status'],
                    'sites_count',
                ],
                'message',
            ]);
    }

    public function test_directeur_can_access_reports(): void
    {
        $response = $this->actingAs($this->directeur)
            ->getJson('/api/v1/reports/summary');

        $response->assertStatus(200);
    }

    public function test_network_status_pdf_returns_200(): void
    {
        Site::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/reports/network-status/pdf');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_modifications_pdf_with_dates_returns_200(): void
    {
        ActivityLog::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/reports/modifications/pdf?from=2020-01-01&to=2030-12-31');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_modifications_pdf_without_dates_returns_422(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/modifications/pdf');

        $response->assertStatus(422);
    }

    public function test_interventions_pdf_returns_200(): void
    {
        Maintenance::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)
            ->get('/api/v1/reports/interventions/pdf');

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_site_architecture_pdf_returns_200(): void
    {
        $site = Site::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get("/api/v1/reports/site/{$site->id}/architecture/pdf");

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/reports/summary');

        $response->assertStatus(403);
    }
}
