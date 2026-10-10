<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\ApartmentGuestInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The host's "Guest arrival" tab: door code, Wi-Fi, arrival notes,
 * check-in/out times and included cleanings, shown to guests in their
 * booking.
 */
class ApartmentGuestInfoController extends Controller
{
    public function show(Request $request, int $apartment): JsonResponse
    {
        $model = $this->authorizedApartment($request, $apartment);

        return response()->json(['data' => $this->present($model)]);
    }

    public function update(Request $request, int $apartment): JsonResponse
    {
        $model = $this->authorizedApartment($request, $apartment);

        $data = $request->validate([
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'door_code' => ['nullable', 'string', 'max:50'],
            'door_code_note' => ['nullable', 'string', 'max:255'],
            'wifi_network' => ['nullable', 'string', 'max:100'],
            'wifi_password' => ['nullable', 'string', 'max:100'],
            'arrival_instructions' => ['nullable', 'string', 'max:3000'],
            'parking_info' => ['nullable', 'string', 'max:1000'],
            'arrival_contact_label' => ['nullable', 'string', 'max:100'],
            'arrival_contact_phone' => ['nullable', 'string', 'max:30'],
            'security_phone' => ['nullable', 'string', 'max:30'],
            'cleanings_per_week' => ['nullable', 'integer', 'between:0,7'],
        ]);

        $model->update([
            'check_in_time1' => filled($data['check_in_time'] ?? null) ? $data['check_in_time'].':00' : '00:00:00',
            'check_out_time' => filled($data['check_out_time'] ?? null) ? $data['check_out_time'].':00' : '00:00:00',
            'datemodified' => now(),
        ]);

        $info = ApartmentGuestInfo::query()->firstOrNew(['apartment_id' => $model->ID]);
        $info->fill(collect($data)
            ->except(['check_in_time', 'check_out_time'])
            ->map(fn ($value) => is_string($value) ? (trim($value) === '' ? null : trim($value)) : $value)
            ->all());
        $info->cleanings_per_week = (int) ($data['cleanings_per_week'] ?? 0);
        $info->save();

        return response()->json([
            'data' => $this->present($model->fresh()),
            'message' => 'Guest arrival details saved.',
        ]);
    }

    protected function authorizedApartment(Request $request, int $id): Apartment
    {
        $apartment = Apartment::query()->findOrFail($id);
        $user = $request->user();

        if ($user->isAdmin()) {
            return $apartment;
        }

        if ($user->isOperator() && (int) $apartment->user_id === (int) $user->legacy_wp_id) {
            return $apartment;
        }

        abort(403);
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(Apartment $apartment): array
    {
        $info = ApartmentGuestInfo::query()->where('apartment_id', $apartment->ID)->first();
        $time = fn (?string $value) => $value && ! str_starts_with($value, '00:00') ? substr($value, 0, 5) : '';

        return [
            'check_in_time' => $time($apartment->check_in_time1),
            'check_out_time' => $time($apartment->check_out_time),
            'door_code' => $info?->door_code ?? '',
            'door_code_note' => $info?->door_code_note ?? '',
            'wifi_network' => $info?->wifi_network ?? '',
            'wifi_password' => $info?->wifi_password ?? '',
            'arrival_instructions' => $info?->arrival_instructions ?? '',
            'parking_info' => $info?->parking_info ?? '',
            'arrival_contact_label' => $info?->arrival_contact_label ?? '',
            'arrival_contact_phone' => $info?->arrival_contact_phone ?? '',
            'security_phone' => $info?->security_phone ?? '',
            'cleanings_per_week' => (int) ($info?->cleanings_per_week ?? 0),
            'access_reveal_hours' => (int) config('vietstays.guest_area.access_reveal_hours', 24),
        ];
    }
}
