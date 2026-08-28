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
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! $this->verifyPassword($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'user' => $this->userPayload($user),
        ]);
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
        if (Hash::check($plain, $stored)) {
            return true;
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
            'role' => $user->role,
            'locale' => $locale,
            'locale_preference' => $user->admin_locale ?: LocaleService::META_LOCALE_AUTO,
            'locale_label' => $this->locales->localeLabel($locale),
            'locale_short' => $this->locales->localeShort($locale),
        ];
    }
}
