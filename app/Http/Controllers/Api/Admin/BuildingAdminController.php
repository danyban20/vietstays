<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BuildingAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Building::query()
            ->with(['district.city.country'])
            ->withCount('apartments');

        if ($request->boolean('archived')) {
            $query->where('status', 'archived');
        } else {
            $query->where('status', '!=', 'archived');
        }

        if ($request->filled('district_id')) {
            $query->where('district_id', $request->integer('district_id'));
        } elseif ($request->filled('city_id')) {
            $cityId = $request->integer('city_id');
            $query->whereHas('district', fn ($q) => $q->where('city_id', $cityId));
        } elseif ($request->filled('country_id')) {
            $countryId = $request->integer('country_id');
            $query->whereHas('district.city', fn ($q) => $q->where('country_id', $countryId));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', $search)->orWhere('short_name', 'like', $search);
            });
        }

        $buildings = $query->orderBy('name')->get();

        $districtsWithBuildings = $buildings->pluck('district_id')->filter()->unique()->count();

        $shortNameConflicts = $buildings
            ->pluck('short_name')
            ->filter()
            ->countBy()
            ->filter(fn ($count) => $count > 1)
            ->count();

        $data = $buildings->map(fn (Building $building) => $this->transform($building));

        return response()->json([
            'data' => $data,
            'meta' => [
                'total' => $buildings->count(),
                'districts_with_buildings' => $districtsWithBuildings,
                'short_name_conflicts' => $shortNameConflicts,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:255', 'unique:buildings,short_name'],
            'district_id' => ['required', 'integer', 'exists:vv_districts,district_id'],
        ]);

        $district = District::query()->find($data['district_id']);

        $building = Building::query()->create([
            'name' => $data['name'],
            'short_name' => $data['short_name'] ?? null,
            'slug' => $this->uniqueSlug($data['name']),
            'district_wp_id' => $district?->district_id ?? 0,
            'district_id' => $data['district_id'],
            'status' => 'publish',
        ]);

        return response()->json(['data' => $this->transform($building->load('district.city.country')), 'message' => 'Building created.'], 201);
    }

    public function update(Request $request, Building $building): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'short_name' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('buildings', 'short_name')->ignore($building->id)],
            'district_id' => ['sometimes', 'integer', 'exists:vv_districts,district_id'],
        ]);

        $building->update($data);

        return response()->json(['data' => $this->transform($building->fresh()->load('district.city.country')), 'message' => 'Building updated.']);
    }

    public function archive(Request $request, Building $building): JsonResponse
    {
        $archived = $request->boolean('archived', $building->status !== 'archived');
        $building->update(['status' => $archived ? 'archived' : 'publish']);

        return response()->json([
            'data' => $this->transform($building->fresh()->load('district.city.country')),
            'message' => $archived ? 'Building archived.' : 'Building restored.',
        ]);
    }

    private function transform(Building $building): array
    {
        return [
            'id' => $building->id,
            'name' => $building->name,
            'short_name' => $building->short_name,
            'slug' => $building->slug,
            'status' => $building->status,
            'apartments_count' => $building->apartments_count ?? 0,
            'district' => $building->district ? [
                'district_id' => $building->district->district_id,
                'name' => $building->district->name,
                'district_code' => $building->district->district_code,
            ] : null,
            'city' => $building->district?->city ? [
                'city_id' => $building->district->city->city_id,
                'name' => $building->district->city->name,
            ] : null,
            'country' => $building->district?->city?->country ? [
                'id' => $building->district->city->country->id,
                'name' => $building->district->city->country->name,
            ] : null,
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (DB::table('buildings')->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$suffix);
        }

        return $slug;
    }
}
