<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
    }

    public function test_resolve_coffret_by_valid_qr_token(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/qr/coffret/{$coffret->qr_token}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $coffret->id)
            ->assertJsonStructure(['status', 'data' => ['id', 'name', 'qr_token', 'equipments']]);
    }

    public function test_resolve_equipement_by_valid_qr_token(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->actingAs($this->admin)
            ->getJson("/api/v1/qr/equipement/{$equipement->qr_token}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $equipement->id)
            ->assertJsonStructure(['status', 'data' => ['id', 'name', 'qr_token', 'ports']]);
    }

    public function test_invalid_coffret_qr_token_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/qr/coffret/invalid-token-here');

        $response->assertStatus(404);
    }

    public function test_invalid_equipement_qr_token_returns_404(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/qr/equipement/invalid-token-here');

        $response->assertStatus(404);
    }

    public function test_coffret_qr_route_requires_auth(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->getJson("/api/v1/qr/coffret/{$coffret->qr_token}");

        $response->assertStatus(401);
    }

    public function test_equipement_qr_route_requires_auth(): void
    {
        $equipement = Equipement::factory()->create();

        $response = $this->getJson("/api/v1/qr/equipement/{$equipement->qr_token}");

        $response->assertStatus(401);
    }

    public function test_all_roles_can_access_qr_routes(): void
    {
        $coffret = Coffret::factory()->create();
        $equipement = Equipement::factory()->create();

        foreach (['administrator', 'directeur', 'technicien', 'user'] as $role) {
            $user = User::factory()->create(['role' => $role, 'is_active' => true]);

            $this->actingAs($user)
                ->getJson("/api/v1/qr/coffret/{$coffret->qr_token}")
                ->assertStatus(200);

            $this->actingAs($user)
                ->getJson("/api/v1/qr/equipement/{$equipement->qr_token}")
                ->assertStatus(200);
        }
    }

    public function test_coffret_auto_generates_qr_token_on_creation(): void
    {
        $coffret = Coffret::factory()->create(['qr_token' => null]);

        $this->assertNotNull($coffret->fresh()->qr_token);
    }

    public function test_equipement_auto_generates_qr_token_on_creation(): void
    {
        $equipement = Equipement::factory()->create(['qr_token' => null]);

        $this->assertNotNull($equipement->fresh()->qr_token);
    }

    public function test_two_coffrets_have_different_qr_tokens(): void
    {
        $a = Coffret::factory()->create(['qr_token' => null]);
        $b = Coffret::factory()->create(['qr_token' => null]);

        $this->assertNotEquals($a->fresh()->qr_token, $b->fresh()->qr_token);
    }

    public function test_backfill_command_fills_null_tokens(): void
    {
        // Insert rows with null qr_token bypassing the boot event
        $coffret = Coffret::factory()->create();
        $equipement = Equipement::factory()->create();

        // Force null to simulate legacy data
        Coffret::where('id', $coffret->id)->update(['qr_token' => null]);
        Equipement::where('id', $equipement->id)->update(['qr_token' => null]);

        $this->assertNull($coffret->fresh()->qr_token);
        $this->assertNull($equipement->fresh()->qr_token);

        Artisan::call('qr:backfill');

        $this->assertNotNull($coffret->fresh()->qr_token);
        $this->assertNotNull($equipement->fresh()->qr_token);
    }
}
