<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\Building;
use App\Models\City;
use App\Models\District;
use App\Models\Facility;
use App\Models\SecurityFeature;
use App\Support\LegacyMediaUrl;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicApartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Apartment::query()
            ->where('status', 'active')
            ->orderByDesc('datemodified');

        if ($request->filled('district')) {
            $query->where('district', $request->integer('district'));
        }

        if ($request->filled('type')) {
            $query->where('apartment_type', $request->string('type'));
        }

        $apartments = $query->limit(50)->get()->map(fn (Apartment $apt) => $this->transform($apt));

        return response()->json(['data' => $apartments]);
    }

    public function show(int $apartment): JsonResponse
    {
        $model = Apartment::query()
            ->where('status', 'active')
            ->findOrFail($apartment);

        return response()->json(['data' => $this->transform($model, true)]);
    }

    public function districts(): JsonResponse
    {
        $districtIds = Apartment::query()
            ->where('status', 'active')
            ->pluck('district')
            ->unique()
            ->filter();

        $districts = District::query()
            ->whereIn('district_id', $districtIds)
            ->orderBy('name')
            ->get(['district_id', 'name']);

        return response()->json(['data' => $districts]);
    }

    public function search(Request $request): JsonResponse
    {
        $apartmentQuery = Apartment::query()->where('status', 'active');

        if ($request->filled('district')) {
            $apartmentQuery->where('district', $request->integer('district'));
        }

        if ($request->filled('city')) {
            $districtIds = District::query()
                ->where('city_id', $request->integer('city'))
                ->pluck('district_id');
            $apartmentQuery->whereIn('district', $districtIds);
        }

        if ($request->filled('type')) {
            $apartmentQuery->where('apartment_type', $request->string('type'));
        }

        if ($request->filled('facility')) {
            foreach ((array) $request->input('facility') as $facilityId) {
                if (is_numeric($facilityId)) {
                    $apartmentQuery->whereJsonContains('facilities', (int) $facilityId);
                }
            }
        }

        if ($request->boolean('checkin_without_host')) {
            $apartmentQuery->where('checkin_without_host', 1);
        }

        if ($request->boolean('flexible_reservation')) {
            $apartmentQuery->where('flexible_reservation', 1);
        }

        $apartments = $apartmentQuery
            ->orderByDesc('datemodified')
            ->limit(200)
            ->get();

        $countsByDistrict = $apartments->groupBy('district')->map->count();

        $districtQuery = District::query()
            ->with('city')
            ->whereIn('district_id', $countsByDistrict->keys());

        if ($request->filled('city')) {
            $districtQuery->where('city_id', $request->integer('city'));
        }

        $districts = $districtQuery
            ->orderBy('name')
            ->get()
            ->map(function (District $district) use ($apartments, $countsByDistrict) {
                $districtApartments = $apartments->where('district', $district->district_id);
                $sample = $districtApartments->first();

                return [
                    'district_id' => $district->district_id,
                    'name' => $district->name,
                    'city_id' => $district->city_id,
                    'city_name' => $district->city?->name,
                    'apartment_count' => $countsByDistrict->get($district->district_id, 0),
                    'image' => $sample ? $this->resolveImage($sample) : null,
                ];
            })
            ->filter(fn (array $row) => $row['apartment_count'] > 0)
            ->values();

        $cities = City::query()
            ->whereIn(
                'city_id',
                District::query()
                    ->whereIn('district_id', Apartment::query()->where('status', 'active')->pluck('district'))
                    ->pluck('city_id')
                    ->unique()
                    ->filter(),
            )
            ->orderBy('name')
            ->get(['city_id', 'name']);

        return response()->json([
            'data' => [
                'districts' => $districts,
                'apartments' => $apartments->map(fn (Apartment $apt) => $this->transform($apt))->values(),
                'cities' => $cities,
            ],
        ]);
    }

    protected function resolveImage(Apartment $apartment): ?string
    {
        $images = is_array($apartment->images) ? $apartment->images : [];
        $heroImage = collect($images)->first(fn ($img) => ! empty($img['thumb'] ?? $img['image_id'] ?? ''));

        return $this->resolveImageUrl($heroImage['thumb'] ?? $heroImage['image_id'] ?? null);
    }

    protected function transform(Apartment $apartment, bool $detailed = false): array
    {
        $building = $apartment->building_id
            ? Building::query()->find($apartment->building_id)
            : null;

        $district = District::query()->find($apartment->district);

        $images = is_array($apartment->images) ? $apartment->images : [];
        $imageUrl = $this->resolveImage($apartment);

        $data = [
            'id' => $apartment->ID,
            'name' => $apartment->display_name ?: $apartment->name,
            'slug' => $apartment->url_slug,
            'district_id' => $apartment->district,
            'district' => $district?->name,
            'building' => $building?->name,
            'type' => $apartment->apartment_type,
            'standard' => $apartment->quality_standard,
            'price_daily' => (float) $apartment->price_daily,
            'image' => $imageUrl,
            'short_description' => strip_tags($apartment->about_this_short ?: ''),
        ];

        if ($detailed) {
            $facilityIds = collect($apartment->facilities ?? [])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            $securityIds = collect($apartment->security_features ?? [])
                ->filter(fn ($id) => is_numeric($id))
                ->map(fn ($id) => (int) $id)
                ->values();

            $host = $apartment->user_id
                ? User::query()->where('legacy_wp_id', $apartment->user_id)->first()
                : null;

            $data += [
                'description' => $apartment->description,
                'about_this_short' => $apartment->about_this_short,
                'about_this' => $apartment->about_this,
                'address' => $apartment->address ?: $building?->address,
                'latitude' => $apartment->address_latitude ?: null,
                'longitude' => $apartment->address_longitude ?: null,
                'facilities' => Facility::query()
                    ->whereIn('facility_id', $facilityIds)
                    ->orderBy('name')
                    ->get(['facility_id', 'name'])
                    ->map(fn (Facility $facility) => [
                        'id' => $facility->facility_id,
                        'name' => $facility->name,
                    ])
                    ->values()
                    ->all(),
                'security_features' => SecurityFeature::query()
                    ->whereIn('security_feature_id', $securityIds)
                    ->orderBy('name')
                    ->get(['security_feature_id', 'name'])
                    ->map(fn (SecurityFeature $feature) => [
                        'id' => $feature->security_feature_id,
                        'name' => $feature->name,
                    ])
                    ->values()
                    ->all(),
                'images' => collect($images)
                    ->map(fn (array $image) => [
                        'thumb' => $this->resolveImageUrl($image['thumb'] ?? null),
                        'full' => $this->resolveImageUrl(
                            $image['image_id'] ?? $image['url'] ?? $image['thumb'] ?? null,
                        ),
                        'caption' => $image['caption'] ?? '',
                    ])
                    ->filter(fn (array $image) => $image['thumb'] || $image['full'])
                    ->values()
                    ->all(),
                'cleaning_fee' => (float) $apartment->cleaning_fee,
                'max_guests' => (int) $apartment->max_guests,
                'rooms' => (int) $apartment->rooms,
                'num_beds' => (int) $apartment->num_beds,
                'num_bathrooms' => (int) $apartment->num_bathrooms,
                'area_sqm' => (int) $apartment->area_sqm,
                'check_in_time1' => $apartment->check_in_time1,
                'check_in_time2' => $apartment->check_in_time2,
                'check_out_time' => $apartment->check_out_time,
                'checkin_without_host' => (bool) $apartment->checkin_without_host,
                'flexible_reservation' => (bool) $apartment->flexible_reservation,
                'airport_pickup' => (bool) $apartment->airport_pickup,
                'scooter_rental' => (bool) $apartment->scooter_rental,
                'host' => $host ? [
                    'name' => $host->display_name ?: $host->name,
                ] : null,
            ];
        }

        return $data;
    }

    protected function resolveImageUrl(?string $src): ?string
    {
        return LegacyMediaUrl::normalize($src);
    }
}
