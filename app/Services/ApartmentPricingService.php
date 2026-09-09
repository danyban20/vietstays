<?php

namespace App\Services;

class ApartmentPricingService
{
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
