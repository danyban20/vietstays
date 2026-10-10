<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\BookingServiceRequest;
use App\Services\BookingConfirmationEmailService;
use App\Services\GuestStayService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Extra cleaning ordered by the guest. The host confirms it; it is paid
 * on site.
 */
class ServiceRequestController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
        protected BookingConfirmationEmailService $emails,
    ) {}

    public function store(Request $request, int $booking): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);
        $stage = $this->stays->stage($model);
        $fee = $this->stays->extraCleaningFee($model->apartment);
        $earliest = $this->stays->earliestCleaningDate($model);

        if (! in_array($stage, ['upcoming', 'current'], true) || $fee <= 0 || ! $earliest) {
            return response()->json(['message' => 'Extra cleaning can no longer be ordered for this stay.'], 422);
        }

        $latest = $this->stays->localDate($model->check_out_date)->subDay();
        $cutoff = (int) config('vietstays.guest_area.same_day_cleaning_cutoff_hour', 10);

        $data = $request->validate([
            'service_date' => ['required', 'date_format:Y-m-d'],
            'time_slot' => ['required', Rule::in(array_keys(config('vietstays.guest_area.cleaning_slots', [])))],
        ]);

        $date = CarbonImmutable::parse($data['service_date'], GuestStayService::TIMEZONE);

        if ($date->lt($earliest) || $date->gt($latest)) {
            return response()->json([
                'message' => 'Pick a day between '.$earliest->format('M j').' and '.$latest->format('M j')
                    .'. Same-day cleaning must be ordered before '.CarbonImmutable::createFromTime($cutoff)->format('g:i A').'.',
                'errors' => ['service_date' => ['Pick a day during your stay.']],
            ], 422);
        }

        $taken = $model->serviceRequests()
            ->whereDate('service_date', $date->toDateString())
            ->whereIn('status', ['requested', 'confirmed'])
            ->exists();

        if ($taken) {
            return response()->json([
                'message' => 'You already have a cleaning on that day.',
                'errors' => ['service_date' => ['You already have a cleaning on that day.']],
            ], 422);
        }

        $service = BookingServiceRequest::query()->create([
            'booking_id' => $model->ID,
            'type' => 'extra_cleaning',
            'service_date' => $date->toDateString(),
            'time_slot' => $data['time_slot'],
            'price' => $fee,
            'status' => 'requested',
            'requested_by' => $request->user()->id,
        ]);

        $this->emails->sendHostExtraCleaningRequested($service->setRelation('booking', $model));

        return response()->json([
            'data' => $this->stays->presentServiceRequest($service),
            'message' => 'Extra cleaning requested. Your host will confirm it.',
        ], 201);
    }

    public function destroy(Request $request, int $booking, int $service): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);
        $item = $model->serviceRequests()->whereKey($service)->firstOrFail();

        if ($item->status !== 'requested') {
            return response()->json(['message' => 'Only requests the host has not confirmed yet can be withdrawn.'], 422);
        }

        $item->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Request withdrawn.']);
    }
}
