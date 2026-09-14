<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Country -> city -> district CRUD for the "Countries & locations" superadmin
 * page. vv_cities and vv_districts are legacy WordPress-imported tables with
 * plain (non auto-increment) integer primary keys, so new rows need a
 * synthetic id generated above the legacy id range (see nextLegacyId()).
 */
class LocationAdminController extends Controller
{
    private const SYNTHETIC_ID_FLOOR = 1_000_000;

    public function countries(): JsonResponse
    {
        $countries = Country::query()->orderBy('name')->get();

        $cityCounts = City::query()
            ->select('country_id', DB::raw('count(*) as cnt'))
            ->groupBy('country_id')
            ->pluck('cnt', 'country_id');

        $districtCounts = DB::table('vv_districts')
            ->join('vv_cities', 'vv_districts.city_id', '=', 'vv_cities.city_id')
            ->select('vv_cities.country_id', DB::raw('count(*) as cnt'))
            ->groupBy('vv_cities.country_id')
            ->pluck('cnt', 'country_id');

        $data = $countries->map(fn (Country $country) => [
            'id' => $country->id,
            'name' => $country->name,
            'code' => $country->code,
            'cities_count' => (int) ($cityCounts[$country->id] ?? 0),
            'districts_count' => (int) ($districtCounts[$country->id] ?? 0),
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function storeCountry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:countries,name'],
            'code' => ['nullable', 'string', 'max:2'],
        ]);

        $country = Country::query()->create($data);

        return response()->json(['data' => $country, 'message' => 'Country created.'], 201);
    }

    public function updateCountry(Request $request, Country $country): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('countries', 'name')->ignore($country->id)],
            'code' => ['sometimes', 'nullable', 'string', 'max:2'],
        ]);

        $country->update($data);

        return response()->json(['data' => $country, 'message' => 'Country updated.']);
    }

    public function cities(Request $request): JsonResponse
    {
        $query = City::query()->orderBy('name');

        if ($request->filled('country_id')) {
            $query->where('country_id', $request->integer('country_id'));
        }

        $cities = $query->get();

        $districtCounts = District::query()
            ->select('city_id', DB::raw('count(*) as cnt'))
            ->groupBy('city_id')
            ->pluck('cnt', 'city_id');

        $buildingCounts = DB::table('buildings')
            ->join('vv_districts', 'buildings.district_id', '=', 'vv_districts.district_id')
            ->select('vv_districts.city_id', DB::raw('count(*) as cnt'))
            ->groupBy('vv_districts.city_id')
            ->pluck('cnt', 'city_id');

        $data = $cities->map(fn (City $city) => [
            'city_id' => $city->city_id,
            'name' => $city->name,
            'code' => $city->code,
            'country_id' => $city->country_id,
            'districts_count' => (int) ($districtCounts[$city->city_id] ?? 0),
            'buildings_count' => (int) ($buildingCounts[$city->city_id] ?? 0),
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function storeCity(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'code' => ['nullable', 'string', 'max:100'],
        ]);

        $now = now()->timestamp;

        $city = City::query()->create([
            'city_id' => $this->nextLegacyId('vv_cities', 'city_id'),
            'name' => $data['name'],
            'code' => $data['code'] ?? '',
            'country_id' => $data['country_id'],
            'dateadded' => $now,
            'datemodified' => $now,
        ]);

        return response()->json(['data' => $city, 'message' => 'City created.'], 201);
    }

    public function updateCity(Request $request, City $city): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'country_id' => ['sometimes', 'integer', 'exists:countries,id'],
            'code' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $data['datemodified'] = now()->timestamp;
        $city->update($data);

        return response()->json(['data' => $city, 'message' => 'City updated.']);
    }

    public function districts(Request $request): JsonResponse
    {
        $query = District::query()->orderBy('name');

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->integer('city_id'));
        }

        $districts = $query->get();

        $buildingCounts = Building::query()
            ->select('district_id', DB::raw('count(*) as cnt'))
            ->groupBy('district_id')
            ->pluck('cnt', 'district_id');

        $data = $districts->map(fn (District $district) => [
            'district_id' => $district->district_id,
            'name' => $district->name,
            'district_code' => $district->district_code,
            'city_id' => $district->city_id,
            'buildings_count' => (int) ($buildingCounts[$district->district_id] ?? 0),
        ])->values();

        return response()->json(['data' => $data]);
    }

    public function storeDistrict(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'city_id' => ['required', 'integer', 'exists:vv_cities,city_id'],
            'district_code' => ['nullable', 'string', 'max:255', 'unique:vv_districts,district_code'],
        ]);

        $now = now()->timestamp;

        $district = District::query()->create([
            'district_id' => $this->nextLegacyId('vv_districts', 'district_id'),
            'district_num' => Str::limit(Str::slug($data['name']), 20, ''),
            'name' => $data['name'],
            'city_id' => $data['city_id'],
            'district_code' => $data['district_code'] ?? null,
            'dateadded' => $now,
            'datemodified' => $now,
        ]);

        return response()->json(['data' => $district, 'message' => 'District created.'], 201);
    }

    public function updateDistrict(Request $request, District $district): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'city_id' => ['sometimes', 'integer', 'exists:vv_cities,city_id'],
            'district_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique('vv_districts', 'district_code')->ignore($district->district_id, 'district_id'),
            ],
        ]);

        $data['datemodified'] = now()->timestamp;
        $district->update($data);

        return response()->json(['data' => $district, 'message' => 'District updated.']);
    }

    private function nextLegacyId(string $table, string $column): int
    {
        $max = (int) DB::table($table)->max($column);

        return max($max + 1, self::SYNTHETIC_ID_FLOOR);
    }
}
