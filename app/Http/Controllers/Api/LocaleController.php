<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LocaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function __construct(
        protected LocaleService $locales,
    ) {}

    public function index(): JsonResponse
    {
        $registry = $this->locales->supportedLocales();

        return response()->json([
            'data' => [
                'default' => $this->locales->defaultLocale(),
                'locales' => collect($registry)->map(fn (array $info, string $slug) => [
                    'slug' => $slug,
                    'label' => $info['label'],
                    'short' => $info['short'],
                    'intl' => $info['locale'],
                ])->values(),
            ],
        ]);
    }

    public function messages(Request $request, string $locale): JsonResponse
    {
        if (! $this->locales->isSupported($locale)) {
            abort(404, 'Unsupported locale.');
        }

        $slug = $this->locales->normalizeLocale($locale);

        return response()->json([
            'data' => [
                'locale' => $slug,
                'intl' => $this->locales->localeToIntl($slug),
                'messages' => $this->locales->messagesForLocale($slug),
            ],
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        $locale = $this->locales->currentLocaleForUser($user, $request);

        return response()->json([
            'data' => [
                'locale' => $locale,
                'preference' => $user?->admin_locale ?: LocaleService::META_LOCALE_AUTO,
                'label' => $this->locales->localeLabel($locale),
                'short' => $this->locales->localeShort($locale),
                'intl' => $this->locales->localeToIntl($locale),
            ],
        ]);
    }

    public function updatePreference(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $allowed = array_merge(
            [LocaleService::META_LOCALE_AUTO],
            array_keys($this->locales->supportedLocales()),
        );

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($allowed)],
        ]);

        $this->locales->saveUserLocale($user, $validated['locale']);

        $resolved = $this->locales->currentLocaleForUser($user->fresh(), $request);

        $response = response()->json([
            'data' => [
                'locale' => $resolved,
                'preference' => $user->fresh()->admin_locale ?: LocaleService::META_LOCALE_AUTO,
                'label' => $this->locales->localeLabel($resolved),
                'short' => $this->locales->localeShort($resolved),
                'intl' => $this->locales->localeToIntl($resolved),
            ],
            'message' => 'Language preference updated.',
        ]);

        return $response->withCookie(
            Cookie::make(
                LocaleService::COOKIE_LOCALE,
                $resolved,
                60 * 24 * 365,
                '/',
                null,
                false,
                false,
                false,
                'lax',
            ),
        );
    }

    public function updatePublic(Request $request): JsonResponse
    {
        $allowed = array_keys($this->locales->supportedLocales());

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($allowed)],
        ]);

        $locale = $this->locales->normalizeLocale($validated['locale']);

        $response = response()->json([
            'data' => [
                'locale' => $locale,
                'label' => $this->locales->localeLabel($locale),
                'short' => $this->locales->localeShort($locale),
                'intl' => $this->locales->localeToIntl($locale),
            ],
        ]);

        return $response->withCookie(
            Cookie::make(
                LocaleService::COOKIE_LOCALE,
                $locale,
                60 * 24 * 365,
                '/',
                null,
                false,
                false,
                false,
                'lax',
            ),
        );
    }
}
