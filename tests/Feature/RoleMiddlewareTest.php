<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    public function test_non_admin_is_forbidden_from_admin_only_route(): void
    {
        $host = User::query()->where('role', '!=', 'superadmin')->first();
        $this->assertNotNull($host);

        $response = $this->actingAs($host)->getJson('/api/admin/users');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_only_route(): void
    {
        $admin = User::query()->where('role', 'superadmin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertOk();
    }

    public function test_guest_is_unauthenticated_on_admin_only_route(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertUnauthorized();
    }

    public function test_host_is_forbidden_from_superadmin_tools_routes(): void
    {
        $host = User::factory()->create(['role' => 'host']);

        $this->actingAs($host)->getJson('/api/admin/hosts')->assertForbidden();
        $this->actingAs($host)->getJson('/api/admin/buildings')->assertForbidden();
        $this->actingAs($host)->getJson('/api/admin/countries')->assertForbidden();
        $this->actingAs($host)->getJson('/api/admin/price-matrix')->assertForbidden();
    }

    public function test_superadmin_and_supervisor_can_access_superadmin_tools_routes(): void
    {
        $superadmin = User::query()->where('role', 'superadmin')->first();
        $this->assertNotNull($superadmin);
        $this->actingAs($superadmin)->getJson('/api/admin/buildings')->assertOk();

        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $this->actingAs($supervisor)->getJson('/api/admin/buildings')->assertOk();
        $this->actingAs($supervisor)->getJson('/api/admin/hosts')->assertOk();
        $this->actingAs($supervisor)->getJson('/api/admin/countries')->assertOk();
        $this->actingAs($supervisor)->getJson('/api/admin/price-matrix')->assertOk();
    }
}
