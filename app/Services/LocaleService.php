<?php

namespace App\Services;

use App\Models\User;
use App\Models\VvOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LocaleService
{
    public const META_LOCALE_AUTO = 'auto';

    public const OPTION_LOCALE_REGISTRY = 'vv_i18n_locales';

    public const COOKIE_LOCALE = 'vv_locale';

    public const TABLE_APARTMENT_I18N = 'vv_apartment_i18n';

    /**
     * @return array<string, array{locale: string, label: string, short: string}>
     */
    public function defaultLocaleRegistry(): array
    {
        return [
            'no' => [
                'locale' => 'nb_NO',
                'label' => 'Norwegian',
                'short' => 'NO',
            ],
            'en' => [
                'locale' => 'en_US',
                'label' => 'English',
                'short' => 'EN',
            ],
            'vi' => [
                'locale' => 'vi',
                'label' => 'Vietnamese',
                'short' => 'VI',
            ],
            'tl' => [
                'locale' => 'tl',
                'label' => 'Tagalog',
                'short' => 'TL',
            ],
        ];
    }

    public function defaultLocale(): string
    {
        return 'en';
    }

    /**
     * @return array<string, array{locale: string, label: string, short: string}>
     */
    public function supportedLocales(): array
    {
        $stored = VvOption::getJson(self::OPTION_LOCALE_REGISTRY, []);
        $registry = ! empty($stored)
            ? $this->sanitizeLocaleRegistry($stored)
            : $this->defaultLocaleRegistry();

        if (empty($stored)) {
            VvOption::setValue(self::OPTION_LOCALE_REGISTRY, $registry);
        }

        return $registry;
    }

    /**
     * @param  array<string, mixed>  $registry
     * @return array<string, array{locale: string, label: string, short: string}>
     */
    public function sanitizeLocaleRegistry(array $registry): array
    {
        $clean = [];

        foreach ($registry as $slug => $info) {
            $slug = $this->sanitizeLocaleSlug((string) $slug);
            if ($slug === '' || ! is_array($info)) {
                continue;
            }

            $label = trim((string) ($info['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $short = strtoupper(trim((string) ($info['short'] ?? '')));
            if ($short === '') {
                $short = strtoupper(substr($slug, 0, 3));
            }

            $wpLocale = trim((string) ($info['locale'] ?? ''));
            if ($wpLocale === '') {
                $wpLocale = $slug;
            }

            $clean[$slug] = [
                'locale' => $wpLocale,
                'label' => $label,
                'short' => substr($short, 0, 5),
            ];
        }

        if (! isset($clean[$this->defaultLocale()])) {
            $defaults = $this->defaultLocaleRegistry();
            $clean = [$this->defaultLocale() => $defaults[$this->defaultLocale()]] + $clean;
        }

        return $clean;
    }

    public function sanitizeLocaleSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9_]/', '', $slug) ?? '';

        if (strlen($slug) < 2 || strlen($slug) > 10 || $slug === 'auto') {
            return '';
        }

        return $slug;
    }

    public function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim($locale));

        if ($locale === '') {
            return $this->defaultLocale();
        }

        if (in_array($locale, ['en_us', 'en-us', 'english'], true)) {
            return 'en';
        }

        if (
            in_array($locale, ['no', 'nb', 'nb_no', 'nn', 'nn_no', 'norwegian', 'norsk', 'bokmal', 'bokmål', 'nynorsk'], true)
            || str_starts_with($locale, 'nb')
            || str_starts_with($locale, 'nn')
        ) {
            return 'no';
        }

        if ($locale === 'vietnamese' || str_starts_with($locale, 'vi')) {
            return 'vi';
        }

        if (in_array($locale, ['tl', 'fil', 'fil_ph', 'tagalog', 'filipino'], true)) {
            return 'tl';
        }

        return $locale;
    }

    public function isSupported(string $locale): bool
    {
        $slug = $this->normalizeLocale($locale);

        return isset($this->supportedLocales()[$slug]);
    }

    public function localeLabel(string $locale): string
    {
        $slug = $this->normalizeLocale($locale);
        $map = $this->supportedLocales();

        return $map[$slug]['label'] ?? strtoupper($slug);
    }

    public function localeShort(string $locale): string
    {
        $slug = $this->normalizeLocale($locale);
        $map = $this->supportedLocales();

        return $map[$slug]['short'] ?? strtoupper($slug);
    }

    public function localeToIntl(string $locale): string
    {
        $slug = $this->normalizeLocale($locale);
        $map = $this->supportedLocales();

        return $map[$slug]['locale'] ?? 'en_US';
    }

    public function languagesDirectory(): string
    {
        return lang_path('vietstays');
    }

    public function getLanguageFilePath(string $locale): string
    {
        $slug = $this->normalizeLocale($locale);
        if ($slug === $this->defaultLocale()) {
            return '';
        }

        return $this->languagesDirectory().'/'.$slug.'.php';
    }

    /**
     * @return array<string, string>
     */
    public function getTranslationMap(string $locale): array
    {
        $slug = $this->normalizeLocale($locale);
        if ($slug === $this->defaultLocale()) {
            return [];
        }

        $map = [];
        $main = $this->getLanguageFilePath($slug);

        if ($main !== '' && File::exists($main)) {
            $loaded = include $main;
            if (is_array($loaded)) {
                $map = $this->normalizeTranslationMap($loaded);
            }
        }

        $pattern = $this->languagesDirectory().'/'.$slug.'-admin*.php';
        foreach (glob($pattern) ?: [] as $adminFile) {
            $loaded = include $adminFile;
            if (is_array($loaded)) {
                $map = array_merge($map, $this->normalizeTranslationMap($loaded));
            }
        }

        $jsonPath = $this->languagesDirectory().'/'.$slug.'.json';
        if (File::exists($jsonPath)) {
            $decoded = json_decode(File::get($jsonPath), true);
            if (is_array($decoded)) {
                $map = array_merge($map, $this->normalizeTranslationMap($decoded));
            }
        }

        return $map;
    }

    /**
     * @param  array<string|int, mixed>  $data
     * @return array<string, string>
     */
    public function normalizeTranslationMap(array $data): array
    {
        $map = [];

        foreach ($data as $source => $translation) {
            $source = (string) $source;
            if ($source === '') {
                continue;
            }
            $map[$source] = (string) $translation;
        }

        return $map;
    }

    /**
     * Flat UI messages for the Vue SPA (English source keys).
     *
     * @return array<string, string>
     */
    public function uiMessages(): array
    {
        return config('vietstays.ui_messages', []);
    }

    /**
     * @return array<string, mixed>
     */
    public function messagesForLocale(string $locale): array
    {
        $slug = $this->normalizeLocale($locale);
        $english = $this->uiMessages();
        $flat = $this->getTranslationMap($slug);

        if ($slug === $this->defaultLocale()) {
            return $english;
        }

        $messages = $this->translateTree($english, $flat);
        $structured = $this->getStructuredUiMessages($slug);

        if ($structured !== []) {
            $messages = $this->mergeMessageTrees($messages, $structured);
        }

        return $messages;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getStructuredUiMessages(string $slug): array
    {
        $path = $this->languagesDirectory().'/ui-'.$slug.'.json';

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overlay
     * @return array<string, mixed>
     */
    protected function mergeMessageTrees(array $base, array $overlay): array
    {
        foreach ($overlay as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = $this->mergeMessageTrees($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * @param  array<string, mixed>  $tree
     * @param  array<string, string>  $flat
     * @return array<string, mixed>
     */
    protected function translateTree(array $tree, array $flat): array
    {
        $result = [];

        foreach ($tree as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->translateTree($value, $flat);

                continue;
            }

            $source = (string) $value;
            $result[$key] = $this->lookupTranslation($source, $flat);
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $flat
     */
    protected function lookupTranslation(string $source, array $flat): string
    {
        if (isset($flat[$source]) && $flat[$source] !== '') {
            return $flat[$source];
        }

        $lowerSource = strtolower($source);
        foreach ($flat as $key => $translation) {
            if (strtolower((string) $key) === $lowerSource && $translation !== '') {
                return $translation;
            }
        }

        return $source;
    }

    public function resolveLocale(?string $locale): string
    {
        $locale = strtolower(trim((string) $locale));

        if ($locale === '' || $locale === self::META_LOCALE_AUTO) {
            return $this->detectLocaleFromRequest();
        }

        $locale = $this->normalizeLocale($locale);

        return $this->isSupported($locale) ? $locale : $this->detectLocaleFromRequest();
    }

    public function detectLocaleFromRequest(?Request $request = null): string
    {
        $request ??= request();

        $cookie = $request->cookie(self::COOKIE_LOCALE);
        if ($cookie && $this->isSupported($cookie)) {
            return $this->normalizeLocale($cookie);
        }

        $accept = strtolower((string) $request->header('Accept-Language', ''));
        if ($accept !== '') {
            if (preg_match('/\bnb\b|\bno\b|\bnorsk\b|\bnorwegian\b|\bbokm[aå]l\b|\bnynorsk\b/', $accept)) {
                return 'no';
            }
            if (str_starts_with($accept, 'vi') || preg_match('/[,;]\s*vi\b/', $accept)) {
                return 'vi';
            }
            if (preg_match('/\btl\b|\bfil\b|\btagalog\b|\bfilipino\b/', $accept)) {
                return 'tl';
            }
        }

        return $this->defaultLocale();
    }

    public function currentLocaleForUser(?User $user, ?Request $request = null): string
    {
        if ($user?->admin_locale) {
            return $this->resolveLocale($user->admin_locale);
        }

        return $this->detectLocaleFromRequest($request);
    }

    public function saveUserLocale(User $user, string $locale): User
    {
        $locale = strtolower(trim($locale));

        if ($locale === '' || $locale === self::META_LOCALE_AUTO) {
            $user->admin_locale = null;
        } else {
            $locale = $this->normalizeLocale($locale);
            $user->admin_locale = $this->isSupported($locale) ? $locale : null;
        }

        $user->save();

        return $user->fresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function getLanguageFileInfo(string $locale): array
    {
        $slug = $this->normalizeLocale($locale);
        $path = $this->getLanguageFilePath($slug);
        $map = $this->getTranslationMap($slug);

        return [
            'slug' => $slug,
            'label' => $this->localeLabel($slug),
            'exists' => $path !== '' && File::exists($path),
            'path' => $path,
            'strings' => count($map),
            'modified' => ($path !== '' && File::exists($path))
                ? date('Y-m-d H:i', File::lastModified($path))
                : '',
        ];
    }

    /**
     * @param  array<string, string>  $map
     */
    public function writeLanguageFile(string $locale, array $map): bool
    {
        $slug = $this->normalizeLocale($locale);
        if ($slug === $this->defaultLocale() || ! $this->isSupported($slug)) {
            return false;
        }

        $dir = $this->languagesDirectory();
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $path = $dir.'/'.$slug.'.json';
        $normalized = $this->normalizeTranslationMap($map);

        File::put(
            $path,
            json_encode($normalized, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n",
        );

        return true;
    }

    public function isProtectedLocale(string $locale): bool
    {
        return $this->normalizeLocale($locale) === $this->defaultLocale();
    }

    /**
     * @return array<int, string>
     */
    public function apartmentI18nFields(): array
    {
        return [
            'name',
            'display_name',
            'description',
            'about_this_short',
            'about_this',
            'features_description',
            'house_rules',
            'property_safety',
        ];
    }
}
