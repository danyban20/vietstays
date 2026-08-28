<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\District;
use App\Models\Facility;
use App\Services\HostApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicHostApplicationController extends Controller
{
    public function __construct(
        protected HostApplicationService $service,
    ) {}

    public function options(): JsonResponse
    {
        $cities = City::query()->orderBy('name')->get(['city_id', 'name']);
        $districts = District::query()->orderBy('name')->get(['district_id', 'city_id', 'name']);
        $facilities = Facility::query()->where('type', 'apartment')->orderBy('name')->get(['facility_id', 'name']);

        return response()->json([
            'data' => [
                'cities' => $cities,
                'districts' => $districts,
                'facilities' => $facilities,
                'space_types' => collect(config('host_applications.space_types', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
                'years_managing' => collect(config('host_applications.years_managing', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
                'guest_profiles' => collect(config('host_applications.guest_profiles', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
                'platforms' => collect(config('host_applications.platforms', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applicant_type' => ['required', 'in:single_property,multi_property'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:64'],
            'address' => ['nullable', 'string'],
            'primary_city_id' => ['nullable', 'integer'],
            'home_city' => ['nullable', 'string', 'max:255'],
            'num_properties' => ['nullable', 'integer', 'min:1'],
            'districts' => ['required', 'array', 'min:1'],
            'districts.*' => ['integer'],
            'description' => ['required', 'string'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'portfolio_url' => ['nullable', 'url', 'max:512'],
            'years_managing' => ['nullable', 'string', 'max:64'],
            'guest_profile' => ['nullable', 'string', 'max:32'],
            'platforms_used' => ['nullable', 'array'],
            'platforms_used.*' => ['string', 'max:32'],
            'confirm_application' => ['required', 'boolean'],
            'property_address' => ['nullable', 'string'],
            'property_space_type' => ['nullable', 'string', 'max:32'],
            'property_beds' => ['nullable', 'integer', 'min:0'],
            'property_bathrooms' => ['nullable', 'integer', 'min:0'],
            'property_price_daily' => ['nullable', 'numeric', 'min:0'],
            'property_amenities' => ['nullable', 'array'],
            'property_amenities.*' => ['integer'],
            'property_name' => ['nullable', 'string', 'max:255'],
            'property_description' => ['nullable', 'string'],
        ]);

        if ($validated['applicant_type'] === 'single_property') {
            $validated['primary_city_id'] = $validated['primary_city_id'] ?? $request->integer('property_city_id');
            $districtId = $request->integer('property_district_id');
            if ($districtId > 0) {
                $validated['districts'] = [$districtId];
            }
            if (! empty($validated['property_name']) && empty($validated['description'])) {
                $validated['description'] = $validated['property_description'] ?? $validated['property_name'];
            }
        }

        try {
            $application = $this->service->submit($validated);
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return response()->json([
            'data' => $this->service->transform($application),
            'message' => 'Application submitted successfully.',
        ], 201);
    }
}
