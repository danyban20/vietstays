<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocaleService;
use App\Services\WordPressPasswordVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected LocaleService $locales,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            // 'dashboard' = the /admin login form; customers sign in on the site.
            'area' => ['nullable', Rule::in(['dashboard', 'site'])],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! $this->verifyPassword($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        if (($credentials['area'] ?? null) === 'dashboard' && ! $user->canUseDashboard()) {
            throw ValidationException::withMessages([
                'email' => [$user->isMember()
                    ? 'This is a customer account. Please sign in on the Vietstays website instead.'
                    : 'This account does not have access to the dashboard yet.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Free customer sign-up from the public site. Always creates a 'member';
     * hosts and partners come in through host applications or admins.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'An account with this email already exists. Try signing in instead.',
        ]);

        $user = User::query()->create([
            'name' => trim($data['name']),
            'display_name' => trim($data['name']),
            'email' => Str::lower(trim($data['email'])),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'password' => Hash::make($data['password']),
            'role' => 'member',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true]);
    }

    public function user(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
    }

    protected function verifyPassword(string $plain, string $stored): bool
    {
        try {
            if (Hash::check($plain, $stored)) {
                return true;
            }
        } catch (\RuntimeException) {
            // Stored hash isn't Bcrypt (legacy WordPress $wp$/$P$/$H$ format) —
            // Hash::check() throws instead of returning false when hashing.bcrypt.verify
            // is enabled. Fall through to the legacy verifier below.
        }

        return WordPressPasswordVerifier::check($plain, $stored);
    }

    protected function userPayload(User $user): array
    {
        $locale = $this->locales->currentLocaleForUser($user);

        return [
            'id' => $user->id,
            'name' => $user->display_name ?: $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'can_use_dashboard' => $user->canUseDashboard(),
            'locale' => $locale,
            'locale_preference' => $user->admin_locale ?: LocaleService::META_LOCALE_AUTO,
            'locale_label' => $this->locales->localeLabel($locale),
            'locale_short' => $this->locales->localeShort($locale),
        ];
    }
}
