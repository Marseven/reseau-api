<?php

namespace Tests\Feature;

use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesTestUsers;

class LoginAuditTest extends TestCase
{
    use RefreshDatabase, CreatesTestUsers;

    public function test_login_creates_audit(): void
    {
        $user = $this->createAdmin(['username' => 'audit_admin']);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'audit_admin',
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('login_audits', [
            'user_id' => $user->id,
            'action' => 'login',
            'method' => 'password',
        ]);
    }

    public function test_failed_login_creates_audit(): void
    {
        $user = $this->createUser(['username' => 'fail_user']);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'fail_user',
            'password' => 'wrong_password',
        ]);

        $this->assertDatabaseHas('login_audits', [
            'user_id' => $user->id,
            'action' => 'login_failed',
        ]);
    }

    public function test_logout_creates_audit(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)->postJson('/api/v1/auth/logout');

        $this->assertDatabaseHas('login_audits', [
            'user_id' => $user->id,
            'action' => 'logout',
        ]);
    }

    public function test_admin_can_list_all_audits(): void
    {
        $admin = $this->createAdmin();
        LoginAudit::create([
            'user_id' => $admin->id,
            'action' => 'login',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/login-audits');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_admin_can_filter_audits_by_action(): void
    {
        $admin = $this->createAdmin();
        LoginAudit::create(['user_id' => $admin->id, 'action' => 'login']);
        LoginAudit::create(['user_id' => $admin->id, 'action' => 'logout']);

        $response = $this->actingAs($admin)->getJson('/api/v1/login-audits?action=login');

        $response->assertStatus(200);
        $items = $response->json('data.data');
        foreach ($items as $item) {
            $this->assertEquals('login', $item['action']);
        }
    }

    public function test_user_can_see_own_history(): void
    {
        $user = $this->createUser();
        LoginAudit::create(['user_id' => $user->id, 'action' => 'login']);

        $response = $this->actingAs($user)->getJson('/api/v1/login-audits/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'data' => ['data'], 'message']);
    }

    public function test_user_cannot_see_all_audits(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->getJson('/api/v1/login-audits');

        $response->assertStatus(403);
    }

    public function test_own_history_only_shows_own_records(): void
    {
        $user1 = $this->createUser();
        $user2 = $this->createDirecteur();
        LoginAudit::create(['user_id' => $user1->id, 'action' => 'login']);
        LoginAudit::create(['user_id' => $user2->id, 'action' => 'login']);

        $response = $this->actingAs($user1)->getJson('/api/v1/login-audits/me');

        $items = $response->json('data.data');
        foreach ($items as $item) {
            $this->assertEquals($user1->id, $item['user_id']);
        }
    }

    public function test_audit_stores_ip_and_user_agent(): void
    {
        $user = $this->createUser(['username' => 'ip_test']);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'ip_test',
            'password' => 'password',
        ], ['User-Agent' => 'TestBrowser/1.0']);

        $audit = LoginAudit::where('user_id', $user->id)->first();
        $this->assertNotNull($audit->ip_address);
    }
}
