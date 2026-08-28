<?php

namespace App\Services;

class ApartmentPricingService
{
    /** @var array<string, int> */
    public const BASE_PRICES = [
        'Studio' => 800_000,
        '1BR' => 1_200_000,
        '2BR' => 2_000_000,
        '3BR' => 3_000_000,
        '4BR' => 4_500_000,
    ];

    /** @var array<string, float> */
    public const STANDARD_MULTIPLIERS = [
        'standard' => 1.0,
        'above_average' => 1.1,
        'premium' => 1.2,
    ];

    public static function mapQualityToPriceLevel(string $qualityStandard): string
    {
        return match ($qualityStandard) {
            'premium' => 'premium',
            'above_average' => 'above_average',
            default => 'normal',
        };
    }

    public static function roomsFromType(string $apartmentType): int
    {
        return match (strtoupper($apartmentType)) {
            'STUDIO' => 0,
            '1BR' => 1,
            '2BR' => 2,
            '3BR' => 3,
            '4BR' => 4,
            default => 1,
        };
    }

    public static function suggestDailyPrice(string $apartmentType, string $qualityStandard): float
    {
        $base = self::BASE_PRICES[$apartmentType] ?? 1_200_000;
        $multiplier = self::STANDARD_MULTIPLIERS[$qualityStandard] ?? 1.0;

        return round($base * $multiplier);
    }

    public static function generateApartmentName(
        string $buildingName,
        string $feature,
        string $districtLabel,
        string $apartmentType,
    ): string {
        $parts = array_filter([trim($buildingName), trim($feature)]);
        $name = implode('–', $parts);

        if ($districtLabel !== '') {
            $name .= ($name !== '' ? ' ' : '').trim($districtLabel);
        }

        if ($apartmentType !== '') {
            $name .= ($name !== '' ? '–' : '').trim($apartmentType);
        }

        return substr($name, 0, 200);
    }
}
