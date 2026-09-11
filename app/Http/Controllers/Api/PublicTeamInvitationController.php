<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicTeamInvitationController extends Controller
{
    public function __construct(
        protected TeamService $teams,
    ) {}

    public function show(Request $request, string $token): JsonResponse
    {
        $preview = $this->teams->invitationPreview($token);

        if (! $preview) {
            return response()->json(['message' => 'Invitation not found.'], 404);
        }

        return response()->json(['data' => $preview]);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $result = $this->teams->acceptInvitation($token);

        if (! $result) {
            return response()->json(['message' => 'Invitation not found.'], 404);
        }

        return response()->json([
            'data' => $result,
            'message' => 'Invitation accepted.',
        ]);
    }
}
