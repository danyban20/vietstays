<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only "all hosts" overview for superadmin/supervisor. Account creation
 * and role changes stay in UserAdminController — this is a reporting view.
 */
class HostOverviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->whereIn('role', ['partner', 'host'])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('display_name', 'like', $search);
            });
        }

        if ($request->filled('role') && in_array($request->string('role')->toString(), ['partner', 'host'], true)) {
            $query->where('role', $request->string('role'));
        }

        $users = $query->limit(200)->get();

        $legacyIds = $users->pluck('legacy_wp_id')->filter()->values();

        $apartmentCounts = Apartment::query()
            ->whereIn('user_id', $legacyIds)
            ->select('user_id', DB::raw('count(*) as cnt'))
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $bookingCounts = DB::table('vv_bookings')
            ->join('vv_apartments', 'vv_bookings.apartment_id', '=', 'vv_apartments.ID')
            ->whereIn('vv_apartments.user_id', $legacyIds)
            ->where('vv_bookings.dateadded', '>=', now()->subDays(90))
            ->select('vv_apartments.user_id', DB::raw('count(*) as cnt'))
            ->groupBy('vv_apartments.user_id')
            ->pluck('cnt', 'user_id');

        $data = $users->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->display_name ?: $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'apartments_count' => (int) ($apartmentCounts[$user->legacy_wp_id] ?? 0),
            'bookings_90d' => (int) ($bookingCounts[$user->legacy_wp_id] ?? 0),
            'created_at' => $user->created_at?->toIso8601String(),
        ])->values();

        return response()->json(['data' => $data]);
    }
}
