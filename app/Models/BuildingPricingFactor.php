<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildingPricingFactor extends Model
{
    protected $table = 'building_pricing_factors';

    protected $fillable = [
        'building_id',
        'type_key',
        'price_override_vnd',
        'factor_override',
    ];

    protected $casts = [
        'price_override_vnd' => 'integer',
        'factor_override' => 'float',
    ];

    /**
     * Relationship to Building
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Get factor for a specific building and type
     */
    public static function getFactor(int $buildingId, string $typeKey): ?float
    {
        return static::where('building_id', $buildingId)
            ->where('type_key', $typeKey)
            ->value('factor_override');
    }

    /**
     * Get price override for a specific building and type
     */
    public static function getPriceOverride(int $buildingId, string $typeKey): ?int
    {
        return static::where('building_id', $buildingId)
            ->where('type_key', $typeKey)
            ->value('price_override_vnd');
    }
}
