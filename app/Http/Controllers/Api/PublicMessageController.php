<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerAggregationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMessageController extends Controller
{
    public function __construct(
        protected CustomerAggregationService $customers,
    ) {}

    public function show(Request $request, string $token): JsonResponse
    {
        $thread = $this->customers->guestThread($token);

        if (! $thread) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        return response()->json(['data' => $thread]);
    }

    public function store(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:4000'],
        ]);

        $thread = $this->customers->postGuestMessage($token, $validated['text']);

        if (! $thread) {
            return response()->json(['message' => 'Conversation not found.'], 404);
        }

        return response()->json([
            'data' => $thread,
            'message' => 'Message sent.',
        ], 201);
    }
}
