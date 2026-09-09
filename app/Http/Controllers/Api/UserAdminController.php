<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    /**
     * @var list<string>
     */
    protected array $assignableRoles = ['admin', 'partner', 'host', 'staff', 'ambassador'];

    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = '%'.$request->string('search').'%';
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('display_name', 'like', $search);
            });
        }

        if ($request->filled('role') && $request->string('role') !== 'all') {
            $query->where('role', $request->string('role'));
        }

        $users = $query->limit(100)->get()->map(fn (User $user) => $this->transform($user));

        return response()->json(['data' => $users]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', Rule::in($this->assignableRoles)],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'display_name' => $data['display_name'] ?? $data['name'],
            'role' => $data['role'] ?? 'admin',
        ]);

        // Apartment/booking/customer ownership is keyed off legacy_wp_id (it
        // matches the legacy WordPress post-author id). Accounts created here
        // have no WordPress counterpart, so give them a synthetic one in a
        // range that can never collide with a real imported id.
        $user->update(['legacy_wp_id' => 900_000 + $user->id]);

        return response()->json([
            'data' => $this->transform($user),
            'message' => 'User created.',
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role' => ['sometimes', Rule::in($this->assignableRoles)],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:255'],
        ]);

        if (! $request->has('role') && ! $request->filled('password')) {
            return response()->json([
                'message' => 'Nothing to update.',
            ], 422);
        }

        $updates = [];

        if ($request->has('role')) {
            if ($request->user()?->id === $user->id && $data['role'] !== 'admin') {
                return response()->json([
                    'message' => 'You cannot remove your own administrator access.',
                ], 422);
            }

            if ($user->isAdmin() && $data['role'] !== 'admin') {
                $otherAdmins = User::query()
                    ->where('role', 'admin')
                    ->where('id', '!=', $user->id)
                    ->exists();

                if (! $otherAdmins) {
                    return response()->json([
                        'message' => 'At least one administrator must remain.',
                    ], 422);
                }
            }

            $updates['role'] = $data['role'];
        }

        if ($request->filled('password')) {
            $updates['password'] = $data['password'];
        }

        $user->update($updates);

        return response()->json([
            'data' => $this->transform($user->fresh()),
            'message' => 'User updated.',
        ]);
    }

    protected function transform(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->display_name ?: $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_label' => $this->roleLabel($user->role),
            'created_at' => $user->created_at?->toIso8601String(),
            'updated_at' => $user->updated_at?->toIso8601String(),
        ];
    }

    protected function roleLabel(?string $role): string
    {
        return match ($role) {
            'admin' => 'Administrator',
            'partner' => 'Partner',
            'host' => 'Host',
            'staff' => 'Staff',
            'ambassador' => 'Ambassador',
            default => ucfirst((string) $role),
        };
    }
}
