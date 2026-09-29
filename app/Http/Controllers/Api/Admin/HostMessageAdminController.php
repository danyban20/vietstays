<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminHostMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostMessageAdminController extends Controller
{
    public function index(Request $request, User $host): JsonResponse
    {
        $messages = AdminHostMessage::query()
            ->where('host_user_id', $host->id)
            ->with('sender')
            ->orderBy('created_at')
            ->get()
            ->map(fn (AdminHostMessage $message) => $this->transform($message));

        return response()->json([
            'data' => $messages,
            'meta' => [
                'host_name' => $host->display_name ?: $host->name,
            ],
        ]);
    }

    public function store(Request $request, User $host): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:4000'],
        ]);

        $message = AdminHostMessage::query()->create([
            'host_user_id' => $host->id,
            'sender_role' => 'admin',
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
            'created_at' => now(),
        ]);

        return response()->json([
            'data' => $this->transform($message->load('sender')),
            'message' => 'Message sent.',
        ], 201);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transform(AdminHostMessage $message): array
    {
        return [
            'id' => $message->id,
            'sender_role' => $message->sender_role,
            'sender_name' => $message->sender?->display_name ?: $message->sender?->name,
            'body' => $message->body,
            'created_at' => optional($message->created_at)?->toIso8601String(),
        ];
    }
}
