<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\District;
use App\Models\HostApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SecurityRegressionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, mixed>
     */
    private function hostApplicationPayload(string $email): array
    {
        $district = District::query()->where('district_id', 475)->firstOrFail();

        return [
            'applicant_type' => 'multi_property',
            'full_name' => 'Attacker Name',
            'email' => $email,
            'phone' => '000',
            'primary_city_id' => $district->city_id,
            'num_properties' => 2,
            'districts' => [$district->district_id],
            'description' => 'Portfolio manager',
            'confirm_application' => true,
        ];
    }

    public function test_public_host_application_cannot_change_an_existing_account(): void
    {
        foreach (['member', 'supervisor', 'staff', 'ambassador'] as $role) {
            $victim = User::factory()->create(['role' => $role, 'name' => 'Original', 'phone' => '111']);

            $this->postJson('/api/public/host-applications', $this->hostApplicationPayload($victim->email))
                ->assertSuccessful();

            $victim->refresh();
            $this->assertSame($role, $victim->role, "A public form must not change a {$role}'s role.");
            $this->assertSame('Original', $victim->name);
            $this->assertSame('111', $victim->phone);
        }
    }

    public function test_new_applicant_becomes_host_only_when_approved(): void
    {
        $email = 'new.applicant.'.uniqid().'@example.com';

        $this->postJson('/api/public/host-applications', $this->hostApplicationPayload($email))
            ->assertSuccessful();

        $applicant = User::query()->where('email', $email)->firstOrFail();
        $this->assertSame('member', $applicant->role);

        $application = HostApplication::query()->where('email', $email)->firstOrFail();
        $admin = User::query()->where('role', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->patchJson("/api/host-applications/{$application->getKey()}/status", ['status' => 'approved'])
            ->assertOk();

        $this->assertSame('host', $applicant->fresh()->role);
    }

    public function test_public_booking_ignores_a_discount_sent_by_the_browser(): void
    {
        $apartment = Apartment::query()->where('status', 'active')->orderBy('ID')->firstOrFail();
        $apartment->update(['price_daily' => 1_000_000]);

        $request = [
            'apartment_id' => $apartment->ID,
            'check_in_date' => now()->addYears(3)->format('Y-m-d'),
            'check_out_date' => now()->addYears(3)->addDays(2)->format('Y-m-d'),
        ];

        $plain = $this->postJson('/api/public/bookings/quote', $request)->assertOk();
        $tampered = $this->postJson('/api/public/bookings/quote', $request + [
            'promo_code' => 'FREE',
            'promo_code_discount' => 100,
        ])->assertOk();

        $this->assertSame($plain->json('data.total'), $tampered->json('data.total'));
        $this->assertEquals(0, $tampered->json('data.promo_discount_amount'));
    }

    public function test_price_matrix_test_page_needs_a_pricing_admin(): void
    {
        $this->get('/test/price-matrix')->assertRedirect();

        $this->actingAs(User::factory()->create(['role' => 'member']))
            ->get('/test/price-matrix')
            ->assertForbidden();
    }

    public function test_only_dashboard_roles_reach_the_dashboard_api(): void
    {
        foreach (['staff', 'ambassador', 'member', 'some_future_role'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            foreach (['/api/bookings', '/api/apartments', '/api/customers', '/api/dashboard'] as $path) {
                $this->actingAs($user)->getJson($path)->assertForbidden();
            }

            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password', 'area' => 'dashboard'])
                ->assertUnprocessable();
        }

        foreach (User::DASHBOARD_ROLES as $role) {
            $user = User::factory()->create(['role' => $role]);
            $user->update(['legacy_wp_id' => 900_000 + $user->id]);

            $this->actingAs($user)->getJson('/api/bookings')->assertOk();
        }
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
                ->assertUnprocessable();
        }

        $this->postJson('/api/login', ['email' => 'nobody@example.com', 'password' => 'wrong'])
            ->assertStatus(429);
    }
}
