<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistrictPriceIndex extends Model
{
    protected $table = 'district_price_indices';

    protected $fillable = [
        'district_code',
        'district_name',
        'price_index',
    ];

    protected $casts = [
        'price_index' => 'float',
    ];

    /**
     * Get price index for a specific district
     */
    public static function getPriceIndex(string $districtCode): ?float
    {
        return static::where('district_code', $districtCode)
            ->orWhere('district_name', $districtCode)
            ->value('price_index');
    }

    /**
     * Get all district indices indexed by code
     */
    public static function getAllIndices(): array
    {
        return static::pluck('price_index', 'district_code')->toArray();
    }
}
