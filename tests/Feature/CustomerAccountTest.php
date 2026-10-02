<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use DatabaseTransactions;

    private function createMember(): User
    {
        return User::factory()->create(['role' => 'member']);
    }

    public function test_registration_creates_a_signed_in_member(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Nguyen Van A',
            'email' => 'Register.Test@Example.com',
            'phone' => '+84 90 000 0000',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ]);

        $response->assertCreated()->assertJsonPath('user.role', 'member');

        $user = User::query()->where('email', 'register.test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('member', $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_ignores_a_requested_role(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
            'role' => 'superadmin',
        ])->assertCreated();

        $this->assertSame('member', User::query()->where('email', 'sneaky@example.com')->value('role'));
    }

    public function test_registration_rejects_an_existing_email(): void
    {
        $existing = $this->createMember();

        $this->postJson('/api/register', [
            'name' => 'Someone',
            'email' => $existing->email,
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_member_is_kept_out_of_the_dashboard_api(): void
    {
        $member = $this->createMember();

        foreach (['/api/dashboard', '/api/apartments', '/api/bookings', '/api/customers', '/api/messages', '/api/team'] as $path) {
            $this->actingAs($member)->getJson($path)->assertForbidden();
        }

        $this->actingAs($member)->getJson('/api/admin/users')->assertForbidden();
    }

    public function test_member_can_read_their_session_and_account(): void
    {
        $member = $this->createMember();

        $this->actingAs($member)->getJson('/api/user')->assertOk()->assertJsonPath('user.role', 'member');
        $this->actingAs($member)->getJson('/api/account')->assertOk()->assertJsonPath('data.profile.email', $member->email);
    }

    public function test_member_cannot_sign_in_to_the_dashboard_but_can_on_the_site(): void
    {
        $member = $this->createMember();

        $this->postJson('/api/login', ['email' => $member->email, 'password' => 'password', 'area' => 'dashboard'])
            ->assertUnprocessable();
        $this->assertGuest();

        $this->postJson('/api/login', ['email' => $member->email, 'password' => 'password', 'area' => 'site'])
            ->assertOk();
        $this->assertAuthenticatedAs($member);
    }

    public function test_hosts_can_still_sign_in_to_the_dashboard(): void
    {
        $host = User::factory()->create(['role' => 'host']);

        $this->postJson('/api/login', ['email' => $host->email, 'password' => 'password', 'area' => 'dashboard'])
            ->assertOk();
    }

    public function test_account_lists_only_the_members_own_bookings(): void
    {
        $member = $this->createMember();
        $other = $this->createMember();

        [$mine, $theirs] = Booking::query()->orderBy('ID')->limit(2)->get()->all() + [null, null];
        $this->assertNotNull($theirs, 'Fixture needs at least two bookings.');

        $mine->update(['member_user_id' => $member->id]);
        $theirs->update(['member_user_id' => $other->id]);

        $response = $this->actingAs($member)->getJson('/api/account')->assertOk();

        $this->assertSame([$mine->ID], array_column($response->json('data.bookings'), 'id'));
    }

    public function test_member_can_update_their_profile_but_not_their_role(): void
    {
        $member = $this->createMember();

        $this->actingAs($member)->putJson('/api/account', [
            'name' => 'New Name',
            'phone' => '0123',
            'role' => 'superadmin',
        ])->assertOk()->assertJsonPath('data.profile.name', 'New Name');

        $this->assertSame('member', $member->fresh()->role);
    }

    public function test_legacy_customers_move_to_member_but_real_hosts_stay(): void
    {
        $customer = User::factory()->create(['role' => 'host', 'legacy_wp_id' => 800_001]);
        $owner = User::factory()->create(['role' => 'host', 'legacy_wp_id' => 800_002]);
        $adminCreated = User::factory()->create(['role' => 'host', 'legacy_wp_id' => 900_000 + 77_777]);
        $applicant = User::factory()->create(['role' => 'host', 'legacy_wp_id' => null]);

        Apartment::query()->orderBy('ID')->firstOrFail()->update(['user_id' => 800_002]);

        $migration = require database_path('migrations/2026_10_01_000002_move_legacy_customers_to_member_role.php');
        $migration->up();

        $this->assertSame('member', $customer->fresh()->role);
        $this->assertSame('host', $owner->fresh()->role);
        $this->assertSame('host', $adminCreated->fresh()->role);
        $this->assertSame('host', $applicant->fresh()->role);
    }

    protected function tearDown(): void
    {
        Auth::forgetGuards();

        parent::tearDown();
    }
}
