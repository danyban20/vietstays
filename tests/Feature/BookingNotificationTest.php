<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use App\Services\VvEmailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class BookingNotificationTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<array{code: string, to: string, tokens: array<string, string>}> */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Record emails instead of talking to SMTP.
        $this->mock(VvEmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendByCode')->andReturnUsing(function (string $code, string $to, array $tokens = []) {
                $this->sent[] = ['code' => $code, 'to' => $to, 'tokens' => $tokens];

                return true;
            });
        });
    }

    private function createHost(): User
    {
        $host = User::factory()->create(['role' => 'host']);
        $host->update(['legacy_wp_id' => 900_000 + $host->id]);

        return $host;
    }

    private function hostApartment(User $host): Apartment
    {
        $apartment = Apartment::query()->where('status', 'active')->orderBy('ID')->firstOrFail();
        $apartment->update(['user_id' => $host->legacy_wp_id, 'price_daily' => 1_000_000, 'max_guests' => 4]);

        return $apartment;
    }

    private function createManualBooking(User $host, Apartment $apartment, string $email = 'guest@example.com'): Booking
    {
        $response = $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Notify Test',
            'email' => $email,
            'guests' => 1,
            'daily_price' => 500000,
            'status' => 'pending',
            'check_in_date' => now()->addYears(4)->format('Y-m-d'),
            'check_out_date' => now()->addYears(4)->addDays(2)->format('Y-m-d'),
        ])->assertCreated();

        $this->sent = [];

        return Booking::query()->findOrFail($response->json('data.id'));
    }

    /** @return list<string> */
    private function sentCodes(): array
    {
        return array_column($this->sent, 'code');
    }

    public function test_website_booking_emails_the_guest_and_the_host(): void
    {
        $host = $this->createHost();
        $apartment = $this->hostApartment($host);

        $this->postJson('/api/public/bookings', [
            'apartment_id' => $apartment->ID,
            'check_in_date' => now()->addYears(4)->format('Y-m-d'),
            'check_out_date' => now()->addYears(4)->addDays(2)->format('Y-m-d'),
            'guest_name' => 'Tran <b>Thi</b> B',
            'email' => 'guest.b@example.com',
            'phone' => '+84 90 111 2222',
        ])->assertCreated();

        $this->assertContains(['code' => 'user_booking_confirmation', 'to' => 'guest.b@example.com'], array_map(
            fn ($mail) => ['code' => $mail['code'], 'to' => $mail['to']],
            $this->sent,
        ));

        $hostMail = collect($this->sent)->firstWhere('code', 'host_new_booking');
        $this->assertNotNull($hostMail, 'The host should be emailed about a new website booking.');
        $this->assertSame($host->email, $hostMail['to']);
        $this->assertStringContainsString('/admin/bookings/', $hostMail['tokens']['BOOKING_ADMIN_LINK']);
        // Guest-typed text is escaped before it goes into the HTML email.
        $this->assertStringNotContainsString('<b>', $hostMail['tokens']['FIRSTNAME'].$hostMail['tokens']['LASTNAME']);
    }

    public function test_manual_booking_by_the_host_does_not_email_the_host(): void
    {
        $host = $this->createHost();
        $apartment = $this->hostApartment($host);

        $this->actingAs($host)->postJson('/api/bookings', [
            'type' => 'manual',
            'apartment_id' => $apartment->ID,
            'guest_name' => 'Walk In',
            'guests' => 1,
            'daily_price' => 500000,
            'check_in_date' => now()->addYears(4)->format('Y-m-d'),
            'check_out_date' => now()->addYears(4)->addDay()->format('Y-m-d'),
        ])->assertCreated();

        $this->assertNotContains('host_new_booking', $this->sentCodes());
    }

    public function test_confirming_a_booking_emails_the_guest(): void
    {
        $host = $this->createHost();
        $booking = $this->createManualBooking($host, $this->hostApartment($host));

        $this->actingAs($host)
            ->putJson("/api/bookings/{$booking->ID}", ['status' => 'confirmed', 'notify_guest' => 'none'])
            ->assertOk()
            ->assertJsonPath('guest_emailed', true);

        $this->assertSame(['guest_booking_confirmed'], $this->sentCodes());
        $this->assertSame('guest@example.com', $this->sent[0]['to']);
    }

    public function test_cancelling_a_booking_emails_the_guest(): void
    {
        $host = $this->createHost();
        $booking = $this->createManualBooking($host, $this->hostApartment($host));

        $this->actingAs($host)
            ->putJson("/api/bookings/{$booking->ID}", ['status' => 'cancelled', 'notify_guest' => 'none'])
            ->assertOk();

        $this->assertSame(['guest_booking_cancelled'], $this->sentCodes());
    }

    public function test_saving_without_a_status_change_sends_nothing(): void
    {
        $host = $this->createHost();
        $booking = $this->createManualBooking($host, $this->hostApartment($host));

        $this->actingAs($host)
            ->putJson("/api/bookings/{$booking->ID}", ['note' => 'Late arrival', 'notify_guest' => 'email'])
            ->assertOk()
            ->assertJsonPath('guest_emailed', false);

        $this->assertSame([], $this->sentCodes());
    }

    public function test_changing_dates_emails_the_guest_only_when_asked(): void
    {
        $host = $this->createHost();
        $apartment = $this->hostApartment($host);
        $booking = $this->createManualBooking($host, $apartment);

        $this->actingAs($host)->postJson("/api/bookings/{$booking->ID}/move", [
            'apartment_id' => $apartment->ID,
            'check_in_date' => now()->addYears(4)->addDays(10)->format('Y-m-d'),
            'check_out_date' => now()->addYears(4)->addDays(12)->format('Y-m-d'),
            'notify_guest' => 'none',
        ])->assertOk();

        $this->assertSame([], $this->sentCodes());

        $this->actingAs($host)->postJson("/api/bookings/{$booking->ID}/move", [
            'apartment_id' => $apartment->ID,
            'check_in_date' => now()->addYears(4)->addDays(20)->format('Y-m-d'),
            'check_out_date' => now()->addYears(4)->addDays(22)->format('Y-m-d'),
            'notify_guest' => 'email',
        ])->assertOk()->assertJsonPath('guest_emailed', true);

        $this->assertSame(['guest_booking_updated'], $this->sentCodes());
    }
}
