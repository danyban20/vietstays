<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\WordPressPasswordVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * "My account" on the public site: the signed-in user's profile and the
 * bookings they placed while signed in.
 */
class AccountController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // Only bookings tied to this account. Matching older guest bookings by
        // email would let anyone who registers with someone else's address
        // see that person's stays, so it waits for email verification.
        $bookings = Booking::query()
            ->with('apartment:ID,name,display_name')
            ->where('member_user_id', $user->id)
            ->orderByDesc('check_in_date')
            ->get()
            ->map(fn (Booking $booking) => [
                'id' => $booking->ID,
                'booking_num' => $booking->booking_num,
                'apartment_id' => $booking->apartment_id,
                'apartment_name' => $booking->apartment?->display_name ?: $booking->apartment?->name,
                'check_in' => $booking->check_in_date?->format('Y-m-d'),
                'check_out' => $booking->check_out_date?->format('Y-m-d'),
                'status' => $booking->status,
                'total' => (float) $booking->total,
            ]);

        return response()->json([
            'data' => [
                'profile' => $this->profile($user),
                'bookings' => $bookings,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update([
            'name' => trim($data['name']),
            'display_name' => trim($data['name']),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
        ]);

        return response()->json([
            'data' => ['profile' => $this->profile($user->fresh())],
            'message' => 'Profile updated.',
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! $this->passwordMatches($data['current_password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Your current password is not correct.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password changed.']);
    }

    protected function passwordMatches(string $plain, string $stored): bool
    {
        try {
            if (Hash::check($plain, $stored)) {
                return true;
            }
        } catch (\RuntimeException) {
            // Legacy WordPress hash: Hash::check() throws instead of failing.
        }

        return WordPressPasswordVerifier::check($plain, $stored);
    }

    /**
     * @return array<string, mixed>
     */
    protected function profile(User $user): array
    {
        return [
            'name' => $user->display_name ?: $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'can_use_dashboard' => $user->canUseDashboard(),
            'member_since' => $user->created_at?->toDateString(),
        ];
    }
}
