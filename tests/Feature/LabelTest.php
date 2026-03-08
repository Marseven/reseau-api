<?php

namespace Tests\Feature;

use App\Models\Coffret;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $this->user = User::factory()->create(['role' => 'user', 'is_active' => true]);
    }

    public function test_generate_coffret_labels_pdf(): void
    {
        $coffrets = Coffret::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/coffrets', [
            'ids' => $coffrets->pluck('id')->toArray(),
            'format' => 'medium',
        ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_generate_equipement_labels_pdf(): void
    {
        $equipements = Equipement::factory()->count(2)->create();

        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/equipements', [
            'ids' => $equipements->pluck('id')->toArray(),
            'format' => 'small',
        ]);

        $response->assertStatus(200)
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_label_format_large(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/coffrets', [
            'ids' => [$coffret->id],
            'format' => 'large',
        ]);

        $response->assertStatus(200);
    }

    public function test_label_invalid_format(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/coffrets', [
            'ids' => [$coffret->id],
            'format' => 'xlarge',
        ]);

        $response->assertStatus(422);
    }

    public function test_label_empty_ids(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/coffrets', [
            'ids' => [],
            'format' => 'medium',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_generate_labels(): void
    {
        $coffret = Coffret::factory()->create();

        $response = $this->actingAs($this->user)->postJson('/api/v1/labels/coffrets', [
            'ids' => [$coffret->id],
            'format' => 'medium',
        ]);

        $response->assertStatus(403);
    }

    public function test_max_100_labels(): void
    {
        $ids = range(1, 101);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/labels/coffrets', [
            'ids' => $ids,
            'format' => 'medium',
        ]);

        $response->assertStatus(422);
    }
}
