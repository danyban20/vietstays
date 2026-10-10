<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Services\GuestMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        protected GuestMessagingService $messages,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->messages->conversations($request->user())]);
    }

    public function show(Request $request, string $conversation): JsonResponse
    {
        return response()->json(['data' => $this->messages->show($request->user(), $conversation)]);
    }

    public function store(Request $request, string $conversation): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:4000'],
            'booking_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'data' => $this->messages->send(
                $request->user(),
                $conversation,
                trim($data['text']),
                isset($data['booking_id']) ? (int) $data['booking_id'] : null,
            ),
            'message' => 'Message sent.',
        ], 201);
    }
}
