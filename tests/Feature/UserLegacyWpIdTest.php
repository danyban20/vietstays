<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserLegacyWpIdTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Apartment/booking/customer ownership is keyed off legacy_wp_id. A user
     * created via the admin panel (not imported from WordPress) previously
     * got legacy_wp_id = null, which crashed every ownership-scoped write
     * (e.g. creating a customer) with a NOT NULL constraint violation.
     */
    public function test_admin_created_user_gets_a_synthetic_legacy_wp_id(): void
    {
        $admin = User::query()->where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'New Host',
            'email' => 'new.host.'.uniqid().'@example.com',
            'password' => 'password123',
            'role' => 'host',
        ]);

        $response->assertCreated();

        $newUser = User::query()->find($response->json('data.id'));
        $this->assertNotNull($newUser->legacy_wp_id);

        $customerResponse = $this->actingAs($newUser)->postJson('/api/customers', [
            'name' => 'A Customer',
        ]);

        $customerResponse->assertCreated();
    }
}
