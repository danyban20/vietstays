<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostManagementCompany;
use App\Models\User;
use App\Services\ManagementCompanyAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManagementCompanyAdminController extends Controller
{
    public function __construct(
        protected ManagementCompanyAdminService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = HostManagementCompany::query()
            ->with(['user', 'reviewer', 'linkedHosts'])
            ->orderByDesc('created_at');

        if ($request->filled('city_id') || $request->filled('country_id')) {
            $apartmentQuery = DB::table('vv_apartments')
                ->join('vv_districts', 'vv_apartments.district', '=', 'vv_districts.district_id');

            if ($request->filled('city_id')) {
                $apartmentQuery->where('vv_districts.city_id', $request->integer('city_id'));
            } else {
                $apartmentQuery
                    ->join('vv_cities', 'vv_districts.city_id', '=', 'vv_cities.city_id')
                    ->where('vv_cities.country_id', $request->integer('country_id'));
            }

            $legacyIds = $apartmentQuery->pluck('vv_apartments.user_id')->unique();

            $companyIds = User::query()
                ->whereIn('legacy_wp_id', $legacyIds)
                ->pluck('management_company_id')
                ->filter()
                ->unique();

            $query->whereIn('id', $companyIds);
        }

        if ($request->filled('status') && $request->string('status') !== 'all') {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('company_number', 'like', $search)
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', $search)
                            ->orWhere('display_name', 'like', $search)
                            ->orWhere('email', 'like', $search);
                    });
            });
        }

        $companies = $query->limit(200)->get()
            ->map(fn (HostManagementCompany $company) => $this->service->transform($company));

        return response()->json([
            'data' => $companies->values(),
            'meta' => [
                'companies_total' => $companies->count(),
                'hosts_total' => $companies->sum('hosts_count'),
                'apartments_total' => $companies->sum('apartments_count'),
                'revenue_90d_total' => $companies->sum('revenue_90d'),
                'statuses' => collect(config('management_companies.statuses', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
                'rejection_reasons' => collect(config('management_companies.rejection_reasons', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_number' => ['nullable', 'string', 'max:100'],
        ]);

        $company = $this->service->createShell($data);

        return response()->json([
            'data' => $this->service->transform($company),
            'message' => 'Company added.',
        ], 201);
    }

    public function updateStatus(Request $request, HostManagementCompany $company): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'rejected', 'paused'])],
            'rejection_reason' => ['nullable', 'string', Rule::in(array_keys(config('management_companies.rejection_reasons', [])))],
        ]);

        if ($validated['status'] === 'rejected' && empty($validated['rejection_reason'])) {
            throw ValidationException::withMessages(['rejection_reason' => 'Rejection reason is required.']);
        }

        $updated = $this->service->updateStatus($company, $validated['status'], $request->user(), $validated);

        return response()->json([
            'data' => $this->service->transform($updated),
            'message' => 'Company status updated.',
        ]);
    }
}
