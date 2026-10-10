<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\ApartmentGuestInfo;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingReview;
use App\Models\BookingServiceRequest;
use App\Models\Building;
use App\Models\City;
use App\Models\District;
use App\Models\Facility;
use App\Models\User;
use App\Support\LegacyMediaUrl;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Everything the customer dashboard shows about a guest's own bookings:
 * where a stay is in its life (upcoming, current, past, cancelled), the
 * price breakdown, access details, the cleaning plan and what the guest
 * can still do (cancel, order cleaning, review).
 */
class GuestStayService
{
    /** Apartments and stays are in Vietnam, whatever the server clock says. */
    public const TIMEZONE = 'Asia/Ho_Chi_Minh';

    private const DEFAULT_CHECK_IN = '14:00';

    private const DEFAULT_CHECK_OUT = '12:00';

    /** @var array<int, User|null> */
    private array $hosts = [];

    /** @var array<int, ApartmentGuestInfo|null> */
    private array $guestInfo = [];

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now(self::TIMEZONE);
    }

    /**
     * Bookings placed from this account. Older guest bookings are not
     * matched by email: see AccountController for why.
     */
    public function bookingsQuery(User $user): Builder
    {
        return Booking::query()
            ->with('apartment')
            ->where('member_user_id', $user->id)
            ->whereNotNull('check_in_date')
            ->whereNotNull('check_out_date');
    }

    public function findForUser(User $user, int $bookingId): Booking
    {
        return $this->bookingsQuery($user)->where('ID', $bookingId)->firstOrFail();
    }

    public function checkInAt(Booking $booking): CarbonImmutable
    {
        return $this->atLocalTime($booking->check_in_date, $this->timeOf($booking->apartment?->check_in_time1, self::DEFAULT_CHECK_IN));
    }

    public function checkOutAt(Booking $booking): CarbonImmutable
    {
        return $this->atLocalTime($booking->check_out_date, $this->timeOf($booking->apartment?->check_out_time, self::DEFAULT_CHECK_OUT));
    }

    /**
     * upcoming | current | past | cancelled
     */
    public function stage(Booking $booking, ?CarbonImmutable $now = null): string
    {
        $now ??= $this->now();

        if ($booking->status === 'cancelled') {
            return 'cancelled';
        }

        $checkInDay = $this->localDate($booking->check_in_date);
        $checkOutAt = $this->checkOutAt($booking);

        if ($now->gte($checkOutAt)) {
            return 'past';
        }

        // A stay only starts once the host has confirmed it.
        if ($booking->status === 'confirmed' && $now->startOfDay()->gte($checkInDay)) {
            return 'current';
        }

        return 'upcoming';
    }

    /**
     * @return array{label: string, tone: string}
     */
    public function statusBadge(Booking $booking, string $stage): array
    {
        return match (true) {
            $stage === 'cancelled' => ['label' => 'Cancelled', 'tone' => 'cancelled'],
            $stage === 'past' && $booking->status === 'confirmed' => ['label' => 'Completed', 'tone' => 'completed'],
            $stage === 'past' => ['label' => 'Not confirmed', 'tone' => 'cancelled'],
            $stage === 'current' => ['label' => 'In progress', 'tone' => 'progress'],
            $booking->status === 'pending' => ['label' => 'Pending approval', 'tone' => 'pending_approval'],
            default => ['label' => 'Confirmed', 'tone' => 'confirmed'],
        };
    }

    /**
     * The card used in lists (reservations, dashboard).
     *
     * @return array<string, mixed>
     */
    public function summary(Booking $booking, ?CarbonImmutable $now = null): array
    {
        $now ??= $this->now();
        $stage = $this->stage($booking, $now);
        $apartment = $booking->apartment;
        $checkIn = $this->checkInAt($booking);
        $checkOut = $this->checkOutAt($booking);
        $nights = $this->nights($booking);
        $guests = (int) $booking->adults + (int) $booking->children;

        return [
            'id' => $booking->ID,
            'booking_num' => $booking->booking_num ?: 'BK-'.$booking->ID,
            'stage' => $stage,
            'status' => $booking->status,
            'badge' => $this->statusBadge($booking, $stage),
            'apartment' => [
                'id' => $apartment?->ID,
                'name' => $this->apartmentName($apartment),
                'slug' => $apartment?->url_slug,
                'area' => $this->areaLabel($apartment),
                'image' => $this->coverImage($apartment),
            ],
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'check_in_time' => $checkIn->format('g:i A'),
            'check_out_time' => $checkOut->format('g:i A'),
            'nights' => $nights,
            'guests' => max(1, $guests),
            'total' => (float) $booking->total,
            'currency' => (string) config('vietstays.site_currency', 'VND'),
            'days_until_check_in' => $stage === 'upcoming'
                ? max(0, (int) $now->startOfDay()->diffInDays($this->localDate($booking->check_in_date), false))
                : null,
            'cancelled_by_guest' => ($this->extra($booking)['cancelled_by'] ?? null) === 'guest',
        ];
    }

    /**
     * Full detail for the booking page.
     *
     * @return array<string, mixed>
     */
    public function detail(Booking $booking): array
    {
        $now = $this->now();
        $summary = $this->summary($booking, $now);
        $stage = $summary['stage'];
        $apartment = $booking->apartment;
        $info = $this->guestInfoFor($apartment);
        $host = $this->hostFor($apartment);

        return $summary + [
            'apartment_detail' => [
                'address' => $this->address($apartment),
                'city' => $this->cityName($apartment),
                'map_url' => $this->mapUrl($apartment),
                'latitude' => $this->coordinate($apartment?->address_latitude),
                'longitude' => $this->coordinate($apartment?->address_longitude),
                'gallery' => $this->gallery($apartment),
                'amenities' => $this->amenities($apartment),
                'house_rules' => $this->houseRules($apartment),
                'max_guests' => (int) ($apartment?->max_guests ?? 0),
            ],
            'adults' => (int) $booking->adults,
            'children' => (int) $booking->children,
            'guest_name' => trim($booking->firstname.' '.$booking->lastname),
            'host' => [
                'name' => $host ? ($host->display_name ?: $host->name) : 'Vietstays',
                'is_team' => $host === null,
            ],
            'price' => $this->priceBreakdown($booking, $stage),
            'access' => $this->access($booking, $info, $now, $stage),
            'arrival' => $this->arrival($info),
            'housekeeping' => $this->housekeeping($booking, $info, $now, $stage),
            'passports' => $this->passportSummary($booking, $stage),
            'cancellation' => $this->cancellationQuote($booking, $now, $stage),
            'review' => $this->reviewState($booking, $stage),
            'documents' => [
                'confirmation' => $stage !== 'cancelled',
                'receipt' => $stage === 'past' && $booking->status === 'confirmed',
            ],
            'support' => [
                'security_phone' => $info?->security_phone ?: null,
                'support_phone' => config('vietstays.contact_phone') ?: null,
                'support_email' => config('vietstays.contact_email') ?: null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function priceBreakdown(Booking $booking, ?string $stage = null): array
    {
        $stage ??= $this->stage($booking);
        $currency = (string) config('vietstays.site_currency', 'VND');
        $items = DB::table('vv_booking_items')->where('booking_id', $booking->ID)->orderBy('item_id')->get();
        $lines = [];
        $sum = 0.0;

        foreach ($items as $item) {
            if (($item->item_type ?? '') !== 'booking-date') {
                continue;
            }

            $amount = (float) $item->price * (int) $item->qty;
            $sum += $amount;
            $lines[] = [
                'label' => $this->nightsLabel((int) $item->qty).' × '.$this->money((float) $item->price),
                'amount' => $amount,
                'kind' => 'stay',
            ];
        }

        if ($lines === []) {
            $nights = $this->nights($booking);
            $amount = (float) $booking->price * $nights;
            $sum += $amount;
            $lines[] = [
                'label' => $this->nightsLabel($nights).' × '.$this->money((float) $booking->price),
                'amount' => $amount,
                'kind' => 'stay',
            ];
        }

        foreach ($items as $item) {
            if (($item->item_type ?? '') !== 'fee') {
                continue;
            }

            $sum += (float) $item->price;
            $lines[] = ['label' => (string) $item->name, 'amount' => (float) $item->price, 'kind' => 'fee'];
        }

        $extra = $this->extra($booking);
        $pickup = (float) ($extra['airport_pickup_cost'] ?? 0);

        if ($pickup > 0) {
            $sum += $pickup;
            $lines[] = ['label' => 'Airport pickup', 'amount' => $pickup, 'kind' => 'fee'];
        }

        $discount = (float) $booking->campaign_discount;

        if ($discount > 0) {
            $sum -= $discount;
            $lines[] = [
                'label' => filled($booking->promo_code) ? 'Discount (code '.$booking->promo_code.')' : 'Discount',
                'amount' => -$discount,
                'kind' => 'discount',
            ];
        }

        // Whatever is left is the booking fee plus extras ordered at checkout
        // (extra cleanings), which are not stored as separate items.
        $total = (float) $booking->total;
        $rest = round($total - $sum, 2);

        if (abs($rest) >= 1) {
            $lines[] = [
                'label' => $rest > 0
                    ? ((int) ($extra['num_cleaning'] ?? 0) > 0 ? 'Service fee & extra cleaning' : 'Service fee')
                    : 'Adjustment',
                'amount' => $rest,
                'kind' => $rest > 0 ? 'fee' : 'discount',
            ];
        }

        $services = $booking->relationLoaded('serviceRequests')
            ? $booking->serviceRequests
            : $booking->serviceRequests()->get();

        foreach ($services->where('status', 'confirmed') as $service) {
            $total += (float) $service->price;
            $lines[] = [
                'label' => 'Extra cleaning · '.$service->service_date->format('M j'),
                'amount' => (float) $service->price,
                'kind' => 'fee',
            ];
        }

        $paysOnSite = ($extra['payment_method'] ?? 'onsite') !== 'card';

        return [
            'currency' => $currency,
            'lines' => $lines,
            'total' => $total,
            'total_label' => match (true) {
                $stage === 'cancelled' => 'Booking total',
                $stage === 'past' => 'Total paid',
                $paysOnSite => 'Total due on arrival',
                default => 'Total',
            },
            'payment_method' => $paysOnSite ? 'onsite' : 'card',
        ];
    }

    /**
     * Door code and Wi-Fi, only once they are due.
     *
     * @return array<string, mixed>
     */
    protected function access(Booking $booking, ?ApartmentGuestInfo $info, CarbonImmutable $now, string $stage): array
    {
        $checkIn = $this->checkInAt($booking);
        $revealAt = $checkIn->subHours(max(0, (int) config('vietstays.guest_area.access_reveal_hours', 24)));
        $live = $booking->status === 'confirmed' && in_array($stage, ['upcoming', 'current'], true);
        $codeVisible = $live && $now->gte($revealAt);
        $wifiVisible = $live && $now->startOfDay()->gte($this->localDate($booking->check_in_date));

        // A code set on the booking itself wins over the apartment's.
        $extra = $this->extra($booking);
        $doorCode = ($extra['door_code'] ?? '') ?: $info?->door_code;
        $wifiNetwork = ($extra['wifi_network'] ?? $extra['wifi_name'] ?? '') ?: $info?->wifi_network;
        $wifiPassword = ($extra['wifi_password'] ?? '') ?: $info?->wifi_password;

        return [
            'reveal_hours' => (int) config('vietstays.guest_area.access_reveal_hours', 24),
            'door_code' => $codeVisible ? ($doorCode ?: null) : null,
            'door_code_note' => $info?->door_code_note ?: null,
            'door_code_visible' => $codeVisible,
            'door_code_set' => filled($doorCode),
            'door_code_reveal_at' => $revealAt->toIso8601String(),
            'door_code_reveal_label' => $revealAt->format('D, M j').' at '.$revealAt->format('g:i A'),
            'wifi_visible' => $wifiVisible,
            'wifi_set' => filled($wifiNetwork) || filled($wifiPassword),
            'wifi_network' => $wifiVisible ? ($wifiNetwork ?: null) : null,
            'wifi_password' => $wifiVisible ? ($wifiPassword ?: null) : null,
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function arrival(?ApartmentGuestInfo $info): array
    {
        return [
            'instructions' => $info?->arrival_instructions ?: null,
            'parking' => $info?->parking_info ?: null,
            'contact_label' => $info?->arrival_contact_label ?: null,
            'contact_phone' => $info?->arrival_contact_phone ?: null,
        ];
    }

    /**
     * Included cleanings (spread evenly over the stay) plus any extra
     * cleanings the guest ordered, as a timeline.
     *
     * @return array<string, mixed>
     */
    public function housekeeping(Booking $booking, ?ApartmentGuestInfo $info, CarbonImmutable $now, string $stage): array
    {
        $checkInDay = $this->localDate($booking->check_in_date);
        $checkOutDay = $this->localDate($booking->check_out_date);
        $perWeek = min(7, max(0, (int) ($info?->cleanings_per_week ?? 0)));
        $today = $now->startOfDay();
        $events = [];

        if ($perWeek > 0) {
            $interval = max(1, intdiv(7, $perWeek));

            for ($day = $checkInDay->addDays($interval); $day->lt($checkOutDay); $day = $day->addDays($interval)) {
                $events[$day->toDateString()] = ['date' => $day, 'type' => 'cleaning'];
            }
        }

        $services = $booking->relationLoaded('serviceRequests')
            ? $booking->serviceRequests
            : $booking->serviceRequests()->get();

        foreach ($services as $service) {
            if (! $service->isOpen()) {
                continue;
            }

            $day = CarbonImmutable::parse($service->service_date->toDateString(), self::TIMEZONE);
            $events[$day->toDateString()] = ['date' => $day, 'type' => 'extra', 'pending' => $service->status === 'requested'];
        }

        ksort($events);
        $cleanings = array_values($events);

        // Keep the timeline short on long stays: the last cleaning done and
        // the next two.
        $pastCleanings = array_values(array_filter($cleanings, fn ($event) => $event['date']->lt($today)));
        $futureCleanings = array_values(array_filter($cleanings, fn ($event) => $event['date']->gte($today)));
        $shown = array_merge(array_slice($pastCleanings, -1), array_slice($futureCleanings, 0, 2));

        $timeline = [['date' => $checkInDay, 'type' => 'check_in']];
        foreach ($shown as $event) {
            $timeline[] = $event;
        }
        $timeline[] = ['date' => $checkOutDay, 'type' => 'check_out'];

        $nextMarked = false;
        $cleaningCount = 0;
        $items = [];

        foreach ($timeline as $event) {
            $done = $event['date']->lt($today) || ($event['type'] === 'check_in' && $stage === 'current');
            $isCleaning = in_array($event['type'], ['cleaning', 'extra'], true);
            $state = $done ? 'done' : 'todo';

            if (! $done && $isCleaning && ! $nextMarked) {
                $state = 'next';
                $nextMarked = true;
            }

            $label = match ($event['type']) {
                'check_in' => 'Check-in',
                'check_out' => 'Check-out',
                default => $this->cleaningLabel($event, $done, $cleaningCount++, $stage),
            };

            $items[] = [
                'date' => $event['date']->toDateString(),
                'date_label' => $stage === 'current'
                    ? 'Day '.((int) $checkInDay->diffInDays($event['date']) + 1)
                    : $event['date']->format('D, M j'),
                'label' => $label,
                'state' => $state,
            ];
        }

        $fee = $this->extraCleaningFee($booking->apartment);
        $cutoff = (int) config('vietstays.guest_area.same_day_cleaning_cutoff_hour', 10);

        return [
            'cleanings_per_week' => $perWeek,
            'badge' => $stage === 'current'
                ? 'Today is Day '.((int) $checkInDay->diffInDays($today) + 1)
                : $this->perWeekLabel($perWeek),
            'timeline' => $items,
            'extra_cleaning' => [
                'available' => $fee > 0 && in_array($stage, ['upcoming', 'current'], true) && $booking->status !== 'cancelled',
                'price' => $fee,
                'cutoff_label' => $this->hourLabel($cutoff),
                'earliest_date' => $this->earliestCleaningDate($booking, $now)?->toDateString(),
                'latest_date' => $checkOutDay->subDay()->toDateString(),
                'slots' => collect(config('vietstays.guest_area.cleaning_slots', []))
                    ->map(fn (array $slot, string $key) => ['key' => $key] + $slot)
                    ->values()
                    ->all(),
            ],
            'requests' => $services
                ->sortBy('service_date')
                ->map(fn (BookingServiceRequest $service) => $this->presentServiceRequest($service))
                ->values()
                ->all(),
        ];
    }

    /**
     * First day an extra cleaning can still be ordered for, or null when
     * the stay has no such day left.
     */
    public function earliestCleaningDate(Booking $booking, ?CarbonImmutable $now = null): ?CarbonImmutable
    {
        $now ??= $this->now();
        $cutoff = (int) config('vietstays.guest_area.same_day_cleaning_cutoff_hour', 10);
        // No cleaning on the arrival day: the apartment was just cleaned.
        $first = $this->localDate($booking->check_in_date)->addDay();
        $today = $now->startOfDay();
        $earliestToday = $now->hour < $cutoff ? $today : $today->addDay();
        $earliest = $first->gt($earliestToday) ? $first : $earliestToday;
        $last = $this->localDate($booking->check_out_date)->subDay();

        return $earliest->lte($last) ? $earliest : null;
    }

    public function extraCleaningFee(?Apartment $apartment): float
    {
        $fee = (float) ($apartment?->extra_cleaning_fee ?? 0);

        return $fee > 0 ? $fee : (float) ($apartment?->cleaning_fee ?? 0);
    }

    /**
     * @return array<string, mixed>
     */
    public function presentServiceRequest(BookingServiceRequest $service): array
    {
        $slots = config('vietstays.guest_area.cleaning_slots', []);
        $slot = $slots[$service->time_slot] ?? null;

        return [
            'id' => $service->id,
            'type' => $service->type,
            'date' => $service->service_date->toDateString(),
            'date_label' => $service->service_date->format('D, M j'),
            'time_slot' => $service->time_slot,
            'time_label' => $slot ? $slot['label'].' · '.$slot['hours'] : $service->time_slot,
            'price' => (float) $service->price,
            'status' => $service->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function passportSummary(Booking $booking, string $stage): array
    {
        $expected = max(1, (int) $booking->adults + (int) $booking->children);
        $guests = $booking->relationLoaded('guests') ? $booking->guests : $booking->guests()->get();

        return [
            'expected' => $expected,
            'registered' => min($expected, $guests->count()),
            'editable' => in_array($stage, ['upcoming', 'current'], true),
            'guests' => $this->presentGuests($guests, $expected),
        ];
    }

    /**
     * One row per expected guest, filled or not.
     *
     * @param  Collection<int, BookingGuest>  $guests
     * @return array<int, array<string, mixed>>
     */
    public function presentGuests(Collection $guests, int $expected): array
    {
        $byPosition = $guests->keyBy('position');
        $rows = [];

        for ($position = 1; $position <= max($expected, (int) $guests->max('position')); $position++) {
            /** @var BookingGuest|null $guest */
            $guest = $byPosition->get($position);

            $rows[] = $guest ? [
                'position' => $position,
                'id' => $guest->id,
                'complete' => true,
                'full_name' => $guest->full_name,
                'nationality' => $guest->nationality,
                'passport_last4' => Str::substr((string) $guest->passport_number, -4),
                'passport_expiry' => $guest->passport_expiry?->toDateString(),
                'has_photo' => filled($guest->photo_path),
            ] : [
                'position' => $position,
                'id' => null,
                'complete' => false,
            ];
        }

        return $rows;
    }

    /**
     * What cancelling now would cost. Bookings are paid on arrival, so in
     * practice nothing is charged; the late fee is shown when configured.
     *
     * @return array<string, mixed>
     */
    public function cancellationQuote(Booking $booking, ?CarbonImmutable $now = null, ?string $stage = null): array
    {
        $now ??= $this->now();
        $stage ??= $this->stage($booking, $now);
        $freeDays = max(0, (int) config('vietstays.guest_area.free_cancellation_days', 0));
        $latePercent = min(100, max(0, (int) config('vietstays.guest_area.late_cancellation_percent', 0)));
        $checkIn = $this->checkInAt($booking);
        $freeUntil = $freeDays > 0 ? $this->localDate($booking->check_in_date)->subDays($freeDays) : $checkIn;
        $isLate = $latePercent > 0 && $now->gte($freeUntil);
        $total = (float) $booking->total;
        $paysOnSite = ($this->extra($booking)['payment_method'] ?? 'onsite') !== 'card';
        $charge = $isLate ? round($total * $latePercent / 100) : 0.0;

        return [
            'allowed' => $stage === 'upcoming' && in_array($booking->status, ['pending', 'confirmed'], true),
            'free_until' => $freeUntil->toDateString(),
            'free_until_label' => $freeDays > 0 ? $freeUntil->format('M j, Y') : 'check-in',
            'late_percent' => $latePercent,
            'is_late' => $isLate,
            'total' => $total,
            'charge' => $charge,
            'refund' => $paysOnSite ? 0.0 : max(0, $total - $charge),
            'pays_on_site' => $paysOnSite,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reviewState(Booking $booking, string $stage): array
    {
        /** @var BookingReview|null $review */
        $review = $booking->relationLoaded('review') ? $booking->review : $booking->review()->first();
        $eligible = $stage === 'past' && $booking->status === 'confirmed';

        return [
            'eligible' => $eligible,
            'submitted' => $review?->rating !== null,
            'skipped' => $review !== null && $review->rating === null && $review->skipped_at !== null,
            'rating' => $review?->rating,
            'category_ratings' => $review?->category_ratings ?? [],
            'comment' => $review?->comment,
            'categories' => collect(BookingReview::CATEGORIES)
                ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
                ->values()
                ->all(),
        ];
    }

    public function hostFor(?Apartment $apartment): ?User
    {
        $ownerId = (int) ($apartment?->user_id ?? 0);

        if ($ownerId <= 0) {
            return null;
        }

        if (! array_key_exists($ownerId, $this->hosts)) {
            $this->hosts[$ownerId] = User::query()->where('legacy_wp_id', $ownerId)->first();
        }

        return $this->hosts[$ownerId];
    }

    public function guestInfoFor(?Apartment $apartment): ?ApartmentGuestInfo
    {
        $id = (int) ($apartment?->ID ?? 0);

        if ($id <= 0) {
            return null;
        }

        if (! array_key_exists($id, $this->guestInfo)) {
            $this->guestInfo[$id] = ApartmentGuestInfo::query()->where('apartment_id', $id)->first();
        }

        return $this->guestInfo[$id];
    }

    public function apartmentName(?Apartment $apartment): string
    {
        return (string) (($apartment?->display_name ?: $apartment?->name) ?: 'Apartment');
    }

    /**
     * "Quận 1, Ho Chi Minh City"
     */
    public function areaLabel(?Apartment $apartment): string
    {
        if (! $apartment) {
            return '';
        }

        $district = District::query()->find($apartment->district);
        $city = $district ? City::query()->find($district->city_id) : null;

        return collect([$district?->name, $city?->name])->filter()->implode(', ');
    }

    public function coverImage(?Apartment $apartment): ?string
    {
        return $this->gallery($apartment)[0]['full'] ?? null;
    }

    /**
     * @return array<int, array{thumb: string|null, full: string|null}>
     */
    public function gallery(?Apartment $apartment): array
    {
        $images = is_array($apartment?->images) ? $apartment->images : [];

        return collect($images)
            ->map(fn ($image) => is_array($image) ? [
                'thumb' => LegacyMediaUrl::normalize($image['thumb'] ?? $image['image_id'] ?? null),
                'full' => LegacyMediaUrl::normalize($image['image_id'] ?? $image['url'] ?? $image['thumb'] ?? null),
            ] : null)
            ->filter(fn ($image) => $image && ($image['thumb'] || $image['full']))
            ->map(fn ($image) => ['thumb' => $image['thumb'] ?: $image['full'], 'full' => $image['full'] ?: $image['thumb']])
            ->values()
            ->all();
    }

    protected function address(?Apartment $apartment): string
    {
        if (! $apartment) {
            return '';
        }

        $building = $apartment->building_id ? Building::query()->find($apartment->building_id) : null;

        return (string) ($apartment->address ?: $building?->address ?: $this->areaLabel($apartment));
    }

    protected function cityName(?Apartment $apartment): ?string
    {
        $district = $apartment ? District::query()->find($apartment->district) : null;

        return $district ? City::query()->find($district->city_id)?->name : null;
    }

    protected function mapUrl(?Apartment $apartment): ?string
    {
        $lat = $this->coordinate($apartment?->address_latitude);
        $lng = $this->coordinate($apartment?->address_longitude);

        if ($lat !== null && $lng !== null) {
            return 'https://www.google.com/maps/search/?api=1&query='.$lat.','.$lng;
        }

        $address = $this->address($apartment);

        return $address !== '' ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address) : null;
    }

    protected function coordinate(mixed $value): ?float
    {
        return is_numeric($value) && (float) $value !== 0.0 ? (float) $value : null;
    }

    /**
     * @return array<int, string>
     */
    protected function amenities(?Apartment $apartment): array
    {
        $ids = collect(is_array($apartment?->facilities) ? $apartment->facilities : [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id);

        if ($ids->isEmpty()) {
            return [];
        }

        return Facility::query()
            ->whereIn('facility_id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->all();
    }

    /**
     * House rules as a list of lines (the host writes free text).
     *
     * @return array<int, string>
     */
    protected function houseRules(?Apartment $apartment): array
    {
        $text = (string) ($apartment?->house_rules ?? '');
        $text = preg_replace('#<\s*(br|/p|/li)\s*/?>#i', "\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5);

        return collect(preg_split('/\r\n|\r|\n/', $text) ?: [])
            ->map(fn ($line) => trim((string) $line, " \t-•*"))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function extra(Booking $booking): array
    {
        return is_array($booking->extra_data) ? $booking->extra_data : [];
    }

    public function nights(Booking $booking): int
    {
        return max(1, (int) $this->localDate($booking->check_in_date)->diffInDays($this->localDate($booking->check_out_date)));
    }

    public function localDate(mixed $date): CarbonImmutable
    {
        return CarbonImmutable::parse(CarbonImmutable::parse($date)->toDateString(), self::TIMEZONE);
    }

    public function money(float $amount): string
    {
        return number_format(round($amount)).' '.config('vietstays.site_currency', 'VND');
    }

    protected function atLocalTime(mixed $date, string $time): CarbonImmutable
    {
        return CarbonImmutable::parse($this->localDate($date)->toDateString().' '.$time, self::TIMEZONE);
    }

    /**
     * Legacy rows store "00:00:00" when the host never set a time.
     */
    protected function timeOf(?string $value, string $default): string
    {
        $value = trim((string) $value);

        return $value === '' || str_starts_with($value, '00:00') ? $default : substr($value, 0, 5);
    }

    protected function nightsLabel(int $nights): string
    {
        return $nights.' '.Str::plural('night', $nights);
    }

    protected function perWeekLabel(int $perWeek): string
    {
        return match ($perWeek) {
            0 => 'Not included',
            1 => 'Included once per week',
            2 => 'Included twice per week',
            7 => 'Included daily',
            default => 'Included '.$perWeek.' times per week',
        };
    }

    protected function hourLabel(int $hour): string
    {
        return CarbonImmutable::createFromTime($hour)->format('g:i A');
    }

    /**
     * @param  array<string, mixed>  $event
     */
    protected function cleaningLabel(array $event, bool $done, int $index, string $stage): string
    {
        if ($event['type'] === 'extra') {
            return ! empty($event['pending']) ? 'Extra cleaning (requested)' : 'Extra cleaning';
        }

        if ($done) {
            return 'Cleaning done';
        }

        return $index === 0 && $stage === 'upcoming' ? 'First cleaning' : 'Next cleaning';
    }
}
