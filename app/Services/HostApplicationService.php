<?php

namespace App\Services;

use App\Models\City;
use App\Models\District;
use App\Models\HostApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class HostApplicationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data): HostApplication
    {
        $errors = $this->validateSubmission($data);

        if ($errors !== []) {
            throw ValidationException::withMessages(['application' => $errors]);
        }

        return DB::transaction(function () use ($data) {
            $user = $this->createOrUpdateApplicantUser($data);
            $now = now()->format('Y-m-d H:i:s');
            $ref = $this->generateApplicationRef();

            $row = [
                'application_ref' => $ref,
                'user_id' => $user->legacy_wp_id ?? $user->id,
                'applicant_type' => $data['applicant_type'],
                'full_name' => $data['full_name'],
                'home_city' => $data['home_city'] ?? $this->cityName((int) ($data['primary_city_id'] ?? 0)),
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'num_properties' => ($data['applicant_type'] === 'single_property') ? 1 : (int) $data['num_properties'],
                'primary_city_id' => (int) ($data['primary_city_id'] ?? 0) ?: null,
                'districts' => json_encode(array_values(array_map('intval', $data['districts'] ?? []))),
                'description' => $data['description'],
                'company_name' => $data['company_name'] ?? null,
                'portfolio_url' => $data['portfolio_url'] ?? null,
                'years_managing' => $data['years_managing'] ?? null,
                'guest_profile' => $data['guest_profile'] ?? null,
                'platforms_used' => json_encode(array_values($data['platforms_used'] ?? [])),
                'status' => 'submitted',
                'status_history' => json_encode([[
                    'status' => 'submitted',
                    'at' => $now,
                    'by' => 'Applicant',
                ]]),
                'dateadded' => $now,
                'datemodified' => $now,
            ];

            if ($data['applicant_type'] === 'single_property') {
                $row['property_address'] = $data['property_address'] ?? null;
                $row['property_kind'] = 'apartment';
                $row['property_type'] = $data['property_space_type'] ?? $data['property_type'] ?? null;
                $row['property_beds'] = (int) ($data['property_beds'] ?? 0) ?: null;
                $row['property_bathrooms'] = (int) ($data['property_bathrooms'] ?? 0) ?: null;
                $row['property_price_daily'] = isset($data['property_price_daily']) ? (float) $data['property_price_daily'] : null;
                $row['property_amenities'] = json_encode(array_values(array_map('intval', $data['property_amenities'] ?? [])));
                $row['property_images'] = json_encode($this->storePropertyImages($data['property_images'] ?? []));
            }

            $application = HostApplication::query()->create($row);

            return $application->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateStatus(HostApplication $application, string $status, User $admin, array $data = []): HostApplication
    {
        $allowed = array_keys(config('host_applications.statuses', []));

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid status.']);
        }

        if ($status === 'activated') {
            throw ValidationException::withMessages(['status' => 'Activated is set automatically when the host verifies their email.']);
        }

        $now = now()->format('Y-m-d H:i:s');
        $update = [
            'status' => $status,
            'datemodified' => $now,
            'assigned_to' => $admin->legacy_wp_id ?? $admin->id,
        ];

        if ($status === 'rejected') {
            $update['rejection_reason'] = $data['rejection_reason'] ?? null;
            $update['rejection_comment'] = isset($data['rejection_comment'])
                ? Str::limit($data['rejection_comment'], 150, '')
                : null;
        } else {
            $update['rejection_reason'] = null;
            $update['rejection_comment'] = null;
        }

        $history = $application->statusHistory();
        $history[] = [
            'status' => $status,
            'at' => $now,
            'by' => $admin->display_name ?: $admin->name,
        ];
        $update['status_history'] = json_encode($history);

        $application->update($update);

        return $application->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    protected function validateSubmission(array $data): array
    {
        $errors = [];

        if (! in_array($data['applicant_type'] ?? '', ['single_property', 'multi_property'], true)) {
            $errors[] = 'Select whether you are listing one apartment or multiple.';
        }

        if (trim((string) ($data['full_name'] ?? '')) === '') {
            $errors[] = 'Full name is required.';
        }

        if (! filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (trim((string) ($data['phone'] ?? '')) === '') {
            $errors[] = 'Phone number is required.';
        }

        if (trim((string) ($data['description'] ?? '')) === '') {
            $errors[] = 'Please describe yourself as a property manager and your portfolio.';
        }

        if (($data['applicant_type'] ?? '') === 'multi_property') {
            if ((int) ($data['num_properties'] ?? 0) < 1) {
                $errors[] = 'Number of apartments is required (minimum 1).';
            }
            if ((int) ($data['primary_city_id'] ?? 0) <= 0) {
                $errors[] = 'Primary city is required.';
            }
        }

        $districts = array_filter(array_map('intval', $data['districts'] ?? []));
        if ($districts === []) {
            $errors[] = 'Select at least one district within your primary city.';
        }

        if (($data['applicant_type'] ?? '') === 'single_property') {
            if (trim((string) ($data['property_address'] ?? '')) === '') {
                $errors[] = 'Property address is required.';
            }
            if (trim((string) ($data['property_space_type'] ?? '')) === '') {
                $errors[] = 'Space type is required.';
            }
            if ((float) ($data['property_price_daily'] ?? 0) <= 0) {
                $errors[] = 'Nightly price is required.';
            }
        }

        if (empty($data['confirm_application'])) {
            $errors[] = ($data['applicant_type'] ?? '') === 'single_property'
                ? 'Please confirm you understand your apartment stays hidden until host approval.'
                : 'Please confirm you understand this is an application and listings are created only after approval.';
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createOrUpdateApplicantUser(array $data): User
    {
        $email = strtolower(trim((string) $data['email']));
        $existing = User::query()->where('email', $email)->first();

        if ($existing?->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => 'This email belongs to an admin account and cannot be used for a host application.',
            ]);
        }

        if ($existing && in_array($existing->role, ['partner', 'admin'], true)) {
            throw ValidationException::withMessages([
                'email' => 'This email is already registered as an approved host or partner.',
            ]);
        }

        $payload = [
            'name' => trim((string) $data['full_name']),
            'display_name' => trim((string) $data['full_name']),
            'phone' => trim((string) $data['phone']),
            'role' => 'host',
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return User::query()->create([
            ...$payload,
            'email' => $email,
            'password' => Hash::make(Str::random(24)),
        ]);
    }

    protected function generateApplicationRef(): string
    {
        $year = now()->format('Y');
        $count = HostApplication::query()
            ->where('application_ref', 'like', "#HR-{$year}-%")
            ->count();

        return sprintf('#HR-%s-%04d', $year, $count + 1);
    }

    protected function cityName(int $cityId): string
    {
        if ($cityId <= 0) {
            return '';
        }

        return (string) City::query()->where('city_id', $cityId)->value('name');
    }

    /**
     * @param  list<mixed>  $images
     * @return list<array<string, string>>
     */
    protected function storePropertyImages(array $images): array
    {
        $stored = [];

        foreach ($images as $image) {
            if (is_string($image) && $image !== '') {
                $stored[] = ['url' => $image, 'thumb' => $image];
            }
        }

        return $stored;
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(HostApplication $application, bool $detailed = false): array
    {
        $cityName = $application->primary_city_id
            ? $this->cityName((int) $application->primary_city_id)
            : $application->home_city;

        $districtIds = $application->districtIds();
        $districtNames = District::query()
            ->whereIn('district_id', $districtIds)
            ->pluck('name', 'district_id');

        $payload = [
            'id' => $application->ID,
            'application_ref' => $application->application_ref,
            'user_id' => $application->user_id,
            'applicant_type' => $application->applicant_type,
            'type_label' => $application->typeLabel(),
            'full_name' => $application->full_name,
            'home_city' => $application->home_city,
            'primary_city_id' => $application->primary_city_id,
            'primary_city_name' => $cityName,
            'email' => $application->email,
            'phone' => $application->phone,
            'num_properties' => $application->num_properties,
            'status' => $application->status,
            'status_label' => $application->statusLabel(),
            'dateadded' => optional($application->dateadded)?->toIso8601String(),
            'datemodified' => optional($application->datemodified)?->toIso8601String(),
        ];

        if (! $detailed) {
            return $payload;
        }

        return array_merge($payload, [
            'address' => $application->address,
            'description' => $application->description,
            'company_name' => $application->company_name,
            'portfolio_url' => $application->portfolio_url,
            'years_managing' => $application->years_managing,
            'years_managing_label' => config('host_applications.years_managing.'.$application->years_managing, $application->years_managing),
            'guest_profile' => $application->guest_profile,
            'guest_profile_label' => config('host_applications.guest_profiles.'.$application->guest_profile, $application->guest_profile),
            'platforms_used' => $application->platformKeys(),
            'districts' => $districtIds,
            'district_names' => collect($districtIds)->map(fn ($id) => $districtNames[$id] ?? "District #{$id}")->values(),
            'rejection_reason' => $application->rejection_reason,
            'rejection_reason_label' => config('host_applications.rejection_reasons.'.$application->rejection_reason, $application->rejection_reason),
            'rejection_comment' => $application->rejection_comment,
            'status_history' => $application->statusHistory(),
            'property_address' => $application->property_address,
            'property_type' => $application->property_type,
            'property_type_label' => config('host_applications.space_types.'.$application->property_type, $application->property_type),
            'property_beds' => $application->property_beds,
            'property_bathrooms' => $application->property_bathrooms,
            'property_price_daily' => $application->property_price_daily,
            'property_amenities' => $application->amenityIds(),
            'property_images' => HostApplication::decodeJsonField($application->property_images),
            'apartment_id' => $application->apartment_id,
        ]);
    }
}
