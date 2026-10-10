<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingReview;
use App\Models\BookingServiceRequest;
use App\Services\BookingConfirmationEmailService;
use App\Services\GuestStayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * What the guest added to a booking from their account, as the host sees
 * it: passports, extra cleaning requests, cancellation and review.
 */
class BookingGuestServicesController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
        protected BookingConfirmationEmailService $emails,
    ) {}

    public function show(Request $request, int $booking): JsonResponse
    {
        $model = $this->authorizedBooking($request, $booking);
        $model->load(['guests', 'serviceRequests', 'review']);
        $extra = $this->stays->extra($model);
        $expected = max(1, (int) $model->adults + (int) $model->children);

        return response()->json([
            'data' => [
                'passports' => [
                    'expected' => $expected,
                    'registered' => min($expected, $model->guests->count()),
                    'guests' => $model->guests->map(fn (BookingGuest $guest) => [
                        'id' => $guest->id,
                        'position' => $guest->position,
                        'full_name' => $guest->full_name,
                        'nationality' => $guest->nationality,
                        'passport_number' => $guest->passport_number,
                        'date_of_birth' => $guest->date_of_birth,
                        'passport_expiry' => $guest->passport_expiry?->toDateString(),
                        'has_photo' => filled($guest->photo_path),
                        'updated_at' => $guest->updated_at?->toIso8601String(),
                    ])->values(),
                ],
                'service_requests' => $model->serviceRequests
                    ->map(fn (BookingServiceRequest $service) => $this->stays->presentServiceRequest($service))
                    ->values(),
                'cancelled_by_guest' => ($extra['cancelled_by'] ?? null) === 'guest',
                'cancelled_at' => $extra['cancelled_at'] ?? null,
                'review' => $this->presentReview($model->review),
                'member_account' => $model->member_user_id !== null,
            ],
        ]);
    }

    public function photo(Request $request, int $booking, int $guest): StreamedResponse
    {
        $model = $this->authorizedBooking($request, $booking);
        $record = $model->guests()->whereKey($guest)->firstOrFail();

        abort_unless($record->photo_path && Storage::disk('local')->exists($record->photo_path), 404);

        return Storage::disk('local')->response($record->photo_path, null, [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function updateServiceRequest(Request $request, int $booking, int $service): JsonResponse
    {
        $model = $this->authorizedBooking($request, $booking);
        $item = $model->serviceRequests()->whereKey($service)->firstOrFail();

        $data = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'declined'])],
        ]);

        if ($item->status === $data['status']) {
            return response()->json(['data' => $this->stays->presentServiceRequest($item)]);
        }

        if ($item->status === 'cancelled') {
            return response()->json(['message' => 'The guest withdrew this request.'], 422);
        }

        $item->update(['status' => $data['status']]);
        $emailed = $this->emails->sendGuestExtraCleaningUpdated($item->setRelation('booking', $model));

        return response()->json([
            'data' => $this->stays->presentServiceRequest($item),
            'message' => ($data['status'] === 'confirmed' ? 'Cleaning confirmed.' : 'Cleaning declined.')
                .($emailed ? ' The guest has been emailed.' : ''),
        ]);
    }

    protected function authorizedBooking(Request $request, int $id): Booking
    {
        $booking = Booking::query()->with('apartment')->findOrFail($id);
        $user = $request->user();

        if ($user->isAdmin()) {
            return $booking;
        }

        $owned = $user->isOperator() && Apartment::query()
            ->where('ID', $booking->apartment_id)
            ->where('user_id', $user->legacy_wp_id)
            ->exists();

        abort_unless($owned, 403);

        return $booking;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function presentReview(?BookingReview $review): ?array
    {
        if (! $review || $review->rating === null) {
            return null;
        }

        return [
            'rating' => $review->rating,
            'categories' => collect($review->category_ratings ?? [])
                ->map(fn ($value, $key) => [
                    'label' => BookingReview::CATEGORIES[$key] ?? $key,
                    'rating' => (int) $value,
                ])
                ->values(),
            'comment' => $review->comment,
            'created_at' => $review->created_at?->toIso8601String(),
        ];
    }
}
