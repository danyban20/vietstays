<?php

namespace Tests\Feature;

use App\Models\Apartment;
use App\Models\User;
use App\Services\VvEmailService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery\MockInterface;
use Tests\TestCase;

class ApartmentReviewTest extends TestCase
{
    use DatabaseTransactions;

    /** @var list<array{code: string, to: string, tokens: array<string, string>}> */
    private array $sent = [];

    protected function setUp(): void
    {
        parent::setUp();

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

    private function createApartment(User $host, array $overrides = []): Apartment
    {
        $response = $this->actingAs($host)->postJson('/api/apartments', $overrides + [
            'building_id' => 476,
            'district_id' => 475,
            'apartment_type' => '2BR',
            'name' => 'Review test apartment',
            'room_number' => 'ReviewTest-'.uniqid(),
            'description' => 'A bright two bedroom apartment.',
            'images' => [['thumb' => 'https://example.com/photo.jpg', 'image_id' => '', 'caption' => '']],
            'price_daily' => 900000,
        ]);

        $response->assertCreated();

        return Apartment::query()->findOrFail($response->json('data.id'));
    }

    private function publicIds(): array
    {
        return collect($this->getJson('/api/public/apartments')->json('data'))->pluck('id')->all();
    }

    public function test_a_host_cannot_publish_their_own_apartment(): void
    {
        $host = $this->createHost();

        $apartment = $this->createApartment($host, ['status' => 'active']);
        $this->assertSame('draft', $apartment->status);

        $this->actingAs($host)->putJson("/api/apartments/{$apartment->ID}", ['status' => 'active'])->assertOk();
        $this->assertSame('draft', $apartment->fresh()->status);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/approve")->assertForbidden();
        $this->assertNotContains($apartment->ID, $this->publicIds());
    }

    public function test_a_complete_apartment_goes_live_without_a_review(): void
    {
        $host = $this->createHost();
        $apartment = $this->createApartment($host, ['facilities' => [1]]);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');

        $this->assertContains($apartment->ID, $this->publicIds());
        $this->assertSame([], $this->sent);
    }

    public function test_a_weak_apartment_waits_for_review_and_alerts_the_team(): void
    {
        $host = $this->createHost();
        $admin = User::factory()->create(['role' => 'superadmin']);
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $apartment = $this->createApartment($host, ['description' => '']);

        $response = $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
        $this->assertLessThan(80, $response->json('data.completion'));
        $this->assertNotContains($apartment->ID, $this->publicIds());

        $alerted = collect($this->sent)->where('code', 'admin_apartment_needs_review')->pluck('to');
        $this->assertContains($admin->email, $alerted);
        $this->assertContains($supervisor->email, $alerted);
        $this->assertNotContains($host->email, $alerted);

        $this->actingAs($admin)->postJson("/api/apartments/{$apartment->ID}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
        $this->assertContains($apartment->ID, $this->publicIds());

        $this->actingAs($admin)->postJson("/api/apartments/{$apartment->ID}/unpublish")->assertOk();
        $this->assertSame('draft', $apartment->fresh()->status);
    }

    public function test_supervisor_can_send_an_apartment_back(): void
    {
        $host = $this->createHost();
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $apartment = $this->createApartment($host, ['description' => '']);

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/publish")
            ->assertJsonPath('data.status', 'pending');

        $this->actingAs($supervisor)->postJson("/api/apartments/{$apartment->ID}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_photos_and_a_price_are_always_required(): void
    {
        $host = $this->createHost();
        $apartment = $this->createApartment($host, ['images' => [], 'facilities' => [1]]);
        // Creation fills a price from the matrix when none is given.
        $apartment->forceFill(['price_daily' => 0])->save();

        $this->actingAs($host)->postJson("/api/apartments/{$apartment->ID}/publish")
            ->assertUnprocessable()
            ->assertJsonPath('missing', ['photos', 'daily price']);
        $this->assertSame('draft', $apartment->fresh()->status);
        $this->assertSame([], $this->sent);
    }

    public function test_a_host_cannot_publish_someone_elses_apartment(): void
    {
        $apartment = $this->createApartment($this->createHost());

        $this->actingAs($this->createHost())->postJson("/api/apartments/{$apartment->ID}/publish")->assertForbidden();
    }
}
