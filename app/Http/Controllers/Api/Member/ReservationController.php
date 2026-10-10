<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\BookingConfirmationEmailService;
use App\Services\GuestMessagingService;
use App\Services\GuestStayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
        protected GuestMessagingService $messages,
        protected BookingConfirmationEmailService $emails,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $now = $this->stays->now();

        $bookings = $this->stays->bookingsQuery($request->user())
            ->orderByDesc('check_in_date')
            ->get()
            ->map(fn (Booking $booking) => $this->stays->summary($booking, $now))
            ->values();

        return response()->json(['data' => $bookings]);
    }

    public function show(Request $request, int $booking): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $model = $this->stays->findForUser($user, $booking);
        $model->load(['guests', 'serviceRequests', 'review']);

        return response()->json([
            'data' => $this->stays->detail($model) + [
                'conversation_id' => $this->messages->conversationIdForBooking($user, $model),
            ],
        ]);
    }

    /**
     * The guest cancels their own upcoming booking.
     */
    public function cancel(Request $request, int $booking): JsonResponse
    {
        $request->validate([
            'accept_policy' => ['accepted'],
        ]);

        $model = $this->stays->findForUser($request->user(), $booking);
        $quote = $this->stays->cancellationQuote($model);

        if (! $quote['allowed']) {
            return response()->json(['message' => 'This booking can no longer be cancelled online. Please contact us.'], 422);
        }

        $extra = $this->stays->extra($model);
        $extra['cancelled_by'] = 'guest';
        $extra['cancelled_at'] = now()->toIso8601String();
        $extra['cancellation_charge'] = $quote['charge'];

        $model->update([
            'status' => 'cancelled',
            'extra_data' => $extra,
            'datemodified' => now(),
        ]);

        $model->serviceRequests()
            ->whereIn('status', ['requested', 'confirmed'])
            ->update(['status' => 'cancelled']);

        $this->emails->sendGuestStatusChange($model);
        $this->emails->sendHostGuestCancelled($model);

        return response()->json([
            'data' => $this->stays->detail($model->fresh(['apartment', 'guests', 'serviceRequests', 'review'])),
            'message' => 'Your reservation has been cancelled.',
        ]);
    }
}
