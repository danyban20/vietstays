<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\VvOption;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class VvEmailService
{
    /**
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        $defaults = [
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

        $settings = array_merge($defaults, VvOption::getJson('vv-sending_receiving_settings', []));

        $settings['disable_ssl_verification_out'] = (int) ($settings['disable_ssl_verification_out'] ?? 0);

        return $settings;
    }

    public function isConfigured(): bool
    {
        $settings = $this->getSettings();

        return trim((string) ($settings['sender_email'] ?? '')) !== ''
            && trim((string) ($settings['host_out'] ?? '')) !== ''
            && trim((string) ($settings['username_out'] ?? '')) !== ''
            && trim((string) ($settings['password_out'] ?? '')) !== '';
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function sendByCode(string $code, string $to, array $tokens = [], ?string $locale = 'en'): bool
    {
        $to = trim($to);
        if ($to === '') {
            return false;
        }

        $template = EmailTemplate::query()
            ->where('code', $code)
            ->where('locale', $locale ?? 'en')
            ->first();

        if ($template === null) {
            Log::warning('Email template not found.', ['code' => $code, 'locale' => $locale]);

            return false;
        }

        $subject = trim((string) $template->subject);
        $body = trim((string) $template->body);

        if ($subject === '' || $body === '') {
            Log::warning('Email template is empty.', ['code' => $code, 'locale' => $locale]);

            return false;
        }

        return $this->sendHtml(
            $to,
            $this->replaceTokens($subject, $tokens),
            $this->replaceTokens($body, $tokens),
        );
    }

    public function sendHtml(string $to, string $subject, string $body): bool
    {
        $to = trim($to);
        if ($to === '' || trim($subject) === '' || trim($body) === '') {
            return false;
        }

        if (! $this->isConfigured()) {
            Log::info('Outbound email skipped: SMTP settings are not configured.', ['to' => $to]);

            return false;
        }

        $settings = $this->getSettings();
        $mailerName = 'vv_smtp';

        Config::set('mail.mailers.'.$mailerName, [
            'transport' => 'smtp',
            'host' => (string) $settings['host_out'],
            'port' => (int) ($settings['port_out'] ?: 587),
            'encryption' => $settings['encryption_out'] ?: null,
            'username' => (string) $settings['username_out'],
            'password' => (string) $settings['password_out'],
            'timeout' => null,
        ]);

        Config::set('mail.from', [
            'address' => (string) $settings['sender_email'],
            'name' => (string) (($settings['sender_name'] ?? '') ?: 'Visit Vietnam'),
        ]);

        Mail::purge($mailerName);

        try {
            Mail::mailer($mailerName)->html($body, function ($message) use ($to, $subject, $settings) {
                $message->to($to)
                    ->subject($subject)
                    ->from(
                        (string) $settings['sender_email'],
                        (string) (($settings['sender_name'] ?? '') ?: 'Visit Vietnam'),
                    );
            });

            return true;
        } catch (Throwable $exception) {
            Log::warning('Failed to send email.', [
                'to' => $to,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, string>  $tokens
     */
    public function replaceTokens(string $text, array $tokens): string
    {
        foreach ($tokens as $key => $value) {
            $text = str_replace('%'.strtoupper((string) $key).'%', (string) $value, $text);
        }

        return $text;
    }
}
