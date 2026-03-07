<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_user_can_list_own_notifications(): void
    {
        Notification::factory()->count(3)->create(['user_id' => $this->user->id]);
        Notification::factory()->count(2)->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['notifications' => ['data'], 'unread_count']]);
        $this->assertCount(3, $response->json('data.notifications.data'));
    }

    public function test_response_includes_unread_count(): void
    {
        Notification::factory()->count(2)->create(['user_id' => $this->user->id, 'read_at' => null]);
        Notification::factory()->read()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notifications');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('data.unread_count'));
    }

    public function test_can_filter_unread_only(): void
    {
        Notification::factory()->count(2)->create(['user_id' => $this->user->id, 'read_at' => null]);
        Notification::factory()->read()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notifications?unread_only=true');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data.notifications.data'));
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id, 'read_at' => null]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_mark_other_users_notification(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)->putJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertStatus(403);
    }

    public function test_can_mark_all_as_read(): void
    {
        Notification::factory()->count(3)->create(['user_id' => $this->user->id, 'read_at' => null]);
        $adminNotif = Notification::factory()->create(['user_id' => $this->admin->id, 'read_at' => null]);

        $response = $this->actingAs($this->user)->putJson('/api/v1/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, Notification::where('user_id', $this->user->id)->whereNull('read_at')->count());
        $this->assertNull($adminNotif->fresh()->read_at);
    }

    public function test_can_delete_own_notification(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_cannot_delete_other_users_notification(): void
    {
        $notification = Notification::factory()->create(['user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)->deleteJson("/api/v1/notifications/{$notification->id}");

        $response->assertStatus(403);
    }

    public function test_unauthenticated_returns_401(): void
    {
        $response = $this->getJson('/api/v1/notifications');

        $response->assertStatus(401);
    }

    public function test_notifications_ordered_by_newest_first(): void
    {
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now()->subHour(),
            'title' => 'Old',
        ]);
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'created_at' => now(),
            'title' => 'New',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/v1/notifications');

        $items = $response->json('data.notifications.data');
        $this->assertEquals('New', $items[0]['title']);
        $this->assertEquals('Old', $items[1]['title']);
    }
}
