<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkRequest;
use Database\Seeders\DemoPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_a_new_request(): void
    {
        $this->seed(DemoPortalSeeder::class);

        $clientUser = User::query()->where('email', 'mina@harborbakery.test')->firstOrFail();

        Sanctum::actingAs($clientUser);

        $response = $this->postJson('/api/requests', [
            'title' => 'April receipt upload',
            'request_type' => 'Document collection',
            'summary' => 'Uploading the first batch of April receipts for coding.',
            'priority' => 'normal',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'April receipt upload')
            ->assertJsonPath('data.status', 'new')
            ->assertJsonPath('data.client_id', $clientUser->client_id);

        $this->assertDatabaseHas('work_requests', [
            'title' => 'April receipt upload',
            'client_id' => $clientUser->client_id,
            'submitted_by_user_id' => $clientUser->id,
        ]);
    }

    public function test_staff_can_update_a_request_and_send_a_reminder(): void
    {
        $this->seed(DemoPortalSeeder::class);

        $staffUser = User::query()->where('email', 'ops@clientlane.test')->firstOrFail();
        $clientUser = User::query()->where('email', 'mina@harborbakery.test')->firstOrFail();
        $workRequest = WorkRequest::query()->where('title', 'March bookkeeping packet')->firstOrFail();

        Sanctum::actingAs($staffUser);

        $this->patchJson("/api/requests/{$workRequest->id}", [
            'status' => 'in_progress',
            'assigned_to_user_id' => $staffUser->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'in_progress');

        $this->postJson("/api/requests/{$workRequest->id}/reminders", [
            'message' => 'Please upload the final March bank statement today so we can close the books.',
        ])
            ->assertOk();

        $this->assertNotNull($workRequest->fresh()->last_reminded_at);

        $this->assertDatabaseHas('activity_logs', [
            'work_request_id' => $workRequest->id,
            'type' => 'reminder.sent',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $clientUser->id,
            'type' => 'App\\Notifications\\PortalEventNotification',
        ]);
    }
}
