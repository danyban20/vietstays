<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HostApplication;
use App\Services\HostApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HostApplicationController extends Controller
{
    public function __construct(
        protected HostApplicationService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $query = HostApplication::query()->orderByDesc('dateadded');

        if ($request->filled('status') && $request->string('status') !== 'all') {
            $query->where('status', $request->string('status'));
        } elseif (! $request->filled('status')) {
            $query->whereIn('status', ['submitted', 'under_review']);
        }

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('full_name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('application_ref', 'like', $search);
            });
        }

        $applications = $query->limit(100)->get()
            ->map(fn (HostApplication $application) => $this->service->transform($application));

        return response()->json(['data' => $applications]);
    }

    public function show(Request $request, int $application): JsonResponse
    {
        $this->ensureAdmin($request);

        $model = HostApplication::query()->findOrFail($application);

        return response()->json([
            'data' => $this->service->transform($model, detailed: true),
            'meta' => [
                'statuses' => collect(config('host_applications.statuses', []))
                    ->except(['activated'])
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
                'rejection_reasons' => collect(config('host_applications.rejection_reasons', []))
                    ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                    ->values(),
            ],
        ]);
    }

    public function updateStatus(Request $request, int $application): JsonResponse
    {
        $this->ensureAdmin($request);

        $model = HostApplication::query()->findOrFail($application);

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(config('host_applications.statuses', [])))],
            'rejection_reason' => ['nullable', 'string', Rule::in(array_keys(config('host_applications.rejection_reasons', [])))],
            'rejection_comment' => ['nullable', 'string', 'max:150'],
        ]);

        if ($validated['status'] === 'rejected' && empty($validated['rejection_reason'])) {
            return response()->json([
                'message' => 'Rejection reason is required.',
            ], 422);
        }

        $updated = $this->service->updateStatus(
            $model,
            $validated['status'],
            $request->user(),
            $validated,
        );

        return response()->json([
            'data' => $this->service->transform($updated, detailed: true),
            'message' => 'Application status updated.',
        ]);
    }

    protected function ensureAdmin(Request $request): void
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only administrators can manage host applications.');
        }
    }
}
