<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\Building;
use App\Models\District;
use App\Services\ApartmentCreationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApartmentController extends Controller
{
    public function __construct(
        protected ApartmentCreationService $apartmentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Apartment::query()->orderByDesc('datemodified');

        if ($request->user()->isPartner() && ! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->legacy_wp_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $apartments = $query
            ->withCount('bookings')
            ->with(['bookings' => function ($q) {
                $q->where('check_out_date', '>=', now()->startOfDay())
                    ->orderBy('check_in_date')
                    ->limit(5);
            }])
            ->get()
            ->map(fn (Apartment $apt) => $this->transform($apt, false, true));

        return response()->json(['data' => $apartments]);
    }

    public function show(Request $request, int $apartment): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        return response()->json(['data' => $this->transform($model, true)]);
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('Apartment store request', [
            'user_id' => $request->user()?->id,
            'building_id' => $request->input('building_id'),
        ]);

        $validated = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'district_id' => ['nullable', 'integer'],
            'apartment_type' => ['required', Rule::in(['Studio', '1BR', '2BR', '3BR', '4BR'])],
            'quality_standard' => ['nullable', Rule::in(['standard', 'above_average', 'premium'])],
            'distinguishing_feature' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:200'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'about_this_short' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer'],
            'images' => ['nullable', 'array'],
            'price_daily' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::in(['draft', 'pending', 'active'])],
            'building_gallery_json' => ['nullable', 'array'],
        ]);

        $validated['quality_standard'] = $validated['quality_standard'] ?? 'standard';

        $apartment = $this->apartmentService->create($validated, $request->user());

        return response()->json([
            'data' => $this->transform($apartment, true),
            'message' => 'Apartment created.',
        ], 201);
    }

    public function update(Request $request, int $apartment): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        $validated = $request->validate([
            'building_id' => ['sometimes', 'integer', 'exists:buildings,id'],
            'district_id' => ['sometimes', 'integer'],
            'apartment_type' => ['sometimes', Rule::in(['Studio', '1BR', '2BR', '3BR', '4BR'])],
            'quality_standard' => ['sometimes', Rule::in(['standard', 'above_average', 'premium'])],
            'distinguishing_feature' => ['nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'string', 'max:200'],
            'display_name' => ['sometimes', 'string', 'max:200'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'about_this_short' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['integer'],
            'images' => ['nullable', 'array'],
            'price_daily' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['draft', 'pending', 'active'])],
        ]);

        $apartment = $this->apartmentService->update($model, $validated);

        return response()->json([
            'data' => $this->transform($apartment, true),
            'message' => 'Apartment updated.',
        ]);
    }

    public function uploadPhotos(Request $request, int $apartment): JsonResponse
    {
        $model = Apartment::query()->findOrFail($apartment);
        $this->authorizeApartment($request, $model);

        $request->validate([
            'photos' => ['required', 'array', 'max:20'],
            'photos.*' => ['image', 'max:8192'],
        ]);

        $images = is_array($model->images) ? $model->images : [];
        $order = count(array_filter($images, fn ($img) => ! empty($img['image_id'] ?? $img['thumb'] ?? '')));

        foreach ($request->file('photos', []) as $file) {
            $path = $file->store('apartments/'.$model->ID, 'public');
            $url = Storage::disk('public')->url($path);
            $order++;
            $images[] = [
                'order' => $order,
                'thumb' => $url,
                'image_id' => $path,
                'caption' => '',
            ];
        }

        $model->update([
            'images' => $images,
            'datemodified' => now(),
        ]);

        return response()->json([
            'data' => $this->transform($model->fresh(), true),
            'message' => 'Photos uploaded.',
        ]);
    }

    protected function authorizeApartment(Request $request, Apartment $apartment): void
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($user->isPartner() && (int) $apartment->user_id === (int) $user->legacy_wp_id) {
            return;
        }

        abort(403);
    }

    protected function transform(Apartment $apartment, bool $detailed = false, bool $forList = false): array
    {
        $building = $apartment->building_id
            ? Building::query()->find($apartment->building_id)
            : null;

        $district = District::query()->find($apartment->district);

        $priceDaily = (float) $apartment->price_daily;

        $data = [
            'id' => $apartment->ID,
            'name' => $apartment->display_name ?: $apartment->name,
            'district' => $district?->name,
            'district_id' => (int) $apartment->district,
            'building' => $building?->name,
            'building_id' => (int) $apartment->building_id,
            'type' => $apartment->apartment_type,
            'standard' => $apartment->quality_standard,
            'price_daily' => $priceDaily,
            'price' => $priceDaily,
            'status' => $apartment->status,
            'completion' => $this->completionPercent($apartment),
        ];

        if ($forList) {
            $data = array_merge($data, $this->listMeta($apartment));
        }

        if ($detailed) {
            $data += [
                'distinguishing_feature' => $apartment->distinguishing_feature,
                'description' => $apartment->description,
                'about_this_short' => $apartment->about_this_short,
                'facilities' => $apartment->facilities ?? [],
                'images' => $apartment->images ?? [],
                'pricing' => $apartment->pricing,
                'cleaning_fee' => (float) $apartment->cleaning_fee,
                'room_number' => $apartment->room_number,
                'floor_number' => $apartment->floor_number,
            ];
        }

        return $data;
    }

    protected function listMeta(Apartment $apartment): array
    {
        $today = now()->startOfDay();
        $bookings = $apartment->relationLoaded('bookings') ? $apartment->bookings : collect();

        $activeBooking = $bookings->first(function (Booking $booking) use ($today) {
            return $booking->check_in_date <= $today && $booking->check_out_date > $today;
        });

        $nextBooking = $bookings->first(function (Booking $booking) use ($today) {
            return $booking->check_in_date > $today;
        });

        $bedrooms = match ($apartment->apartment_type) {
            'Studio' => 0,
            '1BR' => 1,
            '2BR' => 2,
            '3BR' => 3,
            '4BR' => 4,
            default => null,
        };

        $completion = $this->completionPercent($apartment);
        $isUrgent = $apartment->status === 'draft' && $completion < 80;

        $currentStatus = 'available';
        $currentStatusLabel = 'Available';

        if ($activeBooking) {
            $currentStatus = 'guest_inside';
            $currentStatusLabel = 'Guest inside';
        } elseif ($apartment->status === 'draft') {
            $currentStatus = 'draft';
            $currentStatusLabel = 'Draft';
        } elseif ($apartment->status === 'pending') {
            $currentStatus = 'pending';
            $currentStatusLabel = 'Pending';
        }

        $nextBookingPayload = null;

        if ($nextBooking) {
            $daysUntil = (int) $today->diffInDays($nextBooking->check_in_date, false);
            $nextBookingPayload = [
                'date' => $nextBooking->check_in_date->format('Y-m-d'),
                'date_label' => $nextBooking->check_in_date->format('M j'),
                'days_until' => max(0, $daysUntil),
                'guest' => trim($nextBooking->firstname.' '.$nextBooking->lastname) ?: 'Guest',
            ];
        }

        $bookingsCount = (int) ($apartment->bookings_count ?? $bookings->count());
        $activityPercent = min(100, max(8, $bookingsCount * 12));

        return [
            'code' => 'VS'.str_pad((string) $apartment->ID, 3, '0', STR_PAD_LEFT),
            'bedrooms' => $bedrooms,
            'bedroom_label' => $apartment->apartment_type,
            'location_label' => trim(($district?->name ?? '').', '.($building?->name ?? ''), ', '),
            'current_status' => $currentStatus,
            'current_status_label' => $currentStatusLabel,
            'next_booking' => $nextBookingPayload,
            'bookings_count' => $bookingsCount,
            'activity_label' => $bookingsCount === 1 ? '1 booking' : "{$bookingsCount} bookings",
            'rating' => null,
            'activity_percent' => $activityPercent,
            'is_urgent' => $isUrgent,
            'is_new' => $apartment->dateadded && $apartment->dateadded->greaterThan(now()->subDays(14)),
            'owner_name' => null,
        ];
    }

    protected function completionPercent(Apartment $apartment): int
    {
        $checks = [
            filled($apartment->display_name),
            filled($apartment->apartment_type),
            filled($apartment->description),
            $this->hasValidImages($apartment),
            is_array($apartment->facilities) && count($apartment->facilities) > 0,
            (float) $apartment->price_daily > 0,
        ];

        return (int) round((count(array_filter($checks)) / count($checks)) * 100);
    }

    protected function hasValidImages(Apartment $apartment): bool
    {
        if (! is_array($apartment->images)) {
            return false;
        }

        foreach ($apartment->images as $image) {
            if (! is_array($image)) {
                continue;
            }

            $src = $image['thumb'] ?? $image['url'] ?? '';

            if (filled($src)) {
                return true;
            }
        }

        return false;
    }
}
