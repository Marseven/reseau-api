<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
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

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/v1/users');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_create_user(): void
    {
        $site = Site::factory()->create();

        $data = [
            'name' => 'John',
            'surname' => 'Doe',
            'username' => 'johndoe',
            'email' => 'john@example.com',
            'phone' => '+241 01 23 45 67',
            'role' => 'technicien',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'site_id' => $site->id,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', $data);

        $response->assertStatus(201)
            ->assertJsonPath('data.username', 'johndoe');
        $this->assertDatabaseHas('users', ['username' => 'johndoe', 'email' => 'john@example.com']);
    }

    public function test_admin_can_show_user(): void
    {
        $target = User::factory()->create();

        $response = $this->actingAs($this->admin)->getJson("/api/v1/users/{$target->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $target->id);
    }

    public function test_admin_can_update_user(): void
    {
        $target = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/users/{$target->id}", ['name' => 'New Name']);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'name' => 'New Name']);
    }

    public function test_admin_can_deactivate_user(): void
    {
        $target = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/v1/users/{$target->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $target->id, 'is_active' => false]);
    }

    public function test_directeur_cannot_manage_users(): void
    {
        $response = $this->actingAs($this->directeur)->getJson('/api/v1/users');
        $response->assertStatus(403);

        $response = $this->actingAs($this->directeur)->postJson('/api/v1/users', [
            'name' => 'Test', 'surname' => 'User', 'username' => 'testuser',
            'email' => 'test@example.com', 'role' => 'user',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]);
        $response->assertStatus(403);
    }

    public function test_user_cannot_manage_users(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/v1/users');
        $response->assertStatus(403);
    }

    public function test_create_user_validation(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'surname', 'username', 'email', 'role', 'password']);
    }

    public function test_create_user_unique_email(): void
    {
        User::factory()->create(['email' => 'dup@example.com']);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', [
            'name' => 'Test', 'surname' => 'User', 'username' => 'unique_user',
            'email' => 'dup@example.com', 'role' => 'user',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_create_user_unique_username(): void
    {
        User::factory()->create(['username' => 'dupuser']);

        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', [
            'name' => 'Test', 'surname' => 'User', 'username' => 'dupuser',
            'email' => 'unique@example.com', 'role' => 'user',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['username']);
    }

    public function test_create_user_password_min_length(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/v1/users', [
            'name' => 'Test', 'surname' => 'User', 'username' => 'testuser',
            'email' => 'test@example.com', 'role' => 'user',
            'password' => 'short', 'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_unauthenticated_access_returns_401(): void
    {
        $response = $this->getJson('/api/v1/users');

        $response->assertStatus(401);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        User::factory()->create(['role' => 'technicien']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/users?role=technicien');

        $response->assertStatus(200);
        foreach ($response->json('data.data') as $item) {
            $this->assertEquals('technicien', $item['role']);
        }
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->create(['name' => 'UniqueSearchName']);

        $response = $this->actingAs($this->admin)->getJson('/api/v1/users?search=UniqueSearchName');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data.data')));
    }
}
