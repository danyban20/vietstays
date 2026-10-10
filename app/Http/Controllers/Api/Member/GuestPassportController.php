<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Models\BookingGuest;
use App\Services\GuestStayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Passport details per guest, which the host uses to register foreign
 * guests' temporary stay. Photos live on the private disk.
 */
class GuestPassportController extends Controller
{
    public function __construct(
        protected GuestStayService $stays,
    ) {}

    /**
     * Full details of one guest, for editing.
     */
    public function show(Request $request, int $booking, int $position): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);
        $guest = $model->guests()->where('position', $position)->firstOrFail();

        return response()->json(['data' => $this->present($guest)]);
    }

    public function store(Request $request, int $booking): JsonResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);
        $summary = $this->stays->passportSummary($model, $this->stays->stage($model));

        if (! $summary['editable']) {
            return response()->json(['message' => 'Passport details can only be changed before or during the stay.'], 422);
        }

        $data = $request->validate([
            'position' => ['required', 'integer', 'min:1', 'max:'.$summary['expected']],
            'full_name' => ['required', 'string', 'max:150'],
            'nationality' => ['required', 'string', 'regex:/^[A-Z]{2}$/'],
            'passport_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9]{5,20}$/'],
            'date_of_birth' => ['required', 'date_format:Y-m-d', 'before:today'],
            'passport_expiry' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:8192'],
        ], [
            'passport_number.regex' => 'Use letters and numbers only, as printed on the passport.',
            'passport_expiry.after_or_equal' => 'This passport has expired.',
        ]);

        $guest = BookingGuest::query()->firstOrNew([
            'booking_id' => $model->ID,
            'position' => (int) $data['position'],
        ]);

        $guest->fill([
            'full_name' => trim($data['full_name']),
            'nationality' => $data['nationality'],
            'passport_number' => strtoupper($data['passport_number']),
            'date_of_birth' => $data['date_of_birth'],
            'passport_expiry' => $data['passport_expiry'],
        ]);

        if ($request->hasFile('photo')) {
            $old = $guest->photo_path;
            $guest->photo_path = $request->file('photo')->store('guest-passports/'.$model->ID, 'local');

            if ($old) {
                Storage::disk('local')->delete($old);
            }
        }

        $guest->save();

        return response()->json([
            'data' => $this->stays->passportSummary($model->fresh(), $this->stays->stage($model)),
            'message' => 'Passport details saved.',
        ]);
    }

    public function photo(Request $request, int $booking, int $position): StreamedResponse
    {
        $model = $this->stays->findForUser($request->user(), $booking);
        $guest = $model->guests()->where('position', $position)->firstOrFail();

        abort_unless($guest->photo_path && Storage::disk('local')->exists($guest->photo_path), 404);

        return Storage::disk('local')->response($guest->photo_path, null, [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(BookingGuest $guest): array
    {
        return [
            'position' => $guest->position,
            'full_name' => $guest->full_name,
            'nationality' => $guest->nationality,
            'passport_number' => $guest->passport_number,
            'date_of_birth' => $guest->date_of_birth,
            'passport_expiry' => $guest->passport_expiry?->toDateString(),
            'has_photo' => filled($guest->photo_path),
        ];
    }
}
