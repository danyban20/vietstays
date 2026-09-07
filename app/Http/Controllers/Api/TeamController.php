<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function __construct(
        protected TeamService $teams,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['sales', 'operations'])],
        ]);

        $payload = $this->teams->teamPayload($request->user(), $validated['type']);

        return response()->json(['data' => $payload]);
    }

    public function storeInvitation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['sales', 'operations'])],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:50'],
            'pay_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'pay_setup' => ['nullable', Rule::in(['pooled', 'reciprocal', 'one_way'])],
            'org' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($validated['email'] ?? null) && blank($validated['phone'] ?? null)) {
            return response()->json(['message' => 'Email or phone is required.'], 422);
        }

        $invitation = $this->teams->createInvitation(
            $request->user(),
            $validated['type'],
            $validated,
        );

        return response()->json([
            'data' => $invitation,
            'message' => 'Invitation sent successfully.',
        ], 201);
    }

    public function updateGuestInfo(Request $request, int $member): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $record = $this->teams->toggleGuestInfo($request->user(), $member, $validated['enabled']);

        if (! $record) {
            return response()->json(['message' => 'Team member not found.'], 404);
        }

        return response()->json(['data' => $record]);
    }

    public function destroyInvitation(Request $request, int $invitation): JsonResponse
    {
        if (! $this->teams->withdrawInvitation($request->user(), $invitation)) {
            return response()->json(['message' => 'Invitation not found.'], 404);
        }

        return response()->json(['message' => 'Invitation withdrawn.']);
    }
}
