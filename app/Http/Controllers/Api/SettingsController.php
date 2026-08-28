<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\VvOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    public function indexEmailTemplates(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $codes = config('vietstays.email_template_codes', []);
        $locales = config('vietstays.email_locales', []);
        $rows = EmailTemplate::query()
            ->whereIn('code', $codes)
            ->orderBy('email_id')
            ->get();

        $templates = [];

        foreach ($codes as $code) {
            $templates[$code] = [
                'code' => $code,
                'name' => EmailTemplate::humanizeCode($code),
                'locales' => [],
            ];

            foreach (array_keys($locales) as $locale) {
                $row = $rows->first(fn (EmailTemplate $template) => $template->code === $code && $template->locale === $locale);

                $templates[$code]['locales'][$locale] = $row
                    ? $this->transformEmailTemplate($row)
                    : [
                        'email_id' => null,
                        'code' => $code,
                        'locale' => $locale,
                        'subject' => '',
                        'body' => '',
                        'name' => EmailTemplate::humanizeCode($code),
                    ];
            }
        }

        return response()->json([
            'data' => [
                'codes' => $codes,
                'locales' => collect($locales)->map(fn (array $info, string $slug) => [
                    'slug' => $slug,
                    'short' => $info['short'] ?? strtoupper($slug),
                    'label' => $info['label'] ?? strtoupper($slug),
                ])->values(),
                'templates' => $templates,
            ],
        ]);
    }

    public function updateEmailTemplate(Request $request, string $code): JsonResponse
    {
        $this->ensureAdmin($request);

        $allowedCodes = config('vietstays.email_template_codes', []);
        if (! in_array($code, $allowedCodes, true)) {
            abort(404, 'Unknown email template code.');
        }

        $allowedLocales = array_keys(config('vietstays.email_locales', []));

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($allowedLocales)],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $template = EmailTemplate::query()->updateOrCreate(
            [
                'code' => $code,
                'locale' => $validated['locale'],
            ],
            [
                'subject' => $validated['subject'],
                'body' => $validated['body'],
            ],
        );

        return response()->json([
            'data' => $this->transformEmailTemplate($template),
            'message' => 'Email template updated.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformEmailTemplate(EmailTemplate $template): array
    {
        return [
            'email_id' => $template->email_id,
            'code' => $template->code,
            'locale' => $template->locale,
            'subject' => $template->subject,
            'body' => $template->body,
            'name' => $template->displayName(),
        ];
    }

    public function showEmail(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $settings = VvOption::getJson('vv-sending_receiving_settings', $this->defaultEmailSettings());

        return response()->json([
            'data' => $this->normalizeEmailSettings($settings),
        ]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'string', 'max:20'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', Rule::in(['ssl', 'tls'])],
            'sender_email' => ['nullable', 'email', 'max:255'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'host_out' => ['nullable', 'string', 'max:255'],
            'port_out' => ['nullable', 'string', 'max:20'],
            'username_out' => ['nullable', 'string', 'max:255'],
            'password_out' => ['nullable', 'string', 'max:255'],
            'encryption_out' => ['nullable', Rule::in(['ssl', 'tls'])],
            'disable_ssl_verification_out' => ['nullable', 'boolean'],
            'new_service_admin_email' => ['nullable', 'email', 'max:255'],
        ]);

        $current = VvOption::getJson('vv-sending_receiving_settings', $this->defaultEmailSettings());
        $settings = array_merge($current, collect($validated)->except('new_service_admin_email')->all());
        $settings['disable_ssl_verification_out'] = (int) ($validated['disable_ssl_verification_out'] ?? false);

        VvOption::setValue('vv-sending_receiving_settings', $settings);

        if (array_key_exists('new_service_admin_email', $validated)) {
            VvOption::setValue('vv-new_service_admin_email', $validated['new_service_admin_email'] ?? '');
        }

        return response()->json([
            'data' => $this->normalizeEmailSettings($settings),
            'message' => 'Email settings updated.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultEmailSettings(): array
    {
        return [
            'host' => '',
            'port' => '',
            'username' => '',
            'password' => '',
            'encryption' => 'ssl',
            'sender_email' => '',
            'sender_name' => '',
            'host_out' => '',
            'port_out' => '',
            'username_out' => '',
            'password_out' => '',
            'encryption_out' => 'tls',
            'disable_ssl_verification_out' => 0,
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    protected function normalizeEmailSettings(array $settings): array
    {
        $defaults = $this->defaultEmailSettings();
        $merged = array_merge($defaults, $settings);

        $merged['disable_ssl_verification_out'] = (bool) ($merged['disable_ssl_verification_out'] ?? false);
        $merged['new_service_admin_email'] = (string) VvOption::getValue('vv-new_service_admin_email', '');

        return $merged;
    }

    protected function ensureAdmin(Request $request): void
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Only administrators can manage settings.');
        }
    }
}
