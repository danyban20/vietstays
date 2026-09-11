<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HostManagementCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManagementCompanyController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $company = HostManagementCompany::query()->where('user_id', $request->user()->id)->first();

        return response()->json([
            'data' => $company ? $this->present($company) : null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form' => ['required', 'array'],
            'form.name' => ['required', 'string', 'max:255'],
            'form.tagline' => ['nullable', 'string', 'max:255'],
            'legalRegistered' => ['required', 'boolean'],
            'excluded' => ['present', 'array'],
            'excluded.*' => ['string', 'max:50'],
            'pendingInvites' => ['present', 'array'],
            'pendingInvites.*' => ['string', 'max:50'],
            'revenueModel' => ['required', Rule::in(['pool', 'ownership', 'commission'])],
            'shares' => ['required', 'array'],
            'includedCount' => ['required', 'integer', 'min:0'],
            'companyApartmentCount' => ['required', 'integer', 'min:0'],
        ]);

        $company = HostManagementCompany::query()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'name' => trim((string) $validated['form']['name']),
                'tagline' => filled($validated['form']['tagline'] ?? null) ? trim((string) $validated['form']['tagline']) : null,
                'legal_registered' => $validated['legalRegistered'],
                'excluded' => $validated['excluded'],
                'pending_invites' => $validated['pendingInvites'],
                'revenue_model' => $validated['revenueModel'],
                'shares' => $validated['shares'],
                'included_count' => $validated['includedCount'],
                'company_apartment_count' => $validated['companyApartmentCount'],
            ],
        );

        return response()->json([
            'data' => $this->present($company),
            'message' => 'Management company saved.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function present(HostManagementCompany $company): array
    {
        return [
            'form' => [
                'name' => $company->name,
                'tagline' => $company->tagline ?? '',
            ],
            'legalRegistered' => (bool) $company->legal_registered,
            'excluded' => $company->excluded ?? [],
            'pendingInvites' => $company->pending_invites ?? [],
            'revenueModel' => $company->revenue_model,
            'shares' => $company->shares ?? [],
            'includedCount' => (int) $company->included_count,
            'companyApartmentCount' => (int) $company->company_apartment_count,
        ];
    }
}
