<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\GuestMessagingService;
use App\Services\GuestStayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The customer dashboard home: the stay that matters right now, unread
 * host messages and past stays.
 */
class DashboardController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
        protected GuestMessagingService $messages,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $now = $this->stays->now();

        $bookings = $this->stays->bookingsQuery($user)->orderBy('check_in_date')->get();
        $byStage = $bookings->groupBy(fn (Booking $booking) => $this->stays->stage($booking, $now));

        // The current stay first, otherwise the next upcoming one.
        $featured = $byStage->get('current', collect())->first()
            ?? $byStage->get('upcoming', collect())->first();

        $featuredData = null;

        if ($featured) {
            $detail = $this->stays->detail($featured);
            $featuredData = $this->stays->summary($featured, $now) + [
                'access' => $detail['access'],
                'host_name' => $detail['host']['name'],
                'conversation_id' => $this->messages->conversationIdForBooking($user, $featured),
            ];
        }

        $past = $byStage->get('past', collect())
            ->filter(fn (Booking $booking) => $booking->status === 'confirmed')
            ->sortByDesc('check_in_date')
            ->take(3)
            ->map(fn (Booking $booking) => $this->stays->summary($booking, $now))
            ->values();

        $name = trim((string) ($user->display_name ?: $user->name));

        return response()->json([
            'data' => [
                'first_name' => $name !== '' ? explode(' ', $name)[0] : 'there',
                'today' => $now->format('l, F j, Y'),
                'unread' => $this->messages->unread($user),
                'featured' => $featuredData,
                'upcoming_count' => $byStage->get('upcoming', collect())->count(),
                'past' => $past,
                'has_bookings' => $bookings->isNotEmpty(),
            ],
        ]);
    }
}
