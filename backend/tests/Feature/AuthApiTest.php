<?php

namespace Tests\Feature;

use Database\Seeders\DemoPortalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_user_can_log_in_and_fetch_profile(): void
    {
        $this->seed(DemoPortalSeeder::class);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@clientlane.test',
            'password' => 'password',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@clientlane.test')
            ->assertJsonPath('user.role', 'staff')
            ->assertJsonPath('firm.name', 'ClientLane Accounting');

        $token = $loginResponse->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'admin@clientlane.test')
            ->assertJsonPath('firm.slug', 'clientlane-demo');
    }
}
