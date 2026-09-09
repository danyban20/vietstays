<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Building;
use App\Models\City;
use App\Models\District;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function cities(): JsonResponse
    {
        $cities = City::query()->orderBy('name')->get(['city_id', 'name']);

        return response()->json(['data' => $cities]);
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::query()->orderBy('name');

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->integer('city_id'));
        }

        return response()->json([
            'data' => $query->get(['district_id', 'city_id', 'name']),
        ]);
    }

    public function buildings(Request $request): JsonResponse
    {
        $query = Building::query()->where('status', 'publish')->orderBy('name');

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        }

        $buildings = $query->get(['id', 'name', 'district_id', 'facilities', 'building_gallery']);

        return response()->json(['data' => $buildings]);
    }

    public function facilities(Request $request): JsonResponse
    {
        $query = Facility::query()->orderBy('name');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        return response()->json(['data' => $query->get(['facility_id', 'name', 'type'])]);
    }

    public function filterOptions(Request $request): JsonResponse
    {
        $apartmentQuery = Apartment::query()->orderBy('display_name');

        if ($request->user()->isOperator() && ! $request->user()->isAdmin()) {
            $apartmentQuery->where('user_id', $request->user()->legacy_wp_id);
        }

        $apartments = $apartmentQuery->get(['ID', 'display_name', 'name', 'district', 'building_id', 'price_daily']);

        $districtIds = $apartments->pluck('district')->filter()->unique()->values();
        $buildingIds = $apartments->pluck('building_id')->filter()->unique()->values();

        $districts = District::query()
            ->whereIn('district_id', $districtIds)
            ->orderBy('name')
            ->get(['district_id', 'name']);

        $buildings = Building::query()
            ->whereIn('id', $buildingIds)
            ->orderBy('name')
            ->get(['id', 'name', 'district_id']);

        return response()->json([
            'data' => [
                'districts' => $districts,
                'buildings' => $buildings,
                'apartments' => $apartments->map(fn ($a) => [
                    'id' => $a->ID,
                    'name' => $a->display_name ?: $a->name,
                    'district_id' => $a->district,
                    'building_id' => $a->building_id,
                    'price_daily' => (float) $a->price_daily,
                ]),
            ],
        ]);
    }
}
