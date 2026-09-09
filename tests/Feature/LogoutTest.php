<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::query()->where('legacy_wp_id', 3)->first();
        $this->assertNotNull($user);

        $this->actingAs($user)
            ->getJson('/api/user')
            ->assertOk();

        $this->actingAs($user)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_guest_cannot_log_out(): void
    {
        $this->postJson('/api/logout')->assertUnauthorized();
    }
}
