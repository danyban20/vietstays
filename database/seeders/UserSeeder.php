<?php

namespace Database\Seeders;

use App\Support\LegacySqlParser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Import WordPress users into the Laravel users table.
     */
    public function run(): void
    {
        $parser = LegacySqlParser::default();
        $users = $parser->parseGyhUsers();
        $userMeta = $parser->parseGyhUsermeta();

        foreach ($users as $user) {
            $wpId = $user['ID'];
            $meta = $userMeta[$wpId] ?? [];

            DB::table('users')->updateOrInsert(
                ['legacy_wp_id' => $wpId],
                [
                    'name' => $user['display_name'] ?: $user['user_login'],
                    'email' => $user['user_email'],
                    'password' => $user['user_pass'],
                    'role' => $this->resolveRole($wpId, $user['user_email'], $meta),
                    'display_name' => $user['display_name'] ?: null,
                    'phone' => $this->nullableString($meta['billing_phone'] ?? null),
                    'admin_locale' => $this->nullableString($meta['vv_admin_locale'] ?? null),
                    'email_verified_at' => $user['user_registered'] !== '0000-00-00 00:00:00'
                        ? $user['user_registered']
                        : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        $this->command?->info('Imported '.count($users).' users from legacy WordPress data.');
    }

    /**
     * @param  array<string, string>  $meta
     */
    protected function resolveRole(int $wpId, string $email, array $meta): string
    {
        foreach (['vv_user_level', 'user_type'] as $key) {
            if (! empty($meta[$key]) && $meta[$key] !== 'user') {
                return $this->normalizeRole($meta[$key]);
            }
        }

        if (! empty($meta['gyh_capabilities']) && str_contains($meta['gyh_capabilities'], 'administrator')) {
            return 'admin';
        }

        if ($wpId >= 1 && $wpId <= 3) {
            return 'admin';
        }

        if (Str::contains(Str::lower($email), 'partner')) {
            return 'partner';
        }

        return 'host';
    }

    protected function normalizeRole(string $role): string
    {
        return match (Str::lower($role)) {
            'admin', 'administrator' => 'admin',
            'partner' => 'partner',
            'ambassador' => 'ambassador',
            'staff' => 'staff',
            'customer', 'guest', 'user' => 'host',
            default => $role,
        };
    }

    protected function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
