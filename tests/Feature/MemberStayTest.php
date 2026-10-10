<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\ApartmentGuestInfo;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\HostCustomerMessage;
use App\Models\HostCustomerMessageThread;
use App\Models\User;
use App\Services\CustomerAggregationService;
use App\Services\VvEmailService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * The customer dashboard ("My account"): reservations, access codes,
 * passports, extra cleaning, cancellation, reviews, messages, documents.
 */
class MemberStayTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<array{code: string, to: string, tokens: array<string, string>}> */
    private array $sent = [];

    private User $host;

    private Apartment $apartment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(VvEmailService::class, function (MockInterface $mock) {
            $mock->shouldReceive('sendByCode')->andReturnUsing(function (string $code, string $to, array $tokens = []) {
                $this->sent[] = ['code' => $code, 'to' => $to, 'tokens' => $tokens];

                return true;
            });
        });

        $this->host = User::factory()->create(['role' => 'host']);
        $this->host->update(['legacy_wp_id' => 910_000 + $this->host->id]);

        $this->apartment = Apartment::query()->where('status', 'active')->orderBy('ID')->firstOrFail();
        $this->apartment->update([
            'user_id' => $this->host->legacy_wp_id,
            'check_in_time1' => '15:00:00',
            'check_out_time' => '11:00:00',
            'cleaning_fee' => 200000,
            'extra_cleaning_fee' => 250000,
        ]);

        ApartmentGuestInfo::query()->updateOrCreate(
            ['apartment_id' => $this->apartment->ID],
            ['door_code' => '4829#', 'wifi_network' => 'Sunset_5G', 'wifi_password' => 'welcome!', 'cleanings_per_week' => 2],
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function member(): User
    {
        return User::factory()->create(['role' => 'member']);
    }

    private function booking(User $member, string $checkIn, int $nights = 6, array $attributes = []): Booking
    {
        $id = (int) DB::table('vv_bookings')->max('ID') + 1;
        $in = Carbon::parse($checkIn);

        return Booking::query()->create(array_merge([
            'ID' => $id,
            'booking_num' => 'T'.$id,
            'user_id' => 0,
            'member_user_id' => $member->id,
            'apartment_id' => $this->apartment->ID,
            'district_id' => (int) $this->apartment->district,
            'email' => $member->email,
            'firstname' => 'Anna',
            'lastname' => 'Johansson',
            'dates' => [],
            'check_in_date' => $in,
            'check_out_date' => $in->copy()->addDays($nights),
            'extra_data' => ['payment_method' => 'onsite', 'source' => 'guest_checkout'],
            'price' => 1000000,
            'basic_discount' => 0,
            'campaign_discount' => 0,
            'campaign_discount_desc' => [],
            'ambassador_id' => 0,
            'ambassador_commission' => 0,
            'promo_code' => '',
            'promo_code_discount' => 0,
            'booking_fee' => 5,
            'total' => 1000000 * $nights,
            'adults' => 2,
            'children' => 0,
            'child_ages' => [],
            'status' => 'confirmed',
            'dateadded' => now(),
            'datemodified' => now(),
        ], $attributes));
    }

    private function codes(): array
    {
        return array_column($this->sent, 'code');
    }

    public function test_member_pages_need_a_signed_in_user(): void
    {
        $this->getJson('/api/member/dashboard')->assertUnauthorized();
        $this->getJson('/api/member/reservations')->assertUnauthorized();
    }

    public function test_reservations_only_list_the_members_own_bookings(): void
    {
        $member = $this->member();
        $other = $this->member();
        $mine = $this->booking($member, '2031-07-28');
        $theirs = $this->booking($other, '2031-08-10');

        $ids = collect($this->actingAs($member)->getJson('/api/member/reservations')->assertOk()->json('data'))->pluck('id');

        $this->assertTrue($ids->contains($mine->ID));
        $this->assertFalse($ids->contains($theirs->ID));

        $this->actingAs($member)->getJson("/api/member/reservations/{$theirs->ID}")->assertNotFound();
    }

    public function test_door_code_and_wifi_appear_only_when_due(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');

        // A week before: upcoming, nothing revealed.
        Carbon::setTestNow(Carbon::parse('2031-07-21 12:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")
            ->assertOk()
            ->assertJsonPath('data.stage', 'upcoming')
            ->assertJsonPath('data.access.door_code', null)
            ->assertJsonPath('data.access.wifi_network', null)
            ->assertJsonPath('data.days_until_check_in', 7);

        // 23 hours before check-in (15:00): the door code is out, Wi-Fi not yet.
        Carbon::setTestNow(Carbon::parse('2031-07-27 16:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")
            ->assertJsonPath('data.access.door_code', '4829#')
            ->assertJsonPath('data.access.wifi_network', null);

        // During the stay.
        Carbon::setTestNow(Carbon::parse('2031-07-30 10:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")
            ->assertJsonPath('data.stage', 'current')
            ->assertJsonPath('data.access.wifi_password', 'welcome!')
            ->assertJsonPath('data.housekeeping.badge', 'Today is Day 3');

        // After check-out time: past, codes gone again.
        Carbon::setTestNow(Carbon::parse('2031-08-03 11:30', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")
            ->assertJsonPath('data.stage', 'past')
            ->assertJsonPath('data.badge.label', 'Completed')
            ->assertJsonPath('data.access.door_code', null);
    }

    public function test_pending_bookings_never_show_the_door_code(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28', 6, ['status' => 'pending']);

        Carbon::setTestNow(Carbon::parse('2031-07-28 09:00', 'Asia/Ho_Chi_Minh'));

        $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")
            ->assertJsonPath('data.stage', 'upcoming')
            ->assertJsonPath('data.badge.label', 'Pending approval')
            ->assertJsonPath('data.access.door_code', null);
    }

    public function test_guest_can_cancel_an_upcoming_booking_and_both_sides_are_emailed(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');
        Carbon::setTestNow(Carbon::parse('2031-07-01 09:00', 'Asia/Ho_Chi_Minh'));

        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/cancel", [])
            ->assertUnprocessable();

        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/cancel", ['accept_policy' => true])
            ->assertOk()
            ->assertJsonPath('data.stage', 'cancelled');

        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $this->assertSame('guest', $booking->extra_data['cancelled_by']);
        $this->assertContains('guest_booking_cancelled', $this->codes());
        $this->assertContains('host_guest_cancelled', $this->codes());
    }

    public function test_a_finished_stay_cannot_be_cancelled(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-01', 3);
        Carbon::setTestNow(Carbon::parse('2031-07-10 09:00', 'Asia/Ho_Chi_Minh'));

        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/cancel", ['accept_policy' => true])
            ->assertUnprocessable();

        $this->assertSame('confirmed', $booking->fresh()->status);
    }

    public function test_passport_details_are_saved_encrypted_and_kept_private(): void
    {
        Storage::fake('local');
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');
        Carbon::setTestNow(Carbon::parse('2031-07-01 09:00', 'Asia/Ho_Chi_Minh'));

        $this->actingAs($member)->post("/api/member/reservations/{$booking->ID}/guests", [
            'position' => 1,
            'full_name' => 'Anna Johansson',
            'nationality' => 'SE',
            'passport_number' => 'ab1237482',
            'date_of_birth' => '1990-04-02',
            'passport_expiry' => '2033-01-01',
            'photo' => UploadedFile::fake()->create('passport.pdf', 120, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('data.registered', 1)
            ->assertJsonPath('data.expected', 2)
            ->assertJsonPath('data.guests.0.passport_last4', '7482');

        $raw = DB::table('vv_booking_guests')->where('booking_id', $booking->ID)->first();
        $this->assertStringNotContainsString('AB1237482', $raw->passport_number);
        $this->assertSame('AB1237482', BookingGuest::query()->find($raw->id)->passport_number);
        Storage::disk('local')->assertExists($raw->photo_path);

        // Only two guests on the booking.
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/guests", [
            'position' => 3,
            'full_name' => 'Extra',
            'nationality' => 'SE',
            'passport_number' => 'XY12345',
            'date_of_birth' => '1990-01-01',
            'passport_expiry' => '2033-01-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('position');

        // Another customer can't read it.
        $stranger = $this->member();
        $this->actingAs($stranger)->getJson("/api/member/reservations/{$booking->ID}/guests/1")->assertNotFound();
        $this->actingAs($stranger)->get("/api/member/reservations/{$booking->ID}/guests/1/photo")->assertNotFound();

        // The apartment's host sees the full details.
        $this->actingAs($this->host)->getJson("/api/bookings/{$booking->ID}/guest-services")
            ->assertOk()
            ->assertJsonPath('data.passports.guests.0.passport_number', 'AB1237482');
    }

    public function test_extra_cleaning_request_flow(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');
        // Not on the arrival day.
        Carbon::setTestNow(Carbon::parse('2031-07-01 09:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/services", [
            'service_date' => '2031-07-28',
            'time_slot' => 'morning',
        ])->assertUnprocessable();

        Carbon::setTestNow(Carbon::parse('2031-07-29 11:00', 'Asia/Ho_Chi_Minh'));

        // Same day after the 10:00 cut-off, and after the stay: refused.
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/services", [
            'service_date' => '2031-07-29',
            'time_slot' => 'morning',
        ])->assertUnprocessable();

        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/services", [
            'service_date' => '2031-08-03',
            'time_slot' => 'morning',
        ])->assertUnprocessable();

        $serviceId = $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/services", [
            'service_date' => '2031-07-31',
            'time_slot' => 'afternoon',
        ])->assertCreated()->assertJsonPath('data.price', 250000)->json('data.id');

        $this->assertContains('host_extra_cleaning_requested', $this->codes());

        // Same day twice: refused.
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/services", [
            'service_date' => '2031-07-31',
            'time_slot' => 'morning',
        ])->assertUnprocessable();

        $this->actingAs($this->host)->patchJson("/api/bookings/{$booking->ID}/service-requests/{$serviceId}", ['status' => 'confirmed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertContains('guest_extra_cleaning_updated', $this->codes());

        // Confirmed cleaning shows up in the price and can't be withdrawn.
        $price = $this->actingAs($member)->getJson("/api/member/reservations/{$booking->ID}")->json('data.price');
        $this->assertEquals(6000000 + 250000, $price['total']);
        $this->actingAs($member)->deleteJson("/api/member/reservations/{$booking->ID}/services/{$serviceId}")->assertUnprocessable();
    }

    public function test_another_host_cannot_see_or_decide_guest_services(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');
        $otherHost = User::factory()->create(['role' => 'host', 'legacy_wp_id' => 990_001]);

        $this->actingAs($otherHost)->getJson("/api/bookings/{$booking->ID}/guest-services")->assertForbidden();
        $this->actingAs($otherHost)->getJson("/api/apartments/{$this->apartment->ID}/guest-info")->assertForbidden();
    }

    public function test_review_only_after_the_stay_and_only_once(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-01', 3);

        Carbon::setTestNow(Carbon::parse('2031-07-02 12:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/review", ['rating' => 5])->assertUnprocessable();

        Carbon::setTestNow(Carbon::parse('2031-07-10 12:00', 'Asia/Ho_Chi_Minh'));
        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/review", [
            'rating' => 4,
            'category_ratings' => ['cleanliness' => 5, 'unknown' => 1],
            'comment' => 'Lovely view.',
        ])->assertCreated();

        $this->actingAs($member)->postJson("/api/member/reservations/{$booking->ID}/review", ['rating' => 1])->assertUnprocessable();

        $this->actingAs($this->host)->getJson("/api/bookings/{$booking->ID}/guest-services")
            ->assertJsonPath('data.review.rating', 4)
            ->assertJsonPath('data.review.categories.0.label', 'Cleanliness')
            ->assertJsonCount(1, 'data.review.categories');
    }

    public function test_guest_messages_reach_the_hosts_customer_thread(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');

        $conversations = $this->actingAs($member)->getJson('/api/member/messages')->assertOk()->json('data');
        $this->assertSame('host-'.$this->host->id, $conversations[0]['id']);

        $threadId = $this->actingAs($member)->postJson('/api/member/messages/host-'.$this->host->id, [
            'text' => 'What time can we check in?',
            'booking_id' => $booking->ID,
        ])->assertCreated()->json('data.id');

        $thread = HostCustomerMessageThread::query()->findOrFail($threadId);
        $this->assertSame($this->host->id, $thread->user_id);
        $this->assertSame(app(CustomerAggregationService::class)->customerKeyForBooking($booking), $thread->customer_key);

        // The host answers; the guest sees it as unread.
        HostCustomerMessage::query()->create(['thread_id' => $thread->id, 'sender' => 'host', 'author' => 'Minh', 'body' => 'From 3 PM.']);
        $this->actingAs($member)->getJson('/api/member/dashboard')->assertJsonPath('data.unread.count', 1);

        $this->actingAs($member)->getJson("/api/member/messages/{$threadId}")
            ->assertOk()
            ->assertJsonCount(2, 'data.messages');

        $this->actingAs($member)->getJson('/api/member/dashboard')->assertJsonPath('data.unread.count', 0);
        $this->actingAs($this->member())->getJson("/api/member/messages/{$threadId}")->assertNotFound();
    }

    public function test_messages_from_before_the_guests_booking_stay_hidden(): void
    {
        $member = $this->member();
        $customerKey = hash('sha256', 'email:'.strtolower($member->email));

        // Someone used this email with the same host long before.
        $thread = HostCustomerMessageThread::query()->create([
            'user_id' => $this->host->id,
            'customer_key' => $customerKey,
            'guest_token' => Str::random(48),
            'last_message_at' => now()->subYear(),
        ]);
        $old = HostCustomerMessage::query()->create(['thread_id' => $thread->id, 'sender' => 'host', 'body' => 'Old private note']);
        $old->forceFill(['created_at' => now()->subYear()])->save();

        $this->booking($member, '2031-07-28');

        $messages = $this->actingAs($member)->getJson("/api/member/messages/{$thread->id}")->assertOk()->json('data.messages');
        $this->assertSame([], $messages);
    }

    public function test_confirmation_pdf_downloads(): void
    {
        $member = $this->member();
        $booking = $this->booking($member, '2031-07-28');

        $response = $this->actingAs($member)->get("/api/member/reservations/{$booking->ID}/documents/confirmation");
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        // A receipt only exists once the stay is over.
        $this->actingAs($member)->get("/api/member/reservations/{$booking->ID}/documents/receipt")->assertNotFound();
    }

    public function test_host_saves_guest_arrival_details(): void
    {
        $this->actingAs($this->host)->putJson("/api/apartments/{$this->apartment->ID}/guest-info", [
            'check_in_time' => '14:30',
            'check_out_time' => '',
            'door_code' => ' 1234 ',
            'wifi_network' => 'Home',
            'cleanings_per_week' => 1,
        ])->assertOk()
            ->assertJsonPath('data.check_in_time', '14:30')
            ->assertJsonPath('data.check_out_time', '')
            ->assertJsonPath('data.door_code', '1234')
            ->assertJsonPath('data.cleanings_per_week', 1);

        $raw = DB::table('vv_apartment_guest_info')->where('apartment_id', $this->apartment->ID)->value('door_code');
        $this->assertNotSame('1234', $raw);
    }

    public function test_popular_places_are_public(): void
    {
        $this->getJson('/api/public/popular-places')->assertOk()->assertJsonStructure(['data']);
    }
}
