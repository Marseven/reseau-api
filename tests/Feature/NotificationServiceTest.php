<?php

namespace Tests\Feature;

use App\Models\ChangeRequest;
use App\Models\Coffret;
use App\Models\Maintenance;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    public function test_new_change_request_notifies_admins(): void
    {
        $admin1 = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        $admin2 = User::factory()->create(['role' => 'administrator', 'is_active' => true]);
        User::factory()->create(['role' => 'administrator', 'is_active' => false]); // inactive
        User::factory()->create(['role' => 'technicien', 'is_active' => true]); // not admin

        $changeRequest = ChangeRequest::factory()->create();
        $changeRequest->load('coffret');

        $this->service->notifyNewChangeRequest($changeRequest);

        $this->assertEquals(2, Notification::count());
        $this->assertTrue(Notification::where('user_id', $admin1->id)->exists());
        $this->assertTrue(Notification::where('user_id', $admin2->id)->exists());
        $this->assertEquals('modification_request', Notification::first()->type);
    }

    public function test_approved_change_request_notifies_requester(): void
    {
        $requester = User::factory()->create(['role' => 'technicien']);
        $changeRequest = ChangeRequest::factory()->create([
            'requester_id' => $requester->id,
            'status' => 'approuvee',
        ]);

        $this->service->notifyChangeRequestReviewed($changeRequest);

        $notification = Notification::where('user_id', $requester->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('modification_approved', $notification->type);
    }

    public function test_rejected_change_request_notifies_requester(): void
    {
        $requester = User::factory()->create(['role' => 'technicien']);
        $changeRequest = ChangeRequest::factory()->create([
            'requester_id' => $requester->id,
            'status' => 'rejetee',
        ]);

        $this->service->notifyChangeRequestReviewed($changeRequest);

        $notification = Notification::where('user_id', $requester->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('modification_rejected', $notification->type);
    }

    public function test_maintenance_en_cours_notifies_technicien(): void
    {
        $technicien = User::factory()->create(['role' => 'technicien']);
        $maintenance = Maintenance::factory()->create([
            'technicien_id' => $technicien->id,
            'status' => 'en_cours',
        ]);

        $this->service->notifyMaintenanceActive($maintenance);

        $notification = Notification::where('user_id', $technicien->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('intervention_active', $notification->type);
    }
}
