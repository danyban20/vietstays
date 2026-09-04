<?php

namespace App\Services;

use App\Models\Apartment;
use App\Models\Building;
use App\Models\District;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApartmentCreationService
{
    public function create(array $payload, User $user): Apartment
    {
        return DB::transaction(function () use ($payload, $user) {
            $building = Building::query()->findOrFail($payload['building_id']);
            $districtId = (int) ($building->district_id ?: $payload['district_id'] ?? 0);
            $district = District::query()->find($districtId);

            $apartmentType = $payload['apartment_type'];
            $qualityStandard = $payload['quality_standard'] ?? 'standard';
            $feature = $payload['distinguishing_feature'] ?? '';

            $name = trim($payload['name'] ?? '');
            if ($name === '') {
                $name = ApartmentPricingService::generateApartmentName(
                    $building->name,
                    $feature,
                    $district?->name ?? '',
                    $apartmentType,
                );
            }

            $userId = $user->legacy_wp_id;
            if ($user->isAdmin() && ! empty($payload['user_id'])) {
                $userId = (int) $payload['user_id'];
            }

            $suggestedPrice = ApartmentPricingService::suggestDailyPrice($apartmentType, $qualityStandard);
            $priceDaily = isset($payload['price_daily']) && (float) $payload['price_daily'] > 0
                ? (float) $payload['price_daily']
                : $suggestedPrice;

            $now = now();
            $slug = $this->uniqueSlug(Str::slug(strtolower($name)));

            $defaults = $this->defaultAttributes();

            $apartment = Apartment::query()->create(array_merge($defaults, [
                'ID' => $this->nextApartmentId(),
                'name' => $name,
                'display_name' => $name,
                'url_slug' => $slug,
                'user_id' => $userId,
                'building_id' => $building->id,
                'district' => $districtId,
                'distinguishing_feature' => $feature,
                'apartment_type' => $apartmentType,
                'quality_standard' => $qualityStandard,
                'price_level' => ApartmentPricingService::mapQualityToPriceLevel($qualityStandard),
                'rooms' => ApartmentPricingService::roomsFromType($apartmentType),
                'room_number' => $payload['room_number'] ?? null,
                'about_this_short' => $payload['about_this_short'] ?? '',
                'description' => $payload['description'] ?? '',
                'facilities' => $payload['facilities'] ?? [],
                'images' => $payload['images'] ?? [],
                'building_gallery_json' => $payload['building_gallery_json'] ?? null,
                'price_daily' => $priceDaily,
                'cleaning_fee' => (float) ($payload['cleaning_fee'] ?? 0),
                'status' => $payload['status'] ?? 'draft',
                'dateadded' => $now,
                'datemodified' => $now,
                'apartment_num' => 'APT'.$now->format('Ymd').sprintf('%03d', random_int(1, 999)),
            ]));

            return $apartment->fresh();
        });
    }

    public function update(Apartment $apartment, array $payload): Apartment
    {
        $allowed = [
            'name', 'display_name', 'building_id', 'district', 'distinguishing_feature',
            'apartment_type', 'quality_standard', 'price_level', 'room_number', 'floor_number',
            'about_this_short', 'description', 'facilities', 'images', 'price_daily',
            'cleaning_fee', 'status', 'pricing_model', 'pricing', 'seasonal_pricing',
        ];

        $data = array_intersect_key($payload, array_flip($allowed));

        if (isset($data['name']) && ! isset($data['display_name'])) {
            $data['display_name'] = $data['name'];
        }

        if (isset($data['quality_standard'])) {
            $data['price_level'] = ApartmentPricingService::mapQualityToPriceLevel($data['quality_standard']);
        }

        if (isset($data['apartment_type'])) {
            $data['rooms'] = ApartmentPricingService::roomsFromType($data['apartment_type']);
        }

        $dbData = $this->prepareApartmentUpdateRow($data);
        $dbData['datemodified'] = now()->format('Y-m-d H:i:s');

        $affected = DB::table('vv_apartments')
            ->where('ID', $apartment->ID)
            ->update($dbData);

        if ($affected === false) {
            throw new \RuntimeException('Apartment update query failed.');
        }

        return Apartment::query()->findOrFail($apartment->ID);
    }

    protected function prepareApartmentUpdateRow(array $data): array
    {
        $jsonColumns = ['facilities', 'images', 'pricing', 'seasonal_pricing'];
        $row = [];

        foreach ($data as $key => $value) {
            if ($key === 'datemodified') {
                continue;
            }

            if (in_array($key, $jsonColumns, true)) {
                $row[$key] = $this->encodeApartmentJsonColumn($value);

                continue;
            }

            $row[$key] = match ($key) {
                'about_this_short', 'description' => (string) ($value ?? ''),
                'distinguishing_feature' => $value === null ? null : (string) $value,
                'price_daily', 'cleaning_fee' => round((float) ($value ?? 0), 2),
                default => $value,
            };
        }

        return $row;
    }

    protected function encodeApartmentJsonColumn(mixed $value): string
    {
        if ($value === null) {
            return '[]';
        }

        if (is_string($value)) {
            return $value;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);

        if ($encoded !== false) {
            return $encoded;
        }

        $sanitized = $this->sanitizeUtf8($value);
        $encoded = json_encode($sanitized, JSON_UNESCAPED_UNICODE);

        if ($encoded === false) {
            throw new \RuntimeException('Could not encode apartment JSON field.');
        }

        return $encoded;
    }

    protected function sanitizeUtf8(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->sanitizeUtf8($item);
        }

        return $value;
    }

    protected function defaultAttributes(): array
    {
        return [
            'apartment_num' => '',
            'post_id' => 0,
            'floor_number' => '',
            'building_gallery_json' => null,
            'pricing_model' => 'fixed',
            'seasonal_pricing' => null,
            'address' => '',
            'address_latitude' => '',
            'address_longitude' => '',
            'about_this' => '',
            'max_guests' => 2,
            'num_bathrooms' => 1,
            'num_beds' => 1,
            'area_sqm' => 0,
            'ambassador_commission' => 0,
            'promocode_discount' => 0,
            'pricing' => [],
            'check_in_time1' => '14:00:00',
            'check_in_time2' => '00:00:00',
            'check_out_time' => '11:00:00',
            'flexible_check_in' => false,
            'allow_extension' => false,
            'features_description' => '',
            'features' => '',
            'security_features' => [],
            'cleaning_fee_enabled' => true,
            'extra_cleaning_fee' => null,
            'cleaners_checklists' => [],
            'checkin_without_host' => false,
            'airport_pickup' => false,
            'flexible_reservation' => false,
            'scooter_rental' => false,
            'scooter_rental_fee' => 0,
            'house_rules' => '',
            'house_rules_json' => null,
            'assigned_staff' => null,
            'property_safety' => '',
            'rejection_reason' => null,
            'admin_review_note' => null,
        ];
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base !== '' ? $base : 'apartment';
        $candidate = $slug;
        $suffix = 2;

        while (Apartment::query()->where('url_slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    protected function nextApartmentId(): int
    {
        $max = (int) Apartment::query()->lockForUpdate()->max('ID');

        return $max + 1;
    }
}
