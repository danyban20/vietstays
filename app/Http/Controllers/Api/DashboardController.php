<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $apartmentQuery = Apartment::query();
        $bookingQuery = Booking::query();

        if ($user->isPartner() && ! $user->isAdmin()) {
            $apartmentQuery->where('user_id', $user->legacy_wp_id);
            $bookingQuery->whereIn('apartment_id', function ($q) use ($user) {
                $q->select('ID')
                    ->from('vv_apartments')
                    ->where('user_id', $user->legacy_wp_id);
            });
        }

        $activeApartments = (clone $apartmentQuery)->where('status', 'active')->count();
        $draftApartments = (clone $apartmentQuery)->where('status', 'draft')->count();
        $pendingBookings = (clone $bookingQuery)->where('status', 'pending')->count();
        $upcomingBookings = (clone $bookingQuery)
            ->where('check_in_date', '>=', now()->startOfDay())
            ->count();

        return response()->json([
            'stats' => [
                ['key' => 'active_apartments', 'label' => 'Active apartments', 'value' => $activeApartments],
                ['key' => 'draft_apartments', 'label' => 'Draft apartments', 'value' => $draftApartments],
                ['key' => 'pending_bookings', 'label' => 'Pending bookings', 'value' => $pendingBookings],
                ['key' => 'upcoming_bookings', 'label' => 'Upcoming check-ins', 'value' => $upcomingBookings],
            ],
        ]);
    }
}
