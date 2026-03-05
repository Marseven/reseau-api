<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => 'administrator',
            'is_active' => true,
        ], $overrides));
    }

    public function test_login_with_valid_credentials(): void
    {
        $user = $this->createUser(['username' => 'admin', 'email' => 'admin@test.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['user', 'token'], 'message']);
    }

    public function test_login_with_email(): void
    {
        $user = $this->createUser(['email' => 'admin@test.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.email', 'admin@test.com');
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $this->createUser(['username' => 'admin']);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_inactive_user_returns_401(): void
    {
        $this->createUser(['username' => 'inactive', 'is_active' => false]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'inactive',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validation_requires_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $user->id);
    }

    public function test_me_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_logout_deletes_token(): void
    {
        $user = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
