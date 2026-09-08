<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    public function test_non_admin_is_forbidden_from_admin_only_route(): void
    {
        $host = User::query()->where('role', '!=', 'admin')->first();
        $this->assertNotNull($host);

        $response = $this->actingAs($host)->getJson('/api/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_only_route(): void
    {
        $admin = User::query()->where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertOk();
    }

    public function test_guest_is_unauthenticated_on_admin_only_route(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertUnauthorized();
    }
}
