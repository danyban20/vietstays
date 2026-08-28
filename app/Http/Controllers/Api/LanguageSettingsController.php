<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LocaleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LanguageSettingsController extends Controller
{
    public function __construct(
        protected LocaleService $locales,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $registry = $this->locales->supportedLocales();
        $locales = [];

        foreach ($registry as $slug => $info) {
            $fileInfo = $this->locales->getLanguageFileInfo($slug);
            $locales[] = array_merge($fileInfo, [
                'is_default' => $slug === $this->locales->defaultLocale(),
                'short' => $info['short'],
                'intl' => $info['locale'],
            ]);
        }

        return response()->json([
            'data' => [
                'locales' => $locales,
                'preview_strings' => [
                    'Dashboard',
                    'Booking',
                    'Apartments',
                    'Account Settings',
                    'Language',
                    'Save',
                ],
            ],
        ]);
    }

    public function show(Request $request, string $locale): JsonResponse
    {
        $this->ensureAdmin($request);

        if (! $this->locales->isSupported($locale)) {
            abort(404, 'Language not found.');
        }

        $slug = $this->locales->normalizeLocale($locale);
        $map = $this->locales->getTranslationMap($slug);

        return response()->json([
            'data' => [
                'locale' => $slug,
                'label' => $this->locales->localeLabel($slug),
                'info' => $this->locales->getLanguageFileInfo($slug),
                'translations' => $map,
            ],
        ]);
    }

    public function update(Request $request, string $locale): JsonResponse
    {
        $this->ensureAdmin($request);

        if (! $this->locales->isSupported($locale)) {
            abort(404, 'Language not found.');
        }

        $slug = $this->locales->normalizeLocale($locale);
        if ($slug === $this->locales->defaultLocale()) {
            abort(422, 'The default English language cannot be edited here.');
        }

        $validated = $request->validate([
            'translations' => ['required', 'array'],
            'translations.*' => ['nullable', 'string'],
        ]);

        $map = $this->locales->normalizeTranslationMap($validated['translations']);
        $this->locales->writeLanguageFile($slug, $map);

        return response()->json([
            'data' => [
                'locale' => $slug,
                'strings' => count($map),
                'info' => $this->locales->getLanguageFileInfo($slug),
            ],
            'message' => sprintf('Language file updated for %s (%d strings).', $this->locales->localeLabel($slug), count($map)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'slug' => ['required', 'string', 'min:2', 'max:10', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:80'],
            'short' => ['nullable', 'string', 'max:5'],
            'intl' => ['nullable', 'string', 'max:20'],
        ]);

        $slug = $this->locales->sanitizeLocaleSlug($validated['slug']);
        if ($slug === '' || $slug === $this->locales->defaultLocale()) {
            return response()->json(['message' => 'Invalid language code.'], 422);
        }

        $registry = $this->locales->supportedLocales();
        if (isset($registry[$slug])) {
            return response()->json(['message' => 'A language with this code already exists.'], 422);
        }

        $short = $validated['short'] !== null && $validated['short'] !== ''
            ? strtoupper(substr($validated['short'], 0, 5))
            : strtoupper(substr($slug, 0, 3));

        $registry[$slug] = [
            'locale' => $validated['intl'] ?: $slug,
            'label' => $validated['label'],
            'short' => $short,
        ];

        \App\Models\VvOption::setValue(LocaleService::OPTION_LOCALE_REGISTRY, $registry);
        $this->locales->writeLanguageFile($slug, []);

        return response()->json([
            'data' => [
                'locale' => $slug,
                'info' => $this->locales->getLanguageFileInfo($slug),
            ],
            'message' => sprintf('Language %s added.', $this->locales->localeLabel($slug)),
        ], 201);
    }

    public function destroy(Request $request, string $locale): JsonResponse
    {
        $this->ensureAdmin($request);

        $slug = $this->locales->normalizeLocale($locale);
        if ($this->locales->isProtectedLocale($slug)) {
            return response()->json(['message' => 'The default English language cannot be deleted.'], 422);
        }

        $registry = $this->locales->supportedLocales();
        if (! isset($registry[$slug])) {
            abort(404, 'Language not found.');
        }

        unset($registry[$slug]);
        \App\Models\VvOption::setValue(LocaleService::OPTION_LOCALE_REGISTRY, $registry);

        foreach ([
            lang_path('vietstays/'.$slug.'.php'),
            lang_path('vietstays/'.$slug.'.json'),
        ] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        foreach (glob(lang_path('vietstays/'.$slug.'-admin*.php')) ?: [] as $adminFile) {
            @unlink($adminFile);
        }

        return response()->json([
            'message' => 'Language deleted.',
        ]);
    }

    protected function ensureAdmin(Request $request): void
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only administrators can manage languages.');
        }
    }
}
