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

        if (($user->isPartner() || $user->isHost()) && ! $user->isAdmin()) {
            $apartmentQuery->where('user_id', $user->legacy_wp_id);
            $bookingQuery->whereIn('apartment_id', function ($q) use ($user) {
                $q->select('ID')
                    ->from('vv_apartments')
                    ->where('user_id', $user->legacy_wp_id);
            });
        }

        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $activeApartments = (clone $apartmentQuery)->where('status', 'active')->count();
        $pendingBookings = (clone $bookingQuery)->where('status', 'pending')->count();
        $checkInsToday = (clone $bookingQuery)
            ->whereDate('check_in_date', $today)
            ->where('status', 'confirmed')
            ->count();
        $revenueMonth = (clone $bookingQuery)
            ->where('status', 'confirmed')
            ->whereBetween('check_in_date', [$monthStart, $monthEnd])
            ->sum('total');

        return response()->json([
            'stats' => [
                ['key' => 'active_apartments', 'label' => 'Active apartments', 'value' => $activeApartments],
                ['key' => 'pending_bookings', 'label' => 'Pending bookings', 'value' => $pendingBookings],
                ['key' => 'check_ins_today', 'label' => 'Check-ins today', 'value' => $checkInsToday],
                ['key' => 'revenue_month', 'label' => 'Revenue (month)', 'value' => (int) $revenueMonth, 'format' => 'vnd'],
            ],
        ]);
    }
}
