<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\Coffret;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChangeRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $technicien;
    private User $directeur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->technicien = User::factory()->create(['role' => 'technicien', 'is_active' => true]);
        $this->directeur = User::factory()->create(['role' => 'directeur', 'is_active' => true]);
    }

    // ---- LIST ----

    public function test_admin_can_list_all_change_requests(): void
    {
        ChangeRequest::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/change-requests');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_technicien_sees_only_own_change_requests(): void
    {
        ChangeRequest::factory()->create(['requester_id' => $this->technicien->id]);
        ChangeRequest::factory()->create(); // other user

        $response = $this->actingAs($this->technicien)->getJson('/api/v1/change-requests');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data.data'));
    }

    public function test_can_filter_by_status(): void
    {
        ChangeRequest::factory()->create(['status' => 'en_attente']);
        ChangeRequest::factory()->approved()->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/change-requests?status=en_attente');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('en_attente', $item['status']);
        }
    }

    public function test_can_filter_by_type(): void
    {
        ChangeRequest::factory()->create(['type' => 'ajout_port']);
        ChangeRequest::factory()->create(['type' => 'suppression_port']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/change-requests?type=ajout_port');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('ajout_port', $item['type']);
        }
    }

    public function test_can_filter_by_coffret_id(): void
    {
        $coffret = Coffret::factory()->create();
        ChangeRequest::factory()->create(['coffret_id' => $coffret->id]);
        ChangeRequest::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/change-requests?coffret_id={$coffret->id}");

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals($coffret->id, $item['coffret_id']);
        }
    }

    // ---- CREATE ----

    public function test_technicien_can_create_change_request(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Ajout de 4 ports RJ45 supplémentaires',
            'justification' => 'Besoin de connecter de nouveaux équipements réseau',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'ajout_port')
            ->assertJsonPath('data.status', 'en_attente');
        $this->assertDatabaseHas('change_requests', ['coffret_id' => $coffret->id, 'type' => 'ajout_port']);
    }

    public function test_directeur_can_create_change_request(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'changement_statut',
            'description' => 'Mise en maintenance de la baie principale',
            'justification' => 'Remplacement préventif des connecteurs défectueux',
            'intervention_date' => now()->addDays(3)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(201);
    }

    public function test_user_cannot_create_change_request(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Ajout de 4 ports RJ45 supplémentaires',
            'justification' => 'Besoin de connecter de nouveaux équipements',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->user)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(403);
    }

    public function test_create_validates_required_fields(): void
    {
        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coffret_id', 'type', 'description', 'justification', 'intervention_date']);
    }

    public function test_duplicate_pending_request_for_same_coffret_rejected(): void
    {
        $coffret = Coffret::factory()->create();
        ChangeRequest::factory()->create(['coffret_id' => $coffret->id, 'status' => 'en_attente']);

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Deuxième demande sur la même baie test',
            'justification' => 'Justification de la deuxième demande test',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['coffret_id']);
    }

    public function test_snapshot_before_is_captured(): void
    {
        $coffret = Coffret::factory()->create(['name' => 'Baie Alpha']);

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_equipement',
            'description' => 'Ajout switch Cisco 2960 dans la baie',
            'justification' => 'Extension du réseau pour nouveau bâtiment',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(201);
        $snapshot = $response->json('data.snapshot_before');
        $this->assertArrayHasKey('name', $snapshot);
        $this->assertEquals('Baie Alpha', $snapshot['name']);
    }

    public function test_code_is_auto_generated(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Description de test pour la génération de code',
            'justification' => 'Justification suffisamment longue pour passer',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(201);
        $this->assertStringStartsWith('CR-', $response->json('data.code'));
    }

    public function test_can_upload_photo(): void
    {
        Storage::fake('public');
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Ajout de ports avec photo avant intervention',
            'justification' => 'Documentation photographique nécessaire pour suivi',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
            'photo_before' => UploadedFile::fake()->image('before.jpg'),
        ];

        $response = $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('data.photo_before'));
        Storage::disk('public')->assertExists($response->json('data.photo_before'));
    }

    // ---- SHOW ----

    public function test_show_returns_relations(): void
    {
        $changeRequest = ChangeRequest::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/change-requests/{$changeRequest->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['coffret', 'requester']]);
    }

    // ---- REVIEW ----

    public function test_admin_can_approve(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'approuvee',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('change_requests', ['id' => $changeRequest->id, 'status' => 'approuvee']);
    }

    public function test_admin_can_reject_with_comment(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'rejetee',
            'review_comment' => 'Justification insuffisante pour cette modification',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('change_requests', ['id' => $changeRequest->id, 'status' => 'rejetee']);
    }

    public function test_admin_can_request_revision(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'en_revision',
            'review_comment' => 'Veuillez préciser les détails de l\'intervention',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('change_requests', ['id' => $changeRequest->id, 'status' => 'en_revision']);
    }

    public function test_reject_requires_comment(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'rejetee',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['review_comment']);
    }

    public function test_non_admin_cannot_review(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->technicien)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'approuvee',
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_review_already_processed(): void
    {
        $changeRequest = ChangeRequest::factory()->approved()->create();

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'rejetee',
            'review_comment' => 'Tentative de re-review après approbation',
        ]);

        $response->assertStatus(422);
    }

    public function test_changement_statut_auto_applies(): void
    {
        $coffret = Coffret::factory()->create(['status' => 'active']);
        $changeRequest = ChangeRequest::factory()->create([
            'coffret_id' => $coffret->id,
            'type' => 'changement_statut',
            'status' => 'en_attente',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'approuvee',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('coffrets', ['id' => $coffret->id, 'status' => 'inactive']);
        $this->assertNotNull($response->json('data.snapshot_after'));
    }

    // ---- DELETE ----

    public function test_requester_can_delete_own_pending(): void
    {
        $changeRequest = ChangeRequest::factory()->create([
            'requester_id' => $this->technicien->id,
            'status' => 'en_attente',
        ]);

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/change-requests/{$changeRequest->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('change_requests', ['id' => $changeRequest->id]);
    }

    public function test_cannot_delete_approved_request(): void
    {
        $changeRequest = ChangeRequest::factory()->approved()->create([
            'requester_id' => $this->technicien->id,
        ]);

        $response = $this->actingAs($this->technicien)->deleteJson("/api/v1/change-requests/{$changeRequest->id}");

        $response->assertStatus(422);
    }

    public function test_admin_can_delete_any_pending(): void
    {
        $changeRequest = ChangeRequest::factory()->create(['status' => 'en_attente']);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/change-requests/{$changeRequest->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('change_requests', ['id' => $changeRequest->id]);
    }

    // ---- AUTH ----

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/change-requests');

        $response->assertStatus(401);
    }

    // ---- NOTIFICATION INTEGRATION ----

    public function test_creating_change_request_sends_notification_to_admins(): void
    {
        $coffret = Coffret::factory()->create();

        $data = [
            'coffret_id' => $coffret->id,
            'type' => 'ajout_port',
            'description' => 'Ajout de ports pour test notification',
            'justification' => 'Test de la notification lors de la création de demande',
            'intervention_date' => now()->addDays(5)->format('Y-m-d'),
        ];

        $this->actingAs($this->technicien)->postJson('/api/v1/change-requests', $data);

        $this->assertTrue(
            Notification::where('user_id', $this->admin->id)
                ->where('type', 'modification_request')
                ->exists()
        );
    }

    public function test_reviewing_change_request_sends_notification_to_requester(): void
    {
        $changeRequest = ChangeRequest::factory()->create([
            'requester_id' => $this->technicien->id,
            'status' => 'en_attente',
        ]);

        $this->actingAs($this->admin)->putJson("/api/v1/change-requests/{$changeRequest->id}/review", [
            'status' => 'approuvee',
        ]);

        $this->assertTrue(
            Notification::where('user_id', $this->technicien->id)
                ->where('type', 'modification_approved')
                ->exists()
        );
    }
}
