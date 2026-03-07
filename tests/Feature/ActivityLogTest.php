<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private User $directeur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $this->directeur = User::factory()->create(['role' => 'directeur', 'is_active' => true]);
    }

    public function test_creating_coffret_creates_activity_log(): void
    {
        $this->actingAs($this->admin)->postJson('/api/v1/coffrets', [
            'code' => 'COF-001',
            'name' => 'Coffret Test Log',
            'piece' => 'Salle A',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'created',
            'entity_type' => Coffret::class,
            'user_id' => $this->admin->id,
        ]);
    }

    public function test_updating_coffret_logs_old_and_new_values(): void
    {
        $coffret = Coffret::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin)->putJson("/api/v1/coffrets/{$coffret->id}", [
            'name' => 'New Name',
        ]);

        $log = ActivityLog::where('entity_type', Coffret::class)
            ->where('entity_id', $coffret->id)
            ->where('action', 'updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals('Old Name', $log->old_values['name']);
        $this->assertEquals('New Name', $log->new_values['name']);
    }

    public function test_deleting_coffret_creates_activity_log(): void
    {
        $coffret = Coffret::factory()->create();

        $this->actingAs($this->admin)->deleteJson("/api/v1/coffrets/{$coffret->id}");

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'deleted',
            'entity_type' => Coffret::class,
            'entity_id' => $coffret->id,
        ]);
    }

    public function test_activity_log_captures_user_id(): void
    {
        $coffret = Coffret::factory()->create();

        $this->actingAs($this->directeur)->putJson("/api/v1/coffrets/{$coffret->id}", [
            'name' => 'Updated by directeur',
        ]);

        $log = ActivityLog::where('entity_type', Coffret::class)
            ->where('action', 'updated')
            ->first();

        $this->assertEquals($this->directeur->id, $log->user_id);
    }

    public function test_admin_can_list_activity_logs(): void
    {
        ActivityLog::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->getJson('/api/v1/activity-logs');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data']]);
    }

    public function test_can_filter_activity_logs_by_entity_type(): void
    {
        ActivityLog::factory()->create(['entity_type' => Coffret::class]);
        ActivityLog::factory()->create(['entity_type' => Equipement::class, 'entity_id' => Equipement::factory()]);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/activity-logs?entity_type=coffret');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals(Coffret::class, $item['entity_type']);
        }
    }

    public function test_non_admin_non_directeur_cannot_list_activity_logs(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/activity-logs');

        $response->assertStatus(403);
    }

    public function test_any_authenticated_user_can_view_coffret_history(): void
    {
        $coffret = Coffret::factory()->create();
        ActivityLog::factory()->create([
            'entity_type' => Coffret::class,
            'entity_id' => $coffret->id,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/v1/coffrets/{$coffret->id}/history");

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data']]);
    }

    public function test_coffret_history_includes_equipment_changes(): void
    {
        $coffret = Coffret::factory()->create();
        $equipement = Equipement::factory()->create(['coffret_id' => $coffret->id]);

        // LogsActivity trait auto-creates logs on create, so we verify
        // the history endpoint returns logs for both the coffret and its equipment
        $response = $this->actingAs($this->user)->getJson("/api/v1/coffrets/{$coffret->id}/history");

        $response->assertStatus(200);
        $items = $response->json('data.data');

        $entityTypes = array_column($items, 'entity_type');
        $this->assertContains(Coffret::class, $entityTypes);
        $this->assertContains(Equipement::class, $entityTypes);
    }
}
