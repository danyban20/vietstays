<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingMatrix extends Model
{
    protected $table = 'pricing_matrices';

    protected $fillable = [
        'type_key',
        'base_price_vnd',
    ];

    protected $casts = [
        'base_price_vnd' => 'integer',
    ];

    /**
     * Get all base prices as a lookup
     */
    public static function getBasePrice(string $typeKey): ?int
    {
        return static::where('type_key', $typeKey)->value('base_price_vnd');
    }

    /**
     * Get all base prices indexed by type_key
     */
    public static function getAllBasePrices(): array
    {
        return static::pluck('base_price_vnd', 'type_key')->toArray();
    }
}
