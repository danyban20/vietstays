<?php

namespace App\Support;

final class LegacyWordpressUrl
{
    /**
     * Map leftover WordPress booking-search query params onto the Laravel Vue URLs.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public static function rewriteQuery(array $query): array
    {
        unset($query['vv_action']);

        if (! isset($query['city']) || $query['city'] === '' || $query['city'] === null) {
            $cityId = $query['city_id'] ?? null;
            if ($cityId !== null && $cityId !== '') {
                $query['city'] = $cityId;
            }
        }
        unset($query['city_id']);

        $datefilter = $query['datefilter'] ?? '';
        if (is_string($datefilter) && str_contains($datefilter, ' - ')) {
            $parts = array_map('trim', explode(' - ', $datefilter, 2));
            if (count($parts) === 2) {
                $from = self::toIsoDate($parts[0]);
                $to = self::toIsoDate($parts[1]);
                if ($from && empty($query['from'])) {
                    $query['from'] = $from;
                }
                if ($to && empty($query['to'])) {
                    $query['to'] = $to;
                }
            }
        }
        unset($query['datefilter']);

        return array_filter(
            $query,
            static fn ($value) => $value !== null && $value !== '',
        );
    }

    private static function toIsoDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $value, $match)) {
            return sprintf('%04d-%02d-%02d', (int) $match[3], (int) $match[1], (int) $match[2]);
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }
}
