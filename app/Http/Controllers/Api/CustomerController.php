<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerAggregationService;
use App\Services\CustomerExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerController extends Controller
{
    public function __construct(
        protected CustomerAggregationService $customers,
    ) {}

    public function export(Request $request, CustomerExportService $exportService): StreamedResponse
    {
        return $exportService->streamXlsx($this->customers->listCustomers($request->user()));
    }

    public function index(Request $request): JsonResponse
    {
        if ($request->boolean('count_only')) {
            return response()->json([
                'total' => $this->customers->countCustomers($request->user()),
            ]);
        }

        $customers = $this->customers->listCustomers($request->user());

        return response()->json([
            'data' => $customers,
            'meta' => [
                'total' => count($customers),
            ],
        ]);
    }

    public function show(Request $request, string $customer): JsonResponse
    {
        $record = $this->customers->findCustomer($request->user(), $customer);

        if (! $record) {
            return response()->json(['message' => 'Customer not found.'], 404);
        }

        return response()->json(['data' => $record]);
    }

    public function storeNote(Request $request, string $customer): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $record = $this->customers->addNote($request->user(), $customer, $validated['text']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'data' => $record,
            'message' => 'Note saved.',
        ], 201);
    }

    public function merge(Request $request, string $customer): JsonResponse
    {
        $validated = $request->validate([
            'duplicate_id' => ['required', 'string'],
        ]);

        try {
            $record = $this->customers->mergeCustomers($request->user(), $customer, $validated['duplicate_id']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => $record,
            'message' => 'Customers merged.',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'reserved_by' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'reserve_after_create' => ['sometimes', 'boolean'],
        ]);

        $customer = $this->customers->createManualCustomer($request->user(), $validated);

        return response()->json([
            'data' => $customer,
            'reserve_after_create' => (bool) ($validated['reserve_after_create'] ?? false),
            'message' => filled($validated['email'] ?? null)
                ? 'Customer created successfully.'
                : 'Temporary customer #'.$customer['tempRef'].' created.',
        ], 201);
    }
}
