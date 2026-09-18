<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\HostManagementCompany;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManagementCompanyAdminService
{
    /**
     * Flat platform commission used for the "outstanding" figure until a
     * real payouts/settlement module tracks this per company.
     */
    private const COMMISSION_PCT = 10;

    /**
     * @return array<string, mixed>
     */
    public function transform(HostManagementCompany $company): array
    {
        $legacyIds = $company->linkedHosts->pluck('legacy_wp_id')->filter()->values();

        $apartments = DB::table('vv_apartments')
            ->join('vv_districts', 'vv_apartments.district', '=', 'vv_districts.district_id')
            ->join('vv_cities', 'vv_districts.city_id', '=', 'vv_cities.city_id')
            ->whereIn('vv_apartments.user_id', $legacyIds)
            ->select('vv_cities.name as city_name', DB::raw('count(*) as cnt'))
            ->groupBy('vv_cities.name')
            ->pluck('cnt', 'city_name');

        $apartmentsCount = (int) $apartments->sum();
        $market = $apartments->keys()->implode(', ') ?: null;

        $bookings = $legacyIds->isEmpty()
            ? collect()
            : Booking::query()
                ->join('vv_apartments', 'vv_bookings.apartment_id', '=', 'vv_apartments.ID')
                ->whereIn('vv_apartments.user_id', $legacyIds)
                ->where('vv_bookings.dateadded', '>=', now()->subDays(90))
                ->select('vv_bookings.*')
                ->get();

        $nonCancelled = $bookings->where('status', '!=', 'cancelled');
        $gross90 = (float) $nonCancelled->sum('total');
        $outstanding = round($gross90 * self::COMMISSION_PCT / 100);

        return [
            'id' => $company->id,
            'name' => $company->name,
            'company_number' => $company->company_number,
            'status' => $company->status,
            'status_label' => config('management_companies.statuses.'.$company->status, $company->status),
            'manager_name' => $company->user?->display_name ?: $company->user?->name,
            'manager_email' => $company->user?->email,
            'manager_since' => optional($company->created_at)?->toIso8601String(),
            'market' => $market,
            'hosts_count' => $company->linkedHosts->count(),
            'apartments_count' => $apartmentsCount,
            'bookings_90d' => $nonCancelled->count(),
            'revenue_90d' => $gross90,
            'outstanding' => $outstanding,
            'settlement_label' => $outstanding > 0 ? 'Outstanding' : 'Settled',
            'rejection_reason' => $company->rejection_reason,
            'rejection_reason_label' => $company->rejection_reason
                ? config('management_companies.rejection_reasons.'.$company->rejection_reason, $company->rejection_reason)
                : null,
            'reviewed_by' => $company->reviewer?->display_name ?: $company->reviewer?->name,
            'reviewed_at' => optional($company->reviewed_at)?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateStatus(HostManagementCompany $company, string $status, User $admin, array $data = []): HostManagementCompany
    {
        $allowed = ['active', 'rejected', 'paused'];

        if (! in_array($status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid status.']);
        }

        $update = [
            'status' => $status,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ];

        if ($status === 'rejected') {
            $update['rejection_reason'] = $data['rejection_reason'] ?? null;
        } else {
            $update['rejection_reason'] = null;
        }

        $company->update($update);

        return $company->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createShell(array $data): HostManagementCompany
    {
        return HostManagementCompany::query()->create([
            'user_id' => null,
            'name' => trim((string) $data['name']),
            'company_number' => $data['company_number'] ?? null,
            'status' => 'invited',
            'revenue_model' => 'pool',
            'excluded' => [],
            'pending_invites' => [],
            'shares' => [],
            'included_count' => 0,
            'company_apartment_count' => 0,
        ]);
    }
}
