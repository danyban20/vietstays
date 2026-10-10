<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingReview;
use App\Services\GuestStayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
    ) {}

    public function store(Request $request, int $booking): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);

        if (! $this->eligible($model)) {
            return response()->json(['message' => 'You can review a stay once it is finished.'], 422);
        }

        if ($model->review()->whereNotNull('rating')->exists()) {
            return response()->json(['message' => 'You have already reviewed this stay.'], 422);
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'category_ratings' => ['nullable', 'array'],
            'category_ratings.*' => ['integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $categories = collect($data['category_ratings'] ?? [])
            ->only(array_keys(BookingReview::CATEGORIES))
            ->map(fn ($value) => (int) $value)
            ->all();

        BookingReview::query()->updateOrCreate(
            ['booking_id' => $model->ID],
            [
                'apartment_id' => $model->apartment_id,
                'member_user_id' => $request->user()->id,
                'rating' => (int) $data['rating'],
                'category_ratings' => $categories ?: null,
                'comment' => filled($data['comment'] ?? null) ? trim($data['comment']) : null,
                'skipped_at' => null,
            ],
        );

        return response()->json(['message' => 'Thank you for your review!'], 201);
    }

    public function skip(Request $request, int $booking): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);

        if ($this->eligible($model) && ! $model->review()->exists()) {
            BookingReview::query()->create([
                'booking_id' => $model->ID,
                'apartment_id' => $model->apartment_id,
                'member_user_id' => $request->user()->id,
                'skipped_at' => now(),
            ]);
        }

        return response()->json(['message' => 'No problem — you can review it later from this page.']);
    }

    protected function eligible(Booking $booking): bool
    {
        return $booking->status === 'confirmed' && $this->stays->stage($booking) === 'past';
    }
}
